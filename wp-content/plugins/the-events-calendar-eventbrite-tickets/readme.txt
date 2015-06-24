=== The Events Calendar: Eventbrite Tickets ===

Contributors: ModernTribe, roblagatta, jazbek, ckpicker, peterchester, reid.peifer, shane.pearlman, barryhughes, leahkoerper, neillmcshea, brook-tribe, mdbitz, borkweb, zbtirrell, bordoni, joshlimecuda, momnt, geoffgraham
Tags: widget, events, simple, tooltips, grid, month, list, calendar, event, venue, eventbrite, registration, tickets, ticketing, eventbright, api, dates, date, plugin, posts, sidebar, template, theme, time, google maps, google, maps, conference, workshop, concert, meeting, seminar, summit, forum, shortcode, The Events Calendar, The Events Calendar PRO
Donate link: http://m.tri.be/29
Requires at least: 3.9
Tested up to: 4.2.2
Stable tag: 3.9.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Eventbrite Tickets extends The Events Calendar with all the basic Eventbrite controls without ever leaving WordPress.

== Description ==

Looking to track attendees, sell tickets and more? Eventbrite is a free service that provides the full power of a conference ticketing system. This plugin adds the ticket-selling abilities of Eventbrite to your Events Calendar events. You can import Eventbrite events into your calendar, or make events in WordPress and link them to their Eventbrite counterparts. Your users can see tickets right on your event page, and easily buy them through Eventbrite. Don't have an Eventbrite account? No problem, use the following link to set one up: <a href='http://www.eventbrite.com/r/etp'>http://www.eventbrite.com/r/etp</a>.

= The Events Calendar: Eventbrite Tickets =

* Sell tickets from your event's page via Eventbrite
* Create tickets in your WordPress dashboard
* Import Eventbrite events by ID

If you make a new account with Eventbrite, please use our referral code: <a href='http://www.eventbrite.com/r/etp'>http://www.eventbrite.com/r/etp</a>.

For those who want an introduction to how Eventbrite Tickets or the core The Events Calendar works, check out our <a href="http://m.tri.be/39">new user primers.</a>

== Installation ==

= Install =

Just follow these steps:

1. From the dashboard of your site, navigate to Plugins --> Add New.
2. Select the Upload option and hit "Choose File."
3. When the popup appears select the the-events-calendar-eventbrite-tickets.x.x.zip file from your desktop. (The 'x.x' will change depending on the current version number).
4. Follow the on-screen instructions and wait as the upload completes.
5. When it's finished, activate the plugin via the prompt. A message will show confirming activation was successful.
6. For access to new updates, make sure you have added your valid License Key under Events --> Settings --> Licenses.

= Activate =

After downloading and installing the plugin, you'll need to add you Eventbrite App Key + Client Secret to activate all of the great Eventbrite Tickets features. This is how the system knows to link your WordPress site with your Eventbrite account.

1. Install and activate the latest version of The Events Calendar alongside Eventbrite Tickets v 3.9.3 or newer.
2. In another tab, log into Eventbrite.com — if you don’t already have an account, create one.
3. Within Eventbrite.com, navigate to the Account page and find App Management towards the bottom of the lefthand sidebar.
4. Hit the appropriate button to create a new app, and fill in the fields appropriately. Contact Information asks for basic information about who you are and should be quick to complete.
5. Application Details ask you to provide information specific to the app you’re creating. Here’s a quick guide to what goes in each:
** Application URL: The frontend URL for the WordPress site where you’ll be running the plugin.
** OAuth Redirect URI: This field doesn’t have a red asterisk, but it IS required to get Eventbrite Tickets working properly. If you don’t know what it is, don’t worry: just navigate to Events —> Settings —> Eventbrite on the dashboard of your site, and copy the OAuth Redirect URL we’ve provided for you.
** Application Name and Application Description should have a straightforward name and summary that will make this app easy to identify as your list of apps within Eventbrite.com grows over time.
6. Agree to the terms of service, and click the big green “Create Key” button. Let the system work its magic and wait to be redirected back to your App Management page.
7. You’ll see the key you just created, along with a link to view your client secret. First copy the key and plug it into the “Application Key” field under Events —> Settings —> Eventbrite on your WordPress site, then do the same for the “Client Secret.”
8. When all is said and done, hit Save Changes and let the page reload. But you’re not done yet!
9. Once the page refreshes and the App Key and Client Secret are saved in the system, you’ll notice a “Get Authorization” button that wasn’t previously showing.
10. Click it, and wait to be redirected to a page on Eventbrite.com asking you to allow or deny the authorization.
11. Allow it, wait a moment, and you’ll be redirected back to Events —> Settings —> Eventbrite on your WordPress site, with a “Success” message confirming that you’re good to go.

