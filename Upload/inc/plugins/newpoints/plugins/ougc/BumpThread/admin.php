<?php

/***************************************************************************
 *
 *    Newpoints Bump Thread plugin (/inc/plugins/newpoints/plugins/ougc/BumpThread/admin.php)
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

namespace Newpoints\BumpThread\Admin;

use function Newpoints\Admin\db_verify_columns;
use function Newpoints\Admin\plugin_library_load;
use function Newpoints\Core\language_load;
use function Newpoints\Core\log_remove;
use function Newpoints\Core\rules_rebuild_cache;
use function Newpoints\Core\settings_rebuild;
use function Newpoints\Core\templates_rebuild;
use function Newpoints\Core\templates_remove;

const FIELDS_DATA = [
    'threads' => [
        'lastpostbump' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'users' => [
        'lastpostbump' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'newpoints_grouprules' => [
        'bumps_rate' => [
            'type' => 'FLOAT',
            'unsigned' => true,
            'default' => 1
        ],
        'bumps_forums' => [
            'type' => 'TEXT',
            'null' => true
        ],
        'bumps_interval' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'newpoints_forumrules' => [
        'bumps_rate' => [
            'type' => 'FLOAT',
            'unsigned' => true,
            'default' => 1
        ],
        'bumps_groups' => [
            'type' => 'TEXT',
            'null' => true
        ],
        'bumps_interval' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ]
];

function plugin_information(): array
{
    global $lang;

    language_load('bump_thread');

    $lang->newpoints_bump_thread_desc .= '<br/><br/><p style="padding-left:10px;margin:0;">' . $lang->newpoints_bump_thread_credits . '</p>';

    return [
        'name' => 'Bump Thread',
        'description' => $lang->newpoints_bump_thread_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '2.0.0',
        'versioncode' => 2000,
        'compatibility' => '3*'
    ];
}

function plugin_activation(): bool
{
    global $cache;

    language_load();

    $plugin_information = plugin_information();

    plugin_library_load();

    settings_rebuild();

    templates_rebuild();

    // Insert/update version into cache
    $plugins_list = $cache->read('ougc_plugins');

    if (!$plugins_list) {
        $plugins_list = [];
    }

    if (!isset($plugins_list['newpoints_bump_thread'])) {
        $plugins_list['newpoints_bump_thread'] = $plugin_information['versioncode'];
    }

    db_verify_columns(FIELDS_DATA);

    rules_rebuild_cache();

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    $plugins_list['newpoints_bump_thread'] = $plugin_information['versioncode'];

    $cache->update('ougc_plugins', $plugins_list);

    return true;
}

function plugin_deactivation(): bool
{
    return true;
}

function plugin_installation(): bool
{
    global $db;

    db_verify_columns(FIELDS_DATA);

    $db->update_query('threads', ['lastpostbump' => '`lastpost`'], '', '', true);

    return true;
}

function plugin_is_installed(): bool
{
    static $isInstalled = null;

    if ($isInstalled === null) {
        global $db;

        $isInstalledEach = true;

        foreach (FIELDS_DATA as $table_name => $table_columns) {
            foreach ($table_columns as $field_name => $field_data) {
                $isInstalledEach = $db->field_exists($field_name, $table_name) && $isInstalledEach;
            }
        }

        $isInstalled = $isInstalledEach;
    }

    return $isInstalled;
}

function plugin_uninstallation(): bool
{
    global $db, $cache;

    log_remove(['bump']);

    foreach (FIELDS_DATA as $table_name => $table_columns) {
        if ($db->table_exists($table_name)) {
            foreach ($table_columns as $field_name => $field_data) {
                if ($db->field_exists($field_name, $table_name)) {
                    $db->drop_column($table_name, $field_name);
                }
            }
        }
    }


    newpoints_remove_settings(
        'newpoints_bump_thread_interval, newpoints_bump_thread_forums, newpoints_bump_thread_groups, newpoints_bump_thread_points'
    );

    templates_remove('newpoints_bump_thread_showthread_button');

    // Delete version from cache
    $plugins_list = (array)$cache->read('ougc_plugins');

    if (isset($plugins_list['newpoints_bump_thread'])) {
        unset($plugins_list['newpoints_bump_thread']);
    }

    if (!empty($plugins_list)) {
        $cache->update('ougc_plugins', $plugins_list);
    } else {
        $cache->delete('ougc_plugins');
    }

    return true;
}