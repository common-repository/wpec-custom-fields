=== WPEC Custom Fields ===
Contributors: dpe415
Tags: wpec, e-commerce, wp e-commerce, custom fields, custom meta
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 0.2

Replaces WPEC's custom meta functionality with WordPress's default custom fields capabilities.

== Description ==

WPEC Custom Fields replaces the default WP E-Commerce (WPEC) product custom meta functionality with WordPress's default custom fields.  The plugin preserves existing custom meta entries and allows users to enter multiple values for the same key name (fixing an [existing issue](http://code.google.com/p/wp-e-commerce/issues/detail?id=552) in WPEC).

== Installation ==

1. Upload the folder `wpec-custom-fields` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create a new product or edit an existing product in Wp E-Commerce.
1. Add or edit custom fields on WPEC products just like a regular WordPress post.

== Frequently Asked Questions ==

= What version(s) of WP E-Commerce does this plugin work with? =

The plugin has been tested with versions 3.8.6+, but should work with any version 3.8 and above.

= What about existing WPEC custom meta? =

The plugin works with existing meta.  Any existing custom meta items will show up as regular WordPress custom fields as normal.

= Why are there numbered custom field keys in the select drop-down box? =

Previously-entered WPEC custom meta entries could be tied to a numeric "key" as well as the user-entered, text key.  It is safe to ignore these entries and even delete them from the database if you so choose.  A future version of this plugin may even take care of this.

== Screenshots ==

1. The WPEC add/edit product screen showing the "Advanced Settings" box with no custom meta functionality and the standard WordPress "Custom Fields" box.

== Changelog ==

= 0.2 =
* Fixed missing admin stylesheet.

= 0.1.1 =
* 'Readme.txt' updates.

= 0.1 =
* First post!