That’s it! If you are unsure about the process of want to see a video documenting it, see our knowledgebase article on the App Key / Client Secret process at http://m.tri.be/vr.

= Requirements =

* WordPress 4.2
* PHP 5.2
* The Events Calendar 3.9.3 or above

== Documentation ==

= Template Tags =

/**
 * @param int post id (optional if used in the loop)
 * @return int the number of tickets for an event
 */
tribe_eb_get_ticket_count( $postId = null )

/**
 * Returns the event id for the post
 *
 * @param int post id (optional if used in the loop)
 * @return int event id, false if no event is associated with post
 */
tribe_eb_get_id( $postId = null)

/**
 * Determine if an event is live
 *
 * @param int post id (optional if used in the loop)
 * @return boolean
 */
tribe_eb_is_live_event( $postId = null)

/**
 * Outputs the Eventbrite post template.  The post in question must be registered with Eventbrite
 * and must have at least one ticket type associated with the event.
 *
 * @param int post id (optional if used in the loop)
 * @uses views/eventbrite-post-template.php for the HTML display
 * @return void
 */
tribe_eb_event( $postId = null )

/**
 * Returns the Eventbrite attendee data for display
 *
 */
tribe_eb_event_list_attendees($eb_event_id, $ebuser_name, $eb_user_password)

== Screenshots ==

1. Admin interface for adding your first ticket to an Eventbrite event
2. Advanced Eventbrite admin options after saving as draft
3. Eventbrite's ticket widget on frontend

== Frequently Asked Questions ==

= Where do I go to file a bug or ask a question? =

Please visit the forum for questions or comments: http://m.tri.be/3a

== Contributors ==

The plugin is produced by <a href="http://m.tri.be/3b">Modern Tribe Inc</a>.

= Current Contributors =

* <a href="http://profiles.wordpress.org/users/jazbek">Jessica Yazbek</a>
* <a href="http://profiles.wordpress.org/users/barryhughes">Barry Hughes</a>
* <a href="http://profiles.wordpress.org/users/roblagatta">Rob La Gatta</a>
* <a href="http://profiles.wordpress.org/users/neillmcshea">Neill McShea</a>
* <a href="http://profiles.wordpress.org/users/brook-tribe”>Brook Harding</a>
* <a href="http://profiles.wordpress.org/users/ckpicker”>Casey Picker</a>
* <a href="http://profiles.wordpress.org/users/mdbitz”>Matthew Denton</a>
* <a href="http://profiles.wordpress.org/users/leahkoerper">Leah Koerper</a>
* <a href="http://profiles.wordpress.org/users/borkweb">Matthew Batchelder</a>
* <a href="http://profiles.wordpress.org/users/zbtirrell">Zach Tirrell</a>
* <a href="http://profiles.wordpress.org/users/bordoni">Gustavo Bordoni</a>
* <a href="http://profiles.wordpress.org/users/joshlimecuda">Josh Mallard</a>
* <a href="http://profiles.wordpress.org/momnt">George Gecewicz</a>
* <a href="http://profiles.wordpress.org/users/geoffgraham">Geoff Graham</a>
* <a href="http://profiles.wordpress.org/users/peterchester">Peter Chester</a>
* <a href="http://profiles.wordpress.org/users/reid.peifer">Reid Peifer</a>
* <a href="http://profiles.wordpress.org/users/shane.pearlman">Shane Pearlman</a>

= Past Contributors =

* <a href="http://profiles.wordpress.org/users/jonahcoyote">Jonah West</a>
* <a href="http://profiles.wordpress.org/users/caseypatrickdriscoll">Casey Driscoll</a>
* <a href="http://profiles.wordpress.org/users/paulhughes01">Paul Hughes</a>
* <a href="http://profiles.wordpress.org/users/codearachnid">Timothy Wood</a>
* <a href="http://profiles.wordpress.org/users/jkudish">Joachim Kudish</a>
* <a href="http://profiles.wordpress.org/users/jgadbois">John Gadbois</a>
* <a href="http://profiles.wordpress.org/users/kellykathryn">Kelly Groves</a>
* Justin Endler

= Translators =

* Brazilian Portuguese by Dionizio Bonfim Bach
* German from Jan
* French from Alaric Breithof
* Romanian from Cosmin Vaman
* Spanish from Lorenzo Sastre Muntaner
* Swedish from Andreas Bodin

* Previous translators include Pierre Trochet, Hector Gil Rizo, Patricio Campos, Mihai Burcea, Jurgen Michiels, Petri Kajander and Marco Infussi

== Add-Ons ==

But wait: there's more! We've got a whole stable of plugins available to help you kick ass at what you do. Check out a full list of the products below, and over at the <a href="http://m.tri.be/3c">Modern Tribe website.</a>

Our Free Plugins:

* <a href="http://wordpress.org/extend/plugins/advanced-post-manager/" target="_blank">Advanced Post Manager</a>
* <a href="http://wordpress.org/plugins/blog-copier/" target="_blank">Blog Copier</a>
* <a href="http://wordpress.org/plugins/image-rotation-repair/" target="_blank">Image Rotation Widget</a>
* <a href="http://wordpress.org/plugins/widget-builder/" target="_blank">Widget Builder</a>

Our Premium Plugins:

* <a href="http://m.tri.be/2c" target="_blank">The Events Calendar PRO</a>
* <a href="http://m.tri.be/2e" target="_blank">The Events Calendar: Eventbrite Tickets</a>
* <a href="http://m.tri.be/2g" target="_blank">The Events Calendar: Community Events</a>
* <a href="http://m.tri.be/2h" target="_blank">The Events Calendar: Facebook Events</a>
* <a href="http://m.tri.be/2i" target="_blank">The Events Calendar: WooCommerce Tickets</a>
* <a href="http://m.tri.be/ci" target="_blank">The Events Calendar: EDD Tickets Tickets</a>
* <a href="http://m.tri.be/cu" target="_blank">The Events Calendar: WPEC Tickets</a>
* <a href="http://m.tri.be/dp" target="_blank">The Events Calendar: Shopp Tickets</a>
* <a href="http://m.tri.be/fa" target="_blank">The Events Calendar: Filter Bar</a>

== Upgrade Notice ==

3.9.3 is a major update to Eventbrite Tickets: in this version we’ve moved to the latest Eventbrite API, which results in some noticeable changes for end users. Most notably, all site admins - both new and existing users - will need to create an App Key / Client Secret within Eventbrite before they can begin using version 3.9.5. For an overview of the Application creation process, see our tutorial at http://m.tri.be/vr.

== Changelog ==

= 3.9.5 =

* Improved the visibility for the Authorization URL information
* Fixed problems for imported events where the ticket Form would not display (hat tips to p88dadmin and Neil for reporting this in our forums!)
* Fixed instances where multiple prices were incorrectly displayed in the cost field
* Implemented better error handling of some exceptions

= 3.9.4 =

* Updated some of the Eventbrite API-related code from the previous release to ensure compatibility with older versions of PHP
* Implemented some other minor bug fixes

= 3.9.3 =

* Overhauled the plugin codebase so it now uses Eventbrite API v3.0

= 3.9.2 =
* Hardened URL output to protect against XSS attacks.

= 3.9.1 =

* Fixed a series of bugs within various plugin template tags causing fatal errors (our thanks to crhallen in the forums for highlighting these issues!)
* Fixed a bug where the ticket prices displayed from Eventbrite could be incorrect (big thanks to saibotny on the forums for the report!)
* Moved storage of cached ticket pricing to transients

= 3.9 =

* Fixed a bug where the price of donation based tickets would show up blank (thanks to stevenmillstein for the original report!)
* Fixed a bug that loaded the Eventbrite ticket form over HTTP within the context of an HTTPS request (thanks to kjoboyle on the forums for the first report!)

= 3.8.1 =

* Fixed a bug where the time and timezone of events hosted by Eventbrite could be inadvertently changed (our thanks to Frederick W Chapman for highlighting this)

= 3.8 =

* Fixed some PHP strict standards notices
* Added support for UTC offset-based timezones

= 3.7 =

* Fixed some translation strings textdomains for correct translations
* Fixed a bug where the same venue could be re-imported repeatedly, resulting in unnecessary duplicates
* Improved handling of events imported from a different timezone when using a city-based WordPress timezone (thanks to Cloud Genius for highlighting this!)

= 3.6 =

* Removed the Google Checkout payment option when publishing events to Eventbrite
* Incorporated updated French translation files, courtesy of Alaric Breithof

= 3.5 =

* Added a feature where the tribe_get_cost() function in events templates will now display ticket cost from Eventbrite (thanks to randalldon at the forums for reporting this!)
* Added a filter 'tribe_events_eb_request' that can be used to filter any request params before they are sent to Eventbrite (thanks to Cloud Genius on the forums for the report!)
* Added Eventbrite event privacy status to the event editor , along with a link to change the Event privacy on Eventbrite
* Updated the API so that Events will now only be created at Eventbrite if the event is published in TEC
* Improved error messaging when errors occur
* Incorporated updated Romanian translation files, courtesy of Cosmin Vaman
* Incorporated updated Spanish translation files, courtesy of Lorenzo Sastre Muntaner

= 3.4 =

* Donation-based tickets will now show the word "Donation" under the cost column on the event edit screen
* Improved general compliance with PHP strict standards
* Incorporated updated French translation files, courtesy of Pierre Trochet

= 3.3 =

* Featured images on events posts will now be included in the Custom Header field under the "Design" tab on Eventbrite
* The first image in the Custom Header field under the "Design" tab on Eventbrite will now be set as the featured image on events that are imported from Eventbrite

= 3.2.1 =

* Fixed issue with Eventbrite tickets not showing up on single event pages
* Fixed incorrect available ticket count in the admin editor

= 3.2 =

* Switched to JSON format for Eventbrite API requests to ensure formatting passes through properly
* Ensured line breaks are preserved when sending events to Eventbrite (thanks to user timelesstime for reporting this on the forums!)
* Ensured apostrophes in organizer names do not break Eventbrite API requests (thanks to rocketpop for the original report!)
* Added a notice for when an event update or creation is rejected by Eventbrite for no specified reason (thanks to Jared for bringing this to our attention!)
* Ensured our Eventbrite requests won't break when invalid HTML is passed through the editor
* Fixed cost field sometimes being marked as required for free events
* Eventbrite Tickets will now allow events to be saved to Eventbrite without a venue!


= 3.1 =

* Updated Eventbrite API class to use newly required Olson format for the timezone
* Improved some error messages
* Updated translations: Brazilian Portuguese (new), Romanian (new)
* Various minor bug and security fixes

= 3.0.1 =

* Performance improvements to the plugin update engine

= 3.0 =

Updated version number to 3.0.x for plugin version consistency

= 1.0.7 =

* Fix plugin update system on multisite installations

= 1.0.6 =

*Small features, UX and Content tweaks:*

* Total plugin audit/code review for bugs & incomplete functionality.
* Code modifications to ensure compatibility with The Events Calendar/Events Calendar PRO 3.0.
* Due to a change in the Eventbrite API, Eventbrite events can no longer be deleted on the WordPress side; you can now cancel them on WordPress and must go to Eventbrite.com to truly delete.
* Better handling of cross-time zone imports.
* Ticket sale date range is now limited to times before the event takes place.
* Clarified a couple error/warning messages.
* Incorporated new French translation files, courtesy of Frederic-Xavier DuBois.
* Incorporated new Polish translation files, courtesy of Marek Kosina.
* Incorporated new Swedish translation files, courtesy of Andreas Bodin.

*Bug fixes:*

* Plugin now activates when installed on a site running PHP 5.4 or newer.
* Addressed unstylized text indicating events are in draft format, which impacted certain users.
* PayPal email field now triggers as expected.
* Imported events no longer hide tickets on the WP frontend for certain users.
* Tickets no longer appear listed under Related Events when running on the Events 3.0 codebase.
* Offline payment methods no longer show until an online payment method is selected.
* Removed a dead link from eventbrite-events-table.php.
* Addressed an issue where the admin CSS file wasn't loading properly on certain installations.

= 1.0.5 =

*Small features, UX and Content tweaks:*

(none in this release)

*Bug fixes:*

* Various minor bug fixes.

= 1.0.4 =

*Small features, UX and Content tweaks:*

(none in this release)

*Bug fixes:*

* Fixed an ambiguous error message that appeared when the site failed to connect with Eventbrite.

= 1.0.3 =

*Small features, UX and Content Tweaks:*

* Ticket box is now automatically displayed on all Eventbrite events (it previously defaulted to hidden).
* Added new notification that appears upon initial activation, directing users to the new user primer.
* Ticket-specific field for Eventbrite Tickets is no longer mandatory.
* Incorporated new Dutch language files, courtesy of Jurgen Michiels.
* Incorporated new Finnish language files, courtesy of Petri Kajander.
* Incorporated new Italian language files, courtesy of Marco Infussi.

*Bug Fixes:*

* Plugin now works with PHP 5.4 and above.
* Dual cost fields (one for Events, the other for Eventbrite) no longer conflict when both are being used on the same event.
* Fixed a bug where, for some users, editing an existing event yielded a slew of Eventbrite-generated notices.

= 1.0.2 =

*Bug Fixes:*

* Removed unclear/confusing message warning message regarding the need for plugin consistency and added clearer warnings with appropriate links when plugins or add-ons are out date.

= 1.0.1 =

*Small features, UX and Content Tweaks:*

* Incorporated new Spanish translation files, courtesy of Hector at Signo Creativo.
* Added new "Events" admin bar menu with Eventbrite-specific options.

*Bug Fixes:*

* Removed "No Venues/Organizers Found For This User" error when not trying to send a venue/organizer to Eventbrite.
* Added warning message when attempting to begin ticket sales for an Eventbrite event anytime in the past.
* Added proper error messaging when attempting to send country- or state-less events to Eventbrite.

= 1.0 =

Initial release
