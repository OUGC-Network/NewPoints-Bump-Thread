<?php

/***************************************************************************
 *
 *    Newpoints Bump Thread plugin (/inc/plugins/newpoints/plugins/ougc/BumpThread/hooks/admin.php)
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

namespace Newpoints\BumpThread\Hooks\Admin;

use function Newpoints\Core\language_load;

use const Newpoints\BumpThread\Admin\FIELDS_DATA;
use const Newpoints\BumpThread\ROOT;

function newpoints_settings_rebuild_start(array $hook_arguments): array
{
    language_load('bump_thread');

    $hook_arguments['settings_directories'][] = ROOT . '/settings';

    return $hook_arguments;
}

function newpoints_templates_rebuild_start(array $hook_arguments): array
{
    $hook_arguments['templates_directories']['bump_thread'] = ROOT . '/templates';

    return $hook_arguments;
}

function newpoints_admin_user_groups_edit_graph_start(array &$hook_arguments): array
{
    language_load('bump_thread');

    $hook_arguments['data_fields'] = array_merge(
        $hook_arguments['data_fields'],
        FIELDS_DATA['usergroups']
    );

    return $hook_arguments;
}

function newpoints_admin_user_groups_edit_commit_start(array &$hook_arguments): array
{
    return newpoints_admin_user_groups_edit_graph_start($hook_arguments);
}

function newpoints_admin_formcontainer_end_start(array &$hook_arguments): array
{
    return newpoints_admin_forum_management_edit_commit_start($hook_arguments);
}

function newpoints_admin_forum_management_edit_commit_start(array &$hook_arguments): array
{
    language_load('bump_thread');

    $hook_arguments['data_fields'] = array_merge(
        $hook_arguments['data_fields'],
        FIELDS_DATA['forums']
    );

    return $hook_arguments;
}