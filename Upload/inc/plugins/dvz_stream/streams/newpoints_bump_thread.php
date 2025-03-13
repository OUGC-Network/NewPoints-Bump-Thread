<?php

/***************************************************************************
 *
 *    NewPoints Bump Thread plugin (/inc/plugins/dvz_stream/streams/newpoints_bump_thread.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allows users to bump their own threads without posting on exchange of points.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

global $lang;

use dvzStream\Stream;
use dvzStream\StreamEvent;

use function dvzStream\addStream;
use function dvzStream\getCsvSettingValues;
use function dvzStream\getInaccessibleForumIds;
use function NewPoints\Core\language_load;

$stream = new Stream();

$stream->setName(explode('.', basename(__FILE__))[0]);

language_load('newpoints_bump_thread');

$stream->setTitle($lang->newpoints_bump_thread_dvz_stream);

$stream->setEventTitle($lang->newpoints_bump_thread_dvz_stream_event);

$stream->setFetchHandler(function (int $query_limit, int $last_log_id = 0) use ($stream) {
    global $db, $cache;

    $where_clauses = [
        "l.lid>'{$last_log_id}'",
        "l.action='bump_thread'",
        "t.visible='1'",
        "t.closed NOT LIKE 'moved|%'"
    ];

    $hidden_forums = array_merge(
        getInaccessibleForumIds(),
        getCsvSettingValues('hidden_forums')
    );

    if (in_array(-1, $hidden_forums)) {
        return [];
    }

    if ($hidden_forums) {
        $where_clauses[] = 't.fid NOT IN (' . implode(',', $hidden_forums) . ')';
    }

    $logs_cache = [];

    $query = $db->simple_select(
        "newpoints_log l LEFT JOIN {$db->table_prefix}threads t ON (t.tid=l.log_primary_id)",
        'l.lid AS log_id, l.date AS log_stamp, l.uid AS user_id, l.points AS bump_price, l.log_primary_id AS thread_id, t.firstpost AS first_post_id, t.subject AS thread_subject, t.fid AS forum_id, t.prefix AS thread_prefix',
        implode(' AND ', $where_clauses),
        ['order_by' => 'l.date', 'order_dir' => 'desc', 'limit' => $query_limit]
    );

    while ($log_data = $db->fetch_array($query)) {
        $logs_cache[(int)$log_data['log_id']] = $log_data;
    }

    $users_cache = [];

    $user_ids = implode("','", array_map('intval', array_column($logs_cache, 'user_id')));

    $query = $db->simple_select(
        'users',
        'uid AS user_id, username AS username, usergroup AS user_group, displaygroup AS display_group, avatar AS user_avatar',
        "uid IN ('{$user_ids}')"
    );

    while ($user_data = $db->fetch_array($query)) {
        $users_cache[(int)$user_data['user_id']] = $user_data;
    }

    $forums_cache = (array)$cache->read('forums');

    $prefixes_cache = (array)$cache->read('threadprefixes');

    $stream_events = [];

    foreach ($logs_cache as $log_id => $log_data) {
        $stream_event = new StreamEvent();

        $stream_event->setStream($stream);

        $stream_event->setId($log_id);

        $stream_event->setDate($log_data['log_stamp']);

        $stream_event->setUser([
            'id' => $log_data['user_id'],
            'username' => $users_cache[$log_data['user_id']]['username'],
            'usergroup' => $users_cache[$log_data['user_id']]['user_group'],
            'displaygroup' => $users_cache[$log_data['user_id']]['display_group'],
            'avatar' => $users_cache[$log_data['user_id']]['user_avatar'],
        ]);

        $stream_event->addData([
            'first_post_id' => (int)$log_data['first_post_id'],
            'forum_id' => (int)$log_data['forum_id'],
            'forum_name' => $forums_cache[$log_data['forum_id']]['name'] ?? '',
            'thread_id' => (int)$log_data['thread_id'],
            'thread_prefix' => $prefixes_cache[$log_data['thread_prefix']] ?? '',
            'thread_subject' => $log_data['thread_subject']
        ]);

        $stream_events[] = $stream_event;
    }

    return $stream_events;
});

$stream->addProcessHandler(function (StreamEvent $stream_event) {
    global $mybb, $lang;

    $stream_data = $stream_event->getData();

    global $parser;

    if (!($parser instanceof postParser)) {
        require_once MYBB_ROOT . 'inc/class_parser.php';

        $parser = new postParser();
    }

    $thread_subject = $parser->parse_badwords($stream_data['thread_subject']);

    $post_url = get_post_link(
            $stream_data['first_post_id'],
            $stream_data['thread_id']
        ) . '#pid' . $stream_data['first_post_id'];

    $thread_prefix = '';

    if (!empty($stream_data['thread_prefix']['displaystyle'])) {
        $thread_prefix = $stream_data['thread_prefix']['displaystyle'];
    }

    $stream_item = eval(newpoints_bump_thread_get_template('stream_item'));

    $stream_event->setItem($stream_item);

    $forum_url = get_forum_link($stream_data['forum_id']);

    $forum_name = htmlspecialchars_uni($stream_data['forum_name']);

    $stream_location = eval(newpoints_bump_thread_get_template('stream_location'));

    $stream_event->setLocation($stream_location);
});

addStream($stream);
