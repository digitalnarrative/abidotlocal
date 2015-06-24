=== Plugin Name ===
Contributors: booruguru
Tags: wiki, collaborative, buddypress, bbpress, frontend, subscriptions, multisite
Requires at least: 3.7
Tested up to: 3.8.1
Stable tag: 1.2.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


UserPress is a collaborative media (wiki) platform built for WordPress.



== Description ==
UserPress allows you to turn your WordPress site into a powerful wiki platform.

This plugin includes a theme specifically designed for UserPress, but you can use virtually any WordPress theme you desire.

We infrequently check the WordPress support forums so if you need help or would like to offer feedback please visit our official website  http://www.userpress.org


== Installation ==

1. Upload `userpress.zip`via your wp-admin plugins manager.

2. Update your permalinks (via the wp-admin settings page) once the plugin is activated. (Make sure your permalinks use post-names/prettylinks in their URIs).

3. Done. Visit yourwebsite.com/wiki/frontpage/ in order to view your new wiki section.

Additional documentation can be found at http://www.userpress.org


== Screenshots ==

1. Wiki Page for logged-in user
2. Wiki Edit Screen 
3. Subscriptions Management (via UserPress + BuddyPress)

== Frequently Asked Questions ==

= How can I access my wiki home page? =

http://yourwebsite.com/wiki/frontpage/

Once you activate UserPress, a wiki page called "Frontpage" is automatically created. This will serve as the main page of your WordPress wiki (http://yourwebsite.com/wiki/frontpage/). 

Also, if you try to access "http://yourwebsite.com/wiki/" UserPress will automatically forward you to the frontpage.


= How can I post wiki articles? =

You can post wiki articles by visiting "http://yourwebsite.com/wiki/?action=create". (You can post wiki articles using wp-admin under the "Wiki" sidebar menu item.) 


== Changelog ==

= 2.0.9 =
* Fixed improperly defined isset conditional statement / fatal error

= 2.0.4 =
* Fixed TOC XML glitch 
* Fixed extra header space issue

= 2.0.3 =
* Fixed HTML tag restrictions issue with the <pre> tag

= 2.0.1 =

* Fixed TOC bug that inserted DOCTYPE! code into post body
* Fixed bug that prevented from know it is up-to-date after being updated.

= 2.0 =

* Added UserPress User Guide external link to wp-admin "Wiki" sub-menu 
* Page Tree Widget: "Administrator" is now selected by default
* Page Tree Widget: "Administrator" now also includes multisite network administrator
* Fixed TOC Bug: If heading has a link, the link broke the TOC "contentsÓ list
* Added iframe modal for internal wiki links
* Added Wiki Frontpage on WordPress Frontapge functionality
* Added "recently modified" option to Etc. wiki tab
* Fixed subscribe button glitch (causing it to appear next to first archive/search result item)
* Fixed Table of Contents numbering and formatting
* Fixed wiki tabs padding (and Safari glitch)
* Condensed line-height for page tree items
* Fixed usertheme wiki discuss page formatting
- Fixed page tree positioning glitch
- Enhanced restricted HTML tags functionality



= 1.3.3 =

* Fixed category and flag modal line breaks

= 1.3.2 =

* Fixed dynamic frontpage link

= 1.3.1 =

* Added customer updater functionality


= 1.2.8 =

* fixed "create new page" error

= 1.2.7 =

* fixed bpsubscriptions default letter-spacing
* changed usertheme header bottom-border from 2px to 1px
* added placeholder to create new wiki sub-page title field
* changed "leave a reply" to "post a reply" but only for UserTheme
* fixed featured image issue on "create new wiki" page
* added underline to h3 CSS
* h3 automatically formatted  if it is the first line in the content
* fixed page tree css glitch on 404 pages
