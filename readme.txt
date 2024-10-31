=== Plugin Name ===
Contributors: shukhrat.ermatov
Donate link: http://muloqot.uz
Tags: oauth, muloqot
Requires at least: 3.0.1
Tested up to: 1.5
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Oauth Client for muloqot networking sites.

This plugin enables OAuth authentication via Muloqot social network for Uzbekistan users.
For use, you must send mail (with your site name, url) to Muloqot social network ( info@muloqot.uz ).
If Muloqot approve you, you will get secret codes. Next: Configure Oauth Client in Settings -> Oauth Client.


== Installation ==

1. Extract archive to folder oauth-client-muloqot
2. Upload `oauth-client-muloqot` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Request secret keys from SE Muloqot Admins (muloqot.uz)
5. Configure Oauth Client in Settings -> Oauth Client

== Frequently Asked Questions ==

= How to change OAuth settings =

Go to Settings->OAuthClient

== Screenshots ==

1. Settings
2. Login form
3. Authorization

== Changelog ==

= 1.6 =
Fixed avatar

= 1.5 =
Fixed profile url

= 1.2 =
* Secret data replaced to database

= 0.4 =
* Secret data were separated from the code for easy customization

== Upgrade Notice ==

= 1.2 =
* In new version secret data replaced to database!

= 0.4 =
Secret data were separated from the code for easy customization