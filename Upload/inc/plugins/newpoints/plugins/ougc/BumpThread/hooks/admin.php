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

use FormContainer;
use MyBB;

use function Newpoints\Core\language_load;
use function Newpoints\Core\sanitize_array_integers;

use const Newpoints\BumpThread\ROOT;

function newpoints_settings_rebuild_start(array $hook_arguments): array
{
    $hook_arguments['settings_directories'][] = ROOT . '/settings';

    return $hook_arguments;
}

function newpoints_templates_rebuild_start(array $hook_arguments): array
{
    $hook_arguments['templates_directories']['bump_thread'] = ROOT . '/templates';

    return $hook_arguments;
}


function newpoints_admin_grouprules_add(FormContainer &$form_container): FormContainer
{
    global $mybb;

    if ($mybb->get_input('action') !== 'add' && $mybb->get_input('action') !== 'edit') {
        return $form_container;
    }

    global $lang, $form, $rule;

    language_load('bump_thread');

    $form_container->output_row(
        $lang->newpoints_bump_thread_grouprate,
        $lang->newpoints_bump_thread_grouprate_desc,
        $form->generate_text_box(
            'bumps_rate',
            (isset($rule['bumps_rate']) ? (float)$rule['bumps_rate'] : 1),
            ['id' => 'bumps_rate']
        ),
        'bumps_rate'
    );

    $form_container->output_row(
        $lang->newpoints_bump_groupforums,
        $lang->newpoints_bump_groupforums_desc,
        $form->generate_text_box(
            'bumps_forums',
            (isset($rule['bumps_forums']) ? sanitize_array_integers($rule['bumps_forums'], true) : ''),
            ['id' => 'bumps_forums']
        ),
        'bumps_forums'
    );

    $form_container->output_row(
        $lang->newpoints_bump_thread_interval,
        $lang->newpoints_bump_thread_interval_desc,
        $form->generate_text_box(
            'bumps_interval',
            (isset($rule['bumps_interval']) ? (int)$rule['bumps_interval'] : ''),
            ['id' => 'bumps_interval']
        ),
        'bumps_interval'
    );

    return $form_container;
}

function newpoints_admin_grouprules_edit(FormContainer &$form_container): FormContainer
{
    return newpoints_admin_grouprules_add($form_container);
}

function newpoints_admin_grouprules_add_insert(array &$insert_array): array
{
    global $mybb;

    // Insert the value..?
    $insert_array['bumps_rate'] = $mybb->get_input('bumps_rate', MyBB::INPUT_FLOAT);

    $insert_array['bumps_forums'] = sanitize_array_integers(
        $mybb->get_input('bumps_forums', MyBB::INPUT_ARRAY),
        true
    );

    $insert_array['bumps_interval'] = $mybb->get_input('bumps_interval', MyBB::INPUT_INT);

    return $insert_array;
}

function newpoints_admin_grouprules_edit_update(array &$insert_array): array
{
    return newpoints_admin_grouprules_add_insert($insert_array);
}

function newpoints_admin_forumrules_add(): bool
{
    global $mybb;

    // If adding a forum rule..
    if ($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit') {
        global $mybb, $lang, $form, $rule, $form_container;

        language_load('bump_thread');

        $form_container->output_row(
            $lang->newpoints_bump_thread_forumrate,
            $lang->newpoints_bump_thread_forumrate_desc,
            $form->generate_text_box(
                'bumps_rate',
                (isset($rule['bumps_rate']) ? (float)$rule['bumps_rate'] : 1),
                ['id' => 'bumps_rate']
            ),
            'bumps_rate'
        );

        $form_container->output_row(
            $lang->newpoints_bump_forumgroups,
            $lang->newpoints_bump_forumgroups_desc,
            $form->generate_text_box(
                'bumps_groups',
                (isset($rule['bumps_groups']) ? sanitize_array_integers($rule['bumps_groups'], true) : ''),
                ['id' => 'bumps_groups']
            ),
            'bumps_groups'
        );

        $form_container->output_row(
            $lang->newpoints_bump_thread_interval,
            $lang->newpoints_bump_thread_interval_desc,
            $form->generate_text_box(
                'bumps_interval',
                (isset($rule['bumps_interval']) ? (int)$rule['bumps_interval'] : ''),
                ['id' => 'bumps_interval']
            ),
            'bumps_interval'
        );
    }

    return true;
}

function newpoints_admin_forumrules_edit(): bool
{
    return newpoints_admin_forumrules_add();
}

function newpoints_admin_forumrules_add_insert(array &$insert_array): array
{
    global $mybb;

    // Insert the value..?
    $insert_array['bumps_rate'] = $mybb->get_input('bumps_rate', MyBB::INPUT_FLOAT);

    $insert_array['bumps_groups'] = sanitize_array_integers(
        $mybb->get_input('bumps_groups', MyBB::INPUT_ARRAY),
        true
    );

    $insert_array['bumps_interval'] = $mybb->get_input('bumps_interval', MyBB::INPUT_INT);

    return $insert_array;
}

function newpoints_admin_forumrules_edit_update(array &$insert_array): array
{
    return newpoints_admin_forumrules_add_insert();
}