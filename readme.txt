=== The Events Calendar Extension: Advanced iCal Export ===
Contributors: ModernTribe
Donate link: http://m.tri.be/29
Tags: events, calendar, ical, export
Requires at least: 4.5
Tested up to: 4.9.6
Requires PHP: 5.6
Stable tag: 1.1.0
License: GPL version 3 or any later version
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The extension gives you advanced export possibilities through the iCal feed.

== Installation ==

Install and activate like any other plugin!

* You can upload the plugin zip file via the *Plugins ‣ Add New* screen
* You can unzip the plugin and then upload to your plugin directory (typically _wp-content/plugins)_ via FTP
* Once it has been installed or uploaded, simply visit the main plugin list and activate it

== Frequently Asked Questions ==

== How do I use this? ==

These are the different URL parameters you can use.

* Simple: exports events of the current year, e.g. Jan 1st to Dec 31 of current year:       
http://example.com/events/?ical=1&tribe_display=custom

* If only start date is defined, then events until the end of the current year will be exported, e.g. from Feb 2, 2017 until end of current year:  
http://example.com/events/?ical=1&tribe_display=custom&start_date=2017-02-10

* If only end date is defined, then events starting from Jan 1 of current year will be exported, e.g. from Jan 1 of current year until July 31, 2020:  
http://example.com/events/?ical=1&tribe_display=custom&end_date=2020-07-31

* If both start and end date are defined, e.g. from July 1, 2017 until June 30, 2018:  
http://example.com/events/?ical=1&tribe_display=custom&start_date=2017-07-01&end_date=2018-06-30

* If only start year is defined, then events from January 1 of that year will be exported, e.g. from Jan 1, current year until Dec 31, 2020:  
http://example.com/events/?ical=1&tribe_display=custom&start_date=2019

* If only end year is defined, then events until December 31 of that year will be exported, e.g. from Jan 1, current year until Dec 31, 2020:  
http://example.com/events/?ical=1&tribe_display=custom&end_date=2020

* If both start and end year are defined, then events from Jan 1 of the start year until Dec 31 of the end year will be exported, e.g. from Jan 1, 2018 until Dec 31, 2020:  
http://example.com/events/?ical=1&tribe_display=custom&start_date=2018&end_date=2020

= Where can I find more extensions? =

Please visit our [extension library](https://theeventscalendar.com/extensions/) to learn about our complete range of extensions for The Events Calendar and its associated plugins.

= What if I experience problems? =

We're always interested in your feedback and our [premium forums](https://theeventscalendar.com/support-forums/) are the best place to flag any issues. Do note, however, that the degree of support we provide for extensions like this one tends to be very limited.

== Changelog ==

= [1.1.0] 2019-09-20 =
* Fix - Properly sanitize incoming values
* Fix - Fix the extension to work with TEC 4.9.8

= [1.0.0] 2018-07-09 =

* Initial release
