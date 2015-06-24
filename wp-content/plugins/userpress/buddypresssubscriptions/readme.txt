=== Plugin Name ===
Contributors: Peter K Morrison @shastapete
Donate link: http://userpress.org/
Tags: buddypress, subscription
Requires at least: 3.8.1
Tested up to: 3.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin to add post/page subscription manager to BuddyPress instalation of WordPress

== Description ==

It has a widget available in the editor

you can also call the subscribe button with a php function

	bppostsubbutton($postID, $mode)
	
	$postID defaults to current page or post
	$mode defaults to standard operation
		alternatively you can define it as 'delete' and it will behave like the button in the Manage Subscription Page

Lastly, you can use the short code [bpsubbutton],

== Installation ==
If you downloaded this plugin separately...
upload and uncompress bp-subscriptions.zip to your plugin folder and activate it in your plugin editor

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==
= 1.1.1 =
* Added Site Name to What's New and Manage Subs if Site is Multi Site

= 1.1.0 =
* Changed subscription storage from user meta to database table
* Added support for Multisite
* Changed all usermeta queries to custom database queries
* Caught an error in 'userpress_wiki_revision_note' meta handling and fixed

= 1.0.0 =
* Stable release, same as 0.9.4

= 0.9.4 =
* Fixed an issue where 
* Fixed a warning on install
* Fixed Permalinks in What's new
* Commented out DEBUG alets

= 0.9.3 =
* Changed 'revision_note' meta references to 'userpress_wiki_revision_note'
* Added a check to not shot "Subscribe" button on non-pages/posts
* Changed status javascript variable to substatus to avoid sporadic issues in Firefox
* Changed What's New behavior to only post data after user subscribed
* DEBUG javascript alerts kept in place on purpose

= 0.9.2 =
* Fixed issue of including all child pages
* Removed tripple Subscriptions not found
* changed .live() to .on() jQuery calls

= 0.9.1 =
* Fixed some array issues with a fresh install.
* Fixed some CSS issues with the button and pagination links.

= 0.9 =
* Original launch of plugin Beta.

== Upgrade Notice ==
= 1.1.0 =
* You will lose all subscriptions on update to this version


== Arbitrary section ==

