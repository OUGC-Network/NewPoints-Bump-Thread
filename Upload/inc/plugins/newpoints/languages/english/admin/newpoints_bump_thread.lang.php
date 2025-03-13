<?php

/***************************************************************************
 *
 *    NewPoints Bump Thread plugin (/inc/plugins/newpoints/languages/english/admin/newpoints_bump_thread.lang.php)
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

$l = [
    'newpoints_bump_thread' => 'Bump Thread',
    'newpoints_bump_thread_desc' => 'Allows users to bump their own threads without posting on exchange of points.',
    'newpoints_bump_thread_credits' => 'Original plugin coded by <a href="https://mybbhacks.zingaburga.com/member.php?action=profile&uid=1">Zinga Burga from MyBBHacks</a>.',

    'setting_group_newpoints_bump_thread' => 'Bump Thread',
    'setting_group_newpoints_bump_thread_desc' => 'Allows users to bump their own threads for a price.',
    'setting_newpoints_bump_thread_action_name' => 'Action Page Name',
    'setting_newpoints_bump_thread_action_name_desc' => 'Select the action input name to use for this feature.',
    'setting_newpoints_bump_thread_enable_dvz_stream' => 'Enable DVZ Stream Integration',
    'setting_newpoints_bump_thread_enable_dvz_stream_desc' => 'Enable DVZ Stream integration for the bumped threads.',
    'setting_newpoints_bump_thread_price' => 'Bump Price',
    'setting_newpoints_bump_thread_price_desc' => 'Select the amount of points for users to be charged for each thread bump.',
    'setting_newpoints_bump_thread_allow_closed_threads' => 'Allow Closed Threads',
    'setting_newpoints_bump_thread_allow_closed_threads_desc' => 'Allow users to bump closed threads.',
    'setting_newpoints_bump_thread_allow_moderator_bypass' => 'Allow Moderator Bypass',
    'setting_newpoints_bump_thread_allow_moderator_bypass_desc' => 'Allow moderators to bump threads in the forums they moderate.',

    'newpoints_forums_bump_thread_enable' => 'Yes, allow users to bump threads',
    'newpoints_forums_bump_thread_rate' => 'Thread Bump Rate<br /><small class="input">Set a rate that will be applied to thread bumps in this forum. </small><br />',

    'newpoints_user_groups_bump_thread_can_use' => 'Can bump threads?',
    'newpoints_user_groups_bump_thread_interval' => 'Thread Bump Interval<br /><small class="input">Number of minutes between each thread bump. <code style="color: darkorange;">Lowest from all groups.</code> </small><br />',

    'newpoints_user_groups_rate_bump_thread' => 'Bump Thread Rate <code style="color: darkorange;">This works as a percentage. So "0" = user does not pay anything "100" = users pay full price, "200" = user pays twice the price, etc.</code><br /><small class="input">The bump thread rate for this group, used when subtracting points from users when they bump a thread (multiplies the <code>Bump Thread Price</code> permission). Default is <code>100</code>.</small><br />',

    'newpoints_forums_rate_bump_thread' => 'Bump Thread Rate<br /><small class="input">The rate for bumping threads in this forum. Default is <code>1</code>.</small><br />'
];