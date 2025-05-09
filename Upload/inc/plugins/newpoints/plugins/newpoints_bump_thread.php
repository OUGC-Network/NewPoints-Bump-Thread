<?php

/***************************************************************************
 *
 *    NewPoints Bump Thread plugin (/inc/plugins/newpoints/plugins/newpoints_bump_thread.php)
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

use function NewPoints\BumpThread\Admin\plugin_activation;
use function NewPoints\BumpThread\Admin\plugin_information;
use function NewPoints\BumpThread\Admin\plugin_installation;
use function NewPoints\BumpThread\Admin\plugin_is_installed;
use function NewPoints\BumpThread\Admin\plugin_uninstallation;
use function NewPoints\Core\add_hooks;

use function NewPoints\Core\templates_get;

use const NewPoints\BumpThread\ROOT;
use const NewPoints\ROOT_PLUGINS;

defined('IN_MYBB') || die('Direct initialization of this file is not allowed.');

define('NewPoints\BumpThread\ROOT', ROOT_PLUGINS . '/BumpThread');

if (defined('IN_ADMINCP')) {
    require_once ROOT . '/admin.php';

    require_once ROOT . '/hooks/admin.php';

    add_hooks('NewPoints\BumpThread\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    add_hooks('NewPoints\BumpThread\Hooks\Forum');
}

require_once ROOT . '/hooks/shared.php';

add_hooks('NewPoints\BumpThread\Hooks\Shared');

function newpoints_bump_thread_info(): array
{
    return plugin_information();
}

function newpoints_bump_thread_activate(): bool
{
    return plugin_activation();
}

function newpoints_bump_thread_install(): bool
{
    return plugin_installation();
}

function newpoints_bump_thread_uninstall(): bool
{
    return plugin_uninstallation();
}

function newpoints_bump_thread_is_installed(): bool
{
    return plugin_is_installed();
}

function newpoints_bump_thread_get_template(string $template_name = '', bool $enable_html_comments = true): string
{
    return templates_get($template_name, $enable_html_comments, ROOT, 'bump_thread_');
}

(function () {
    global $groupzerolesser, $grouppermbyswitch;

    $groupzerolesser[] = 'newpoints_rate_bump_thread';

    $grouppermbyswitch['newpoints_rate_bump_thread'] = 'newpoints_bump_thread_can_use';
})();