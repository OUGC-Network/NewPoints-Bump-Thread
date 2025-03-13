<?php

/***************************************************************************
 *
 *    Newpoints Bump Thread plugin (/inc/plugins/newpoints/plugins/ougc/BumpThread/hooks/forum.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2012 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allows users to bump their own threads for a price.
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

use function Newpoints\Core\get_setting;
use function Newpoints\Core\group_permission_get_lowest;
use function Newpoints\Core\language_load;
use function Newpoints\Core\control_db;
use function Newpoints\Core\log_add;
use function Newpoints\Core\points_subtract;

use const Newpoints\Core\LOGGING_TYPE_CHARGE;

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

    $is_moderator = false;

    if (get_setting('bump_thread_allow_moderator_bypass') && is_moderator($forum['fid'])) {
        $is_moderator = true;
    }

    $thread_id = (int)$thread['tid'];

    global $lang;

    if ($mybb->get_input('action') !== 'bump_thread' && ($is_author || $is_moderator)) {
        language_load('bump_thread');

        $thread_link = get_thread_link($thread_id, 0, 'bump_thread');

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

    if ($mybb->get_input('action') === 'bump_thread' && ($is_author || $is_moderator)) {
        language_load('bump_thread');

        $bump_price = get_setting('bump_thread_price') * $forum['newpoints_bump_thread_rate'];

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
            get_thread_link($thread_id),
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