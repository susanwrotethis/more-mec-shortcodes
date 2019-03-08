# More MEC Shortcodes

This plugin was written for a small business offering series of classes for children, with each series normally running six or eight weeks. While its limited scope may not make it useful to other users of Modern Events Calendar, it provides a template that can be adapted for other custom uses.

## Description

Extends Modern Events Calendar Lite by adding shortcodes to display an events list in a non-caledar format. The shortcode is for the specific case of classes or activities posted as recurring events, one or more days a week over a series of several weeks.

**Features**

* Works on multisite as well as single installations
* Clean installation: no options added to the database or settings page to manage
* Contains language support

## How It Works

This plugin gets a list of all the event categories. For each category that has events, it displays the category name; the category description if one is available; and each event.

The following fields are shown for events:

* Event title
* Day(s) of the week the event takes place
* Event start and end times
* Event location
* Location address
* Event cost
* Number of weeks (calculated by the number of recurrences)
* Start date of series
* End date of series
* Event link, displayed as a signup link

The categories are listed in ascending order by category slug. The events are listed by menu order. Events with the same menu order are sorted by the date they were added. Both are in ascending order.

## Limitations

The shortcode will display all non-private published events assigned a category, even if the event has expired. To remove an event, delete it, make it private or set its status to draft or preview.

A number of fields are not used in this format, including the event content, the excerpt, tags, event label, event color, featured image, organizers and the more info link.

Single events, all-day events, the hiding of times or locations and hourly schedule information are ignored. The plugin assumes that Repeat is set to Certain Weekdays and the After field is used to set when the event stops repeating.

Categories cannot be excluded.

While missing values for the fields that are used should not break the site, the results may not be pretty.

There is no pagination. The shortcode is intended for short lists.

## Installation

Download the current release of this plugin as a zip file. Make sure the file is named more-mec-shortcodes.zip.

* In the WordPress admin, go to Plugins > Add New. On multisite, this is under the network admin.
* Click the Upload Plugin button at the top of the page and browse for the plugin's zip file.
* Upload the zip file.
* Once the plugin is installed, activate it. On multisite, this can be network activated or activated on individual sites.

## Setup

This plugin has no settings. It adds WordPress core's Menu Order setting to events to provide finer-grained control over the order of listings as they appear in the menu.

If you have set up Modern Events Calendar Lite and added some recurring events, all you need to do is add one of the shortcodes to a page.

For events as paragraphs with event titles in H3 tags:

[custom-event-list]

For events as unordered lists with event title in the list item:

[custom-event-ul]

## Filter Hooks

This plugin has two filter hooks, **swt_mec_default_style** and **swt_mec_list_style**.

Both filters allow for complete filtering of an events entry after it has been formatted. The formatted string for the entry and the array of data used to build it are passed as parameters.

## Frequently Asked Questions

**Why is this plugin useful?**

There are several good events calendar plugins available, but they focus on presenting events by date. But recurring events sometimes require a list of onging events or activites to be listed.

The plugins that are configurable are more complicated than is needed for a small website. This plugin provides some of that functionality.

**Why Modern Events Calendar?**

Some of the events plugins provide recurring events only with the premium version. Others have events and recurring events set up separately. Modern Events Calendar Lite offers the recurring events with a single events interface and the right combination of taxonomies to be useful.

**Will it work with other calendar plugins?**

No, because the custom post type and taxonomy names are unique to Modern Events Calendar. A developer interested in adapting the concept of this shortcode could probably do so for other events plugins as long as they have comparable fields.

**What happens if I deactivate or uninstall the calendar plugin?**

If there are still published events in the website's database, this plugin will continue to display them. If there are no events found, this plugin won't do anything.

**Will the menu order field affect the calendar's behavior elsewhere?**

The field's addition should have no effect on Modern Events Calendar.

**What if I also have single events to display?**

Check the Event Repeating checkbox, select Certain Weekays and check the day or days. For end repeat, choose After and set 1 as the value.

**Does this plugin have a Gutenberg block?**

Not at this time.

**Is this plugin translation ready?**

Yes.

**Does it work on multisite?**

Yes.

**Is there anything else I should know?**

This plugin was created to handle a very specific use case: there is a small number of events in the system (less than 50), they are all recurring events, and each is assigned a category and location.

Because this plugin assembles and formats many different pieces of information, it is not ideal for large calendars.

## About Modern Events Calendar

See the Modern Events Calendar Lite plugin in [the WordPress plugins repository](https://wordpress.org/plugins/modern-events-calendar-lite/) to learn more.

## Changelog

### 1.0

* New release

### 1.1

* Fix Sunday events not displaying day of week. Compensates for MEC setting the value for Sunday as 7 rather than 9.

### 1.2

* Add a second shortcode to display events in unordered lists.
* Refactor code to support the development of additional shortcodes for different formats.
* Added filter hooks to access the events for additional formatting.
* Update the translation template.