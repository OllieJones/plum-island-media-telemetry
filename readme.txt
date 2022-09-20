=== Plum Island Media Telemetry ===
Author URI: https://github.com/OllieJones
Plugin URI: https://github.com/OllieJones/plum-island-media-telemetry
Donate link: 
Contributors:  Ollie Jones
Tags: private
Requires at least: 5.9
Tested up to: 6.0.2
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accepts telemetry requests from other plugins

== Description ==

Accepts Telemetry and sticks it into custom posts.

POST JSON objects to `https://whatever.plumislandmedia.net/wp-json/plumislandmedia/v1/upload`

It stores the JSON in the post in base64 format in a [renderjson] shortcode. When displayed, it shows the JSON in a progressive-disclosure format.

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.


== Installation ==

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add new`
3. Click on Upload Plugin
4. Find `plum-island-media-telemetry.zip` and upload it
4. Click on `Activate plugin`

== Changelog ==

= 1.0.0: August 27, 2022 =
* Birthday of Plum Island Media Telemetry

= 1.0.2: Sept 16, 2022 =
* Better rendering

= 1.0.3: Sept 20, 2022 =
* Show dates and times in post headers.

== Credit ==

* David Caldwell <david@porkrind.org> for renderjson.js
