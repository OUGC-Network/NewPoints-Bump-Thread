<?php

/***************************************************************************
 *
 *    Newpoints Bump Thread plugin (/inc/plugins/newpoints/plugins/ougc/BumpThread/hooks/forum.php)
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

declare(strict_types=1);

namespace Newpoints\BumpThread\Hooks\Forum;

use MyBB;

use function Newpoints\Core\get_setting;
use function Newpoints\Core\group_permission_get_lowest;
use function Newpoints\Core\language_load;
use function Newpoints\Core\control_db;
use function Newpoints\Core\log_add;
use function Newpoints\Core\points_subtract;
use function Newpoints\Core\post_parser;

use const Newpoints\Core\LOGGING_TYPE_CHARGE;

function global_intermediate(): bool
{
    global $mybb;

    if (get_setting('bump_thread_enable_dvz_stream') && isset($mybb->settings['dvz_stream_active_streams'])) {
        $mybb->settings['dvz_stream_active_streams'] .= ',newpoints_bump_thread';
    }

    return true;
}

function xmlhttp09(): bool
{
    global $mybb;

    if (get_setting('bump_thread_enable_dvz_stream') && isset($mybb->settings['dvz_stream_active_streams'])) {
        $mybb->settings['dvz_stream_active_streams'] .= ',newpoints_bump_thread';
    }

    return true;
}

function newpoints_global_start(array &$hook_arguments): array
{
    $hook_arguments['showthread.php'][] = 'newpoints_bump_thread_showthread_button';

    return $hook_arguments;
}

function showthread_start09(): bool
{
    global $mybb;
    global $forum, $thread;
    global $newpoints_bump_thread;

    $newpoints_bump_thread = '';

    if (
        empty($forum['newpoints_bump_thread_enable']) ||
        empty($mybb->usergroup['newpoints_bump_thread_can_use']) ||
        (!empty($thread['closed']) && !get_setting('bump_thread_allow_closed_threads'))
    ) {
        return false;
    }

    $current_user_id = (int)$mybb->user['uid'];

    $is_author = (int)$thread['uid'] === $current_user_id;

    $is_moderator = get_setting('bump_thread_allow_moderator_bypass') && is_moderator($forum['fid']);

    $thread_id = (int)$thread['tid'];

    global $lang;

    $action_name = get_setting('bump_thread_action_name');

    if ($mybb->get_input('action') !== $action_name && ($is_author || $is_moderator)) {
        language_load('bump_thread');

        $thread_link = get_thread_link($thread_id, $mybb->get_input('page', MyBB::INPUT_INT), $action_name);

        $title = $lang->newpoints_bump_thread_show_thread_button;

        if (!empty($thread['newpoints_bump_thread_stamp'])) {
            $title = strip_tags(
                $lang->sprintf(
                    $lang->newpoints_bump_thread_show_thread_button_title,
                    my_date('relative', $thread['newpoints_bump_thread_stamp'])
                )
            );
        }

        $newpoints_bump_thread = eval(newpoints_bump_thread_get_template('showthread_button'));
    }

    if ($mybb->get_input('action') === $action_name && ($is_author || $is_moderator)) {
        language_load('bump_thread');

        $bump_price = get_setting('bump_thread_price') *
            $forum['newpoints_rate_bump_thread'] *
            $mybb->usergroup['newpoints_rate_bump_thread'] / 100;

        $user_points = (float)$mybb->user['newpoints'];

        if ($is_author && $bump_price > $user_points) {
            error(
                $lang->sprintf(
                    $lang->newpoints_bump_thread_error_price,
                    newpoints_format_points($bump_price),
                    newpoints_format_points($user_points)
                )
            );
        }

        $interval_minutes = group_permission_get_lowest('newpoints_bump_thread_interval');

        $interval_seconds = $interval_minutes * 60;

        if ($thread['newpoints_bump_thread_stamp'] + $interval_seconds > TIME_NOW || $mybb->user['newpoints_bump_thread_last_stamp'] + $interval_seconds > TIME_NOW) {
            error(
                $lang->sprintf(
                    $lang->newpoints_bump_thread_error_interval,
                    my_number_format($interval_minutes)
                )
            );
        }

        global $db;

        $db->update_query('threads', ['newpoints_bump_thread_stamp' => TIME_NOW], "tid='{$thread_id}'");

        $db->update_query('users', ['newpoints_bump_thread_last_stamp' => TIME_NOW], "uid='{$current_user_id}'");

        $forum_id = (int)$thread['fid'];

        $db->delete_query('forumsread', "fid='{$forum_id}'");

        $db->delete_query('threadsread', "tid='{$thread_id}'");

        if ($is_author) {
            points_subtract($current_user_id, $bump_price);
        }

        log_add(
            'bump_thread',
            '',
            $mybb->user['username'] ?? '',
            $current_user_id,
            $bump_price,
            $thread_id,
            0,
            0,
            LOGGING_TYPE_CHARGE
        );

        redirect(
            get_thread_link($thread_id, $mybb->get_input('page', MyBB::INPUT_INT)),
            $lang->newpoints_bump_thread_success_message,
            $lang->newpoints_bump_thread_success_title
        );
    }

    return true;
}

function forumdisplay_start09(): bool
{
    global $mybb;
    global $foruminfo;

    if (!isset($mybb->input['sortby']) && !empty($foruminfo['defaultsortby'])) {
        $mybb->input['sortby'] = $foruminfo['defaultsortby'];
    }

    if (!in_array($mybb->get_input('sortby'), ['subject', 'replies', 'views', 'starter', 'rating', 'started'])) {
        control_db(
            'function query($string, $hide_errors = 0, $write_query = 0)
{
    if (my_strpos($string, "t.sticky") !== false && my_strpos($string, "lastpost") !== false) {
        $string = str_replace(["lastpost ", "t.lastpost "],
            ["newpoints_bump_thread_stamp ", "t.newpoints_bump_thread_stamp "],
            $string);
    }

    return parent::query($string, $hide_errors, $write_query);
}'
        );
    }

    return true;
}

function newpoints_logs_log_row(): bool
{
    global $log_data;

    if (!in_array($log_data['action'], [
        'bump_thread',
    ])) {
        return false;
    }

    global $lang;
    global $log_action, $log_primary, $log_secondary, $log_tertiary;

    language_load('bump_thread');

    if ($log_data['action'] === 'bump_thread') {
        $log_action = $lang->newpoints_bump_thread_page_logs_bump_thread;
    }

    $thread_id = (int)$log_data['log_primary_id'];

    $thread_data = get_thread($thread_id);

    if (empty($thread_data)) {
        return false;
    }

    global $mybb;

    $thread_subject = post_parser()->parse_badwords($thread_data['subject']);

    $thread_url = get_thread_link($thread_id);

    $log_primary = $lang->sprintf(
        $lang->newpoints_bump_thread_page_logs_thread_link,
        $mybb->settings['bburl'],
        $thread_url,
        $thread_subject
    );

    return true;
}

function newpoints_logs_end(): bool
{
    global $lang;
    global $action_types;

    language_load('bump_thread');

    foreach ($action_types as $key => &$action_type) {
        if ($key === 'bump_thread') {
            $action_type = $lang->newpoints_bump_thread_page_logs_bump_thread;
        }
    }

    return true;
}