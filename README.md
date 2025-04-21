<p align="center">
    <a href="" rel="noopener">
        <img width="700" height="400" src="https://github.com/user-attachments/assets/c5792e53-89c9-4fdf-a6aa-de96f244a1c0" alt="Project logo">
    </a>
</p>

<h3 align="center">NewPoints Bump Thread</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/NewPoints--Bump-Thread.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-OUGC-Network/NewPoints--Bump-Thread.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Allows users to bump their own threads for a price.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
    - [Dependencies](#dependencies)
    - [File Structure](#file_structure)
    - [Install](#install)
    - [Update](#update)
    - [Template Modifications](#template_modifications)
- [Settings](#settings)
- [Templates](#templates)
- [Usage](#usage)
    - [Forums](#usage_forums)
    - [Groups](#usage_groups)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

Allows users to bump their own threads for a price.

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8
- PHP >= 7
- [NewPoints](https://github.com/OUGC-Network/NewPoints) >= 3

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── plugins
   │ │ ├── newpoints
   │ │ │ ├── languages
   │ │ │ │ ├── english
   │ │ │ │ │ ├── admin
   │ │ │ │ │ │ ├── newpoints_bump_thread.lang.php
   │ │ │ │ │ ├── newpoints_bump_thread.lang.php
   │ │ │ │ ├── espanol
   │ │ │ │ │ ├── admin
   │ │ │ │ │ │ ├── newpoints_bump_thread.lang.php
   │ │ │ │ │ ├── newpoints_bump_thread.lang.php
   │ │ │ ├── plugins
   │ │ │ │ ├── ougc
   │ │ │ │ │ ├── BumpThread
   │ │ │ │ │ │ ├── hooks
   │ │ │ │ │ │ │ ├── admin.php
   │ │ │ │ │ │ │ ├── forum.php
   │ │ │ │ │ │ │ ├── shared.php
   │ │ │ │ │ │ ├── settings
   │ │ │ │ │ │ │ ├── bump_thread.json
   │ │ │ │ │ │ ├── templates
   │ │ │ │ │ │ │ ├── showthread_button.html
   │ │ │ │ │ │ ├── admin.php
   │ │ │ │ ├── newpoints_bump_thread.php
   │ │ │ ├── newpoints_bump_thread.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package.
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Newpoints » Plugins_ and install this plugin by clicking _Install & Activate_.
4. Browse to _Newpoints » Settings_ to manage the plugin settings.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.
4. Browse to _NewPoints_ to manage NewPoints modules.

### Template Modifications <a name = "template_modifications"></a>

It is required that you edit the following template for each of your themes.

1. Place `{$newpoints_bump_thread}` before `{$newreply}` in the `showthread` template to display the bump button.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Main Settings

- **Price For Bumping Threads** `decimal`
    - _Select the amount of points for users to pay for each thread bump they make._
- **Allow Closed Threads** `yesNO`
    - _Allow users to bump closed threads._
- **Allow Moderator Bypass** `yesNO`
    - _Allow moderators to bump threads in the forums they moderate._

[Go up to Table of Contents](#table_of_contents)

## 📐 Templates <a name = "templates"></a>

The following is a list of templates available for this plugin.

- `newpoints_bump_thread_showthread_button`
    - _front end_;

[Go up to Table of Contents](#table_of_contents)

## 📖 Usage <a name="usage"></a>

The following is a description of additional configuration for this plugin.

### Forums <a name="usage_forums"></a>

Two new settings are added to forums.

- **Yes, allow users to bump threads**
- **Thread Bump Rate**
    - _Set a rate that will be applied to thread bumps in this forum._

### Groups <a name="usage_groups"></a>

Two new settings are added to groups.

- **Can bump threads?**
- **Thread Bump Interval**
    - _Number of minutes between each thread bump._

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/NewPoints--Bump-Thread/contributors) who
participated in
this
project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-159249.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)