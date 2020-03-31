# Happy Converter

> **⚠ This plugin is an early prototype**, it is not fully featured and should be used with care.

![Demonstration of migrating events from Sugar Calendar](https://raw.githubusercontent.com/mt-support/happy-converter/master/docs/media/basic-workflow.gif)

This plugin offers a means of migrating events from a different calendar plugin across to The Events Calendar.

### Disclaimer

Make sure to back up your site before doing any migration in case you need to rollback to the previous state, as the plugin does not have a mechanism to rollback or to remove changes, this requires a manual process, doing a removal of events manually after the migration is complete in case something needs to be changed.

### Requirements

- PHP 7.2

### Steps

* Install The Events Calendar in the same site where you originally used Sugar Calendar (Lite).
* Deactivate that unused plugin if you like.
* Navigate to the **Tools ‣ TEC Happy Converter** admin screen.

### Converters

The Plugin supports the conversion from the following Plugins into TEC events.

- [Sugar Calendar Event](https://wordpress.org/plugins/sugar-calendar-lite/)
- [All-in-One Event Calendar](https://wordpress.org/plugins/all-in-one-event-calendar/)

The plugin detects if any of the converts above are active and if there are any events that hasn't been migrated, if that's the case the plugin enable a button to migrate the remaining events into [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/).

### Recurrence

Some Calendars has support for recurrence events, in those cases if [Events PRO] is not enabled single instances per recurrence is created, example if you have 1 event happening every day during 10 events, 10 events will be created but as separate individual events. If [Events PRO] is active 10 events are created but all of them are related with PRO recurrence rules.

[Events PRO]: https://theeventscalendar.com/product/wordpress-events-calendar-pro/
