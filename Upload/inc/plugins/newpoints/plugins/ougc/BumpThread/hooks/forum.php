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

use function Newpoints\Core\language_load;
use function Newpoints\Core\control_db;
use function Newpoints\Core\templates_get;

function global_start(): bool
{
    if (THIS_SCRIPT == 'showthread.php') {
        global $templatelist;

        if (isset($templatelist)) {
            $templatelist .= ',';
        } else {
            $templatelist = '';
        }

        $templatelist .= 'newpoints_bump_thread';
    }

    return true;
}

function showthread_start09(): bool
{
    global $thread, $mybb, $lang;

    language_load('bump_thread');

    if ($thread['closed']) {
        return false;
    }

    // Get newpoints rules
    $forumrules = newpoints_getrules('forum', $thread['fid']);

    $groupsrules = newpoints_getrules('group', $mybb->user['usergroup']);

    if (isset($groupsrules['bumps_forums'])) {
        $mybb->settings['newpoints_bump_thread_forums'] = $groupsrules['bumps_forums'];
    }

    if ($mybb->settings['newpoints_bump_thread_forums'] != -1 && !strpos(
            ',' . $mybb->settings['newpoints_bump_thread_forums'] . ',',
            ',' . $thread['fid'] . ','
        )) {
        return false;
    }

    if (isset($forumrules['bumps_groups'])) {
        $mybb->settings['newpoints_bump_thread_groups'] = $forumrules['bumps_groups'];
    }

    if ($mybb->settings['newpoints_bump_thread_groups'] != -1 && !is_member(
            $mybb->settings['newpoints_bump_thread_groups']
        )) {
        return false;
    }

    // Interval time
    // The issue here is, should we use the largest interval ratio or the lowest one? This is "easy" to solve, allowing administrators to make use of the "-" sign inside the value to determine how it should work.
    // The real issue, is if whether forum or groups rules should be checked before any other, the order can modify the end result. I decided to go with forum rule first.
    $interval = (int)$mybb->settings['newpoints_bump_thread_interval'];

    if (isset($forumrules['bumps_interval']) && $forumrules['bumps_interval'] >= 0) {
        $finterval = (int)$forumrules['bumps_interval'];

        if (my_strpos($forumrules['bumps_interval'], '-')) {
            $overwrite = ($finterval < $interval);
        } else {
            $overwrite = ($finterval > $interval);
        }

        if ($overwrite) {
            $interval = $finterval;
        }
    }

    if (isset($groupsrules['bumps_interval']) && $groupsrules['bumps_interval'] >= 0) {
        $ginterval = (int)$groupsrules['bumps_interval'];

        if (my_strpos($groupsrules['bumps_interval'], '-')) {
            $overwrite = ($ginterval < $interval);
        } else {
            $overwrite = ($ginterval > $interval);
        }

        if ($overwrite) {
            $interval = $ginterval;
        }
    }

    $lastpostbump = my_date('relative', $thread['lastpostbump']);

    $threadlink = get_thread_link($thread['tid'], 0, 'bump');

    // Show the button.
    if ($permission = (is_moderator($thread['fid']) || ($mybb->user['uid'] && $thread['uid'] == $mybb->user['uid']))) {
        global $templates, $theme, $newpoints_bump_thread;

        $title = $lang->newpoints_bump_thread;

        if ($thread['lastpostbump'] + $interval * 60 > TIME_NOW) {
            $title = $lang->sprintf($lang->newpoints_bump_thread_last, $lastpostbump);
        }

        $newpoints_bump_thread = eval(templates_get('bump_thread'));
    }

    if ($mybb->get_input('action') != 'bump') {
        return false;
    }

    // Request
    if ($permission) {
        // Set $points based in groupsrules and forumrules.
        $points = (float)$mybb->settings['newpoints_bump_thread_points'] * (float)($groupsrules['bumps_rate'] ?? 1) * (float)($forumrules['bumps_rate'] ?? 1);

        // If is thread author and required points are higher that current user points, show error page.
        if ($thread['uid'] == $mybb->user['uid'] && $points > (float)$mybb->user['newpoints']) {
            error($lang->sprintf($lang->newpoints_bump_thread_error_points, newpoints_format_points($points)));
        }

        // Is the last bump was not so long ago (from settings), show error.
        if ($thread['lastpostbump'] + $interval * 60 > TIME_NOW || $mybb->user['lastpostbump'] + $interval * 60 > TIME_NOW) {
            error($lang->sprintf($lang->newpoints_bump_thread_error_interval, my_number_format($interval)));
        }

        // They passed trow here, so lets bump the thread!!
        global $db;

        $db->update_query('threads', ['lastpostbump' => TIME_NOW], 'tid=' . (int)$thread['tid']);

        $db->update_query('users', ['lastpostbump' => TIME_NOW], 'uid=' . (int)$mybb->user['uid']);

        $db->delete_query('forumsread', 'fid=\'' . (int)$thread['fid'] . '\''); // someone might complain..

        $db->delete_query('threadsread', 'tid=\'' . (int)$thread['tid'] . '\'');
        // need we to modify search queries? may be..

        // If current user is thread author, remove points, otherwise, don't (so admins/global_mods can bump as much threads how they want, as long as they are not the original authors).
        if ($thread['uid'] == $mybb->user['uid']) {
            newpoints_addpoints($mybb->user['uid'], -$points);
        }

        $threadlink = get_thread_link($thread['tid']);

        // Log it.
        newpoints_log(
            'bump',
            $mybb->settings['bburl'] . '/' . $threadlink,
            $mybb->user['username'],
            $mybb->user['uid']
        );

        redirect($threadlink, $lang->newpoints_bump_thread_success_message, $lang->newpoints_bump_thread_success_title);
    }

    error_no_permission();

    return true;
}

function forumdisplay_start09(): bool
{
    global $mybb;
    global $foruminfo;

    if (!isset($mybb->input['sortby']) && !empty($foruminfo['defaultsortby'])) {
        $mybb->input['sortby'] = $foruminfo['defaultsortby'];
    }

    switch ($mybb->get_input('sortby')) {
        case 'subject':
        case 'replies':
        case 'views':
        case 'starter':
        case 'rating':
        case 'started':
            break;
        default:
            control_db(
                '
				function query($string, $hide_errors=0, $write_query=0)
				{
					if(!$done && strpos($string, \'t.sticky\') !== false && strpos($string, \'lastpost\') !== false)
					{
						$string = str_replace(array("lastpost ", "t.lastpost "), array("lastpostbump ", "t.lastpostbump "), $string);
					}

					return parent::query($string, $hide_errors, $write_query);
				}
			'
            );
            break;
    }

    return true;
}