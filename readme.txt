=== Ezdeebee Connector WordPress Plugin ===
Contributors: besson3c
Donate link: http://ezdeebee.com
Tags: database, database admin, custom, form, table, Ezdeebee
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ezdeebee is a cloud service for managing database-driven data, such as content you wish to display within your WordPress website or web applications.

== Description ==

Ezdeebee is the perfect tool for managing data, such as content you may wish to display on your website, or, if you are developer, data that you use in your applications. Ezdeedee is a cloud service, meaning you can access Ezdeebee from anywhere and from any device. With Ezdeebee you can easily collaborate with others without needing to install any special software.

Using this Ezdeebee Connector WordPress plugin you can display the data in your Ezdeebee data collections, allow form submissions from your WordPress site to populate an Ezdeebee data collection, or else sync with an Ezdeebee data collection so that you have a local copy you can query as needed.

The instructions here assume that you have an Ezdeebee account, and have a WordPress site where you can install this plugin. If you don't have an Ezdeebee account you can request an invitation to Ezdeebee beta [here](http://ezdeebee.com/contact). You can learn more about Ezdeebee at [our website](http://ezdeebee.com).

== Installation ==

1. This plugin is installed just like any other WordPress plugin, but before doing so, make sure you have an Ezdeebee account, and within the "Settings" section of your account note the site ID that has been assigned to you - you'll need this for later. You will also need to provide the domain name(s) for the website you wish to provide access to.
1. Select the data collection you want to integrate with your website, click on the "Data Collection Operations/Settings" tab, followed by the "Connector Settings" button. From here you can establish the settings for the sortable tables and forms you would like to integrate with your site. You will need to enable access to this data collection here, as well as configure your tables/forms as desired.
1. Upload `ezdeebee-wp-connector` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Once the plugin has been activated you'll see a new item in your WordPress Settings menu called "Ezdeebee Settings". On this page you'll need to provide your Ezdebee Site ID (see "Configuring Connector Access", above, for obtaining this item). If you wish to cache a local copy of your Ezdeebee data check the "Cache to Local Database" checkbox.

== Frequently asked questions ==

See [Ezdeebee support](http://ezdeebee.com/support)

== Screenshots ==

1. Sample data collection for a media library consisting of Blu-ray/DVD video and CD/MP3/vinyl audio. Includes fields for uploading cover artwork, and providing a whole host of additional information including genre and parental ratings. Data collections make use of image uploads, dropdown field types, as well as textareas with editors
1. Sample custom table view of data from media library data collection, displayed on a WordPress site using this plugin

== Changelog ==

= 1.1.1 =

* fix local cache creation bug

= 1.1.0 =

* when "Cache to Local Database" is checked output a table on initial page load hidden via CSS display:none for improved SEO
* upgrade to YUI 3.11.0

= 1.0.1 =

* use local Yahoo User Interface (YUI) library

= 1.0.0 =

* initial Ezdeebee WordPress connector release for Ezdeebee beta

== Upgrade notice ==

= 1.0.1 -> 1.1.0 =

* for the local database feature some new fields have been added to the ezdb__modifications table which should be automatically added to this table, and the cache automatically regenerated
