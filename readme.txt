=== Admin Clean Up ===
Contributors: digiwise
Tags: admin, cleanup, admin bar, dashboard, disable comments
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up and simplify the WordPress admin interface by removing unnecessary elements.

== Description ==

Admin Clean Up helps you create a cleaner, simpler WordPress admin experience by removing unnecessary elements and customizing the interface.

**Admin Bar**

* Remove WordPress logo and its submenu
* Remove submenus under site name
* Remove "+New" button
* Remove search field
* Hide "Howdy, user" on frontend

**Comments**

* Completely disable comments site-wide
* Removes all comment functionality including menus, pingbacks, and REST API

**Dashboard**

* Remove Welcome Panel
* Remove "At a Glance" widget
* Remove "Activity" widget
* Remove "Quick Draft" widget
* Remove "WordPress Events and News" widget
* Remove or completely disable Site Health

**Admin Menus**

* Hide any admin menu (Posts, Media, Pages, Appearance, Plugins, Users, Tools, Settings)
* Role-based control: hide menus for all users, all except administrators, or all except administrators & editors

**Footer**

* Remove or customize footer text
* Remove or customize version number

**Notices**

* Hide update notices
* Hide all admin notices
* Hide "Screen Options" tab
* Hide "Help" tab

**Media**

* Automatically clean filenames on upload
* Converts special characters to ASCII
* Replaces spaces with dashes
* Converts to lowercase

**Plugin Notices**

* Hide PixelYourSite promotional notices and nag screens

**Updates**

* Control WordPress core auto-updates (disable all, security only, minor only, all updates)
* Disable automatic plugin updates
* Disable automatic theme updates
* Disable update notification emails
* Hide update nags in admin

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → Admin Clean Up to configure

== Frequently Asked Questions ==

= Do the settings affect all users? =

Most settings apply to all users. Admin menu hiding can be configured per role (e.g., hide only for non-administrators).

= Can I restore hidden elements? =

Yes, simply uncheck the options in settings and the elements will be visible again.

= What happens when I disable comments? =

All comment functionality is removed: comments are closed on all posts/pages, existing comments are hidden, the Comments menu is removed, and pingbacks/trackbacks are disabled.

= Will hiding menus block access to those pages? =

No, menus are only hidden visually. Users can still access pages via direct links if they have the capability.

== Screenshots ==

1. Admin Bar settings
2. Dashboard widget settings
3. Admin menu settings with role-based control
4. Media filename cleaning settings

== Changelog ==

= 1.0.0 =
* Initial release
* Admin bar cleanup options
* Complete comment disabling
* Dashboard widget management
* Admin menu hiding with role-based control
* Footer customization
* Notices management
* Media filename cleaning
* Plugin notices hiding (PixelYourSite)
* Auto-update controls
* Swedish translation included

== Upgrade Notice ==

= 1.0.0 =
Initial release of Admin Clean Up.
