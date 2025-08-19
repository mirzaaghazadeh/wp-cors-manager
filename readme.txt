=== CORS Manager ===
Contributors: navidmirzaaghazadeh
Tags: cors, cross-origin, headers, security, api
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive WordPress plugin for managing Cross-Origin Resource Sharing (CORS) settings.

== Description ==

CORS Manager is a comprehensive WordPress plugin for managing Cross-Origin Resource Sharing (CORS) settings. This plugin allows administrators to easily configure CORS headers and manage allowed origins through a user-friendly interface in the WordPress admin panel.

= Features =

* **Easy CORS Configuration**: Simple toggle to enable/disable CORS
* **Origin Management**: Add multiple allowed origins with validation
* **Method Control**: Select which HTTP methods to allow (GET, POST, PUT, DELETE, etc.)
* **Header Management**: Configure allowed headers for cross-origin requests
* **Credentials Support**: Option to allow credentials in CORS requests
* **Security Focused**: Built with security best practices in mind
* **User-Friendly Interface**: Clean, intuitive admin interface under Tools menu
* **Real-time Status**: View current CORS configuration at a glance
* **Help Documentation**: Built-in help and security guidelines

= Security Note =

Never use `*` (wildcard) for allowed origins in production environments. Always specify exact domains that should be allowed to make cross-origin requests.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cors-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to **Tools > CORS Manager** in your WordPress admin panel
4. Configure your CORS settings as needed

== Frequently Asked Questions ==

= What is CORS? =

CORS (Cross-Origin Resource Sharing) is a security feature implemented by web browsers that blocks web pages from making requests to a different domain than the one serving the web page, unless the server explicitly allows it.

= Why do I need this plugin? =

If you're building APIs, web applications, or need to allow specific domains to access your WordPress site's resources, you'll need to configure CORS headers properly. This plugin makes it easy to manage these settings.

= Is it safe to use wildcards (*) for origins? =

No, using wildcards for allowed origins in production is not recommended as it can expose your site to security risks. Always specify exact domains.

== Screenshots ==

1. CORS Manager admin interface
2. Origin management settings
3. HTTP methods configuration

== Changelog ==

= 1.0.0 =
* Initial release
* Basic CORS configuration
* Origin management
* Method and header control
* Admin interface

== Upgrade Notice ==

= 1.0.0 =
Initial release of CORS Manager plugin.

== License ==

This plugin is licensed under the GPLv2 or later.

Copyright (C) 2024 Navid Mirzaaghazadeh

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA