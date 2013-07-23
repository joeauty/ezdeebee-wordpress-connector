Ezdeebee Connector WordPress Plugin
===================================

Using the Ezdeebee Connector WordPress plugin you can display the data in your Ezdeebee data collections, allow form submissions from your WordPress site to populate an Ezdeebee data collection, or else sync with an Ezdeebee data collection so that you have a local copy.

The instructions here assume that you have an Ezdeebee account, and have a WordPress site where you can install this plugin. If you don't have an Ezdeebee account you can request an invitation to Ezdeebee beta [here](http://ezdeebee.com/contact). You can learn more about Ezdeebee at [our website](http://ezdeebee.com).

Configuring Connector Access
----------------------------

This plugin is installed just like any other WordPress plugin, but before doing so, make sure you have an Ezdeebee account, and within the "Settings" section of your account note the site ID that has been assigned to you - you'll need this for later. You will also need to provide the domain name(s) for the website you wish to provide access to.

Select the data collection you want to integrate with your website, click on the "Data Collection Operations/Settings" tab, followed by the "Connector Settings" button. From here you can establish the settings for the sortable tables and forms you would like to integrate with your site. You will need to enable access to this data collection here, as well as configure your tables/forms as desired.

Installing the Plugin
---------------------

With your Ezdeebee account setup, simply install and enable this plugin like you would any other through the WordPress web interface, or else by adding this plugin to your WordPress folder's *wp-content/plugins* directory. Once installed the plugin can be enabled/disabled via WordPress' "Plugins" section.

Configuring the WordPress Plugin
--------------------------------

Once the plugin has been activated you'll see a new item in your WordPress Settings menu called "Ezdeebee Settings". On this page you'll need to provide your Ezdebee Site ID (see "Configuring Connector Access", above). If you wish to cache a local copy of your Ezdeebee data check the "Cache to Local Database" checkbox.

About the Local Cache
---------------------

With this option enabled you will find tables in your WordPress database with the "ezdb_" prefix. Using the standard WordPress database functions you can query these tables as needed. It is important to note that if you decide to make changes to these tables your changes will **not** be synced back to Ezdeebee. This sync is one-way, which is why it is better to think of it as a cache rather than a replica of some sort. The cache will be automatically regenerated whenever a change to your Ezdeebee data collection has been detected.