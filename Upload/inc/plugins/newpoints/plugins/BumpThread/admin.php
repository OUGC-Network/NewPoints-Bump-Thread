<?php

/***************************************************************************
 *
 *    Newpoints Bump Thread plugin (/inc/plugins/newpoints/plugins/ougc/BumpThread/admin.php)
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

namespace Newpoints\BumpThread\Admin;

use function Newpoints\Admin\db_build_field_definition;
use function Newpoints\Admin\db_verify_columns;
use function Newpoints\Core\language_load;
use function Newpoints\Core\log_remove;
use function Newpoints\Core\settings_remove;
use function Newpoints\Core\templates_remove;

const FIELDS_DATA = [
    'threads' => [
        'newpoints_bump_thread_stamp' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'users' => [
        'newpoints_bump_thread_last_stamp' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'usergroups' => [
        'newpoints_bump_thread_can_use' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1,
            'formType' => 'checkBox'
        ],
        'newpoints_bump_thread_interval' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 60,
            'formType' => 'numericField',
            'formOptions' => [
                //'min' => 0,
                //'step' => 0.01,
            ]
        ],
    ],
    'forums' => [
        'newpoints_bump_thread_enable' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1,
            'formType' => 'checkBox'
        ],
        'newpoints_bump_thread_rate' => [
            'type' => 'FLOAT',
            'unsigned' => true,
            'default' => 1,
            'formType' => 'numericField',
            'formOptions' => [
                //'min' => 0,
                'step' => 0.01,
            ]
        ],
    ]
];

function plugin_information(): array
{
    global $lang;
    global $action_file;

    language_load('bump_thread');

    if ($action_file === 'plugins.php') {
        $lang->newpoints_bump_thread_desc .= '<br/><br/><p style="padding-left:10px;margin:0;">' . $lang->newpoints_bump_thread_credits . '</p>';
    }

    return [
        'name' => 'Bump Thread',
        'description' => $lang->newpoints_bump_thread_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '3.0.0',
        'versioncode' => 3000,
        'compatibility' => '3*'
    ];
}

function plugin_activation(): bool
{
    global $cache;

    language_load('bump_thread');

    $plugin_information = plugin_information();

    $plugins_list = $cache->read('newpoints_plugins_versions');

    if (!$plugins_list) {
        $plugins_list = [];
    }

    if (!isset($plugins_list['newpoints_bump_thread'])) {
        $plugins_list['newpoints_bump_thread'] = $plugin_information['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    if ($plugins_list['newpoints_bump_thread'] < 3000) {
        global $db;

        foreach (
            [
                'newpoints_grouprules' => ['bumps_interval', 'bumps_rate', 'bumps_forums'],
                'newpoints_forumrules' => ['bumps_interval', 'bumps_rate', 'bumps_groups'],
            ] as $table_name => $table_columns
        ) {
            if ($db->table_exists($table_name)) {
                foreach ($table_columns as $field_name => $field_data) {
                    if ($db->field_exists($field_name, $table_name)) {
                        $db->drop_column($table_name, $field_name);
                    }
                }
            }
        }

        if ($db->field_exists('lastpostbump', 'threads') && !$db->field_exists(
                'newpoints_bump_thread_stamp',
                'threads'
            )) {
            $db->rename_column(
                'threads',
                'lastpostbump',
                'newpoints_bump_thread_stamp',
                db_build_field_definition(FIELDS_DATA['threads']['newpoints_bump_thread_stamp'])
            );
        }

        if ($db->field_exists('lastpostbump', 'users') && !$db->field_exists(
                'newpoints_bump_thread_last_stamp',
                'users'
            )) {
            $db->rename_column(
                'users',
                'lastpostbump',
                'newpoints_bump_thread_last_stamp',
                db_build_field_definition(FIELDS_DATA['users']['newpoints_bump_thread_last_stamp'])
            );
        }

        settings_remove(
            [
                'interval',
                'forums',
                'groups',
                'points'
            ],
            'newpoints_bump_thread_'
        );
    }

    /*~*~* RUN UPDATES END *~*~*/

    db_verify_columns(FIELDS_DATA);

    $plugins_list['newpoints_bump_thread'] = $plugin_information['versioncode'];

    $cache->update('newpoints_plugins_versions', $plugins_list);

    return true;
}

function plugin_installation(): bool
{
    global $db;

    db_verify_columns(FIELDS_DATA);

    $db->update_query('threads', ['newpoints_bump_thread_stamp' => '`lastpost`'], '', '', true);

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

    log_remove(['bump_thread', 'bump']);

    foreach (FIELDS_DATA as $table_name => $table_columns) {
        if ($db->table_exists($table_name)) {
            foreach ($table_columns as $field_name => $field_data) {
                if ($db->field_exists($field_name, $table_name)) {
                    $db->drop_column($table_name, $field_name);
                }
            }
        }
    }

    settings_remove(
        [
            'price',
            'allow_closed_threads'
        ],
        'newpoints_bump_thread_'
    );

    templates_remove(['showthread_button'], 'newpoints_bump_thread_');

    // Delete version from cache
    $plugins_list = (array)$cache->read('newpoints_plugins_versions');

    if (isset($plugins_list['newpoints_bump_thread'])) {
        unset($plugins_list['newpoints_bump_thread']);
    }

    if (!empty($plugins_list)) {
        $cache->update('newpoints_plugins_versions', $plugins_list);
    } else {
        $cache->delete('newpoints_plugins_versions');
    }

    return true;
}