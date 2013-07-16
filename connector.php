<?php
/*
Plugin Name: Ezdeebee WordPress Connector
Plugin URI: http://ezdeebee.com/wordpress
Description: Ezdeebee WordPress Connector Plugin
Version: 1.0
Author: Ezdeebee
Author URI: http://ezdeebee.com
License: GPL2


Copyright 2013  Ezdeebee  (email: support@ezdeebee.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WP_DEBUG', true);

$referer = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
$referer .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$cachebuster = rand(0, 10000);

$sitetoken = 'yzg0TOBW';  // user token, retrieved from DB

if ($_GET['ezdb_initconnector']) {
	$url = "http://wklocal.netmusician.org/webkit1_0/dbmanager/" . $_GET['ezdeebee_cid'] . "/json";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'sitetoken=' . $sitetoken . '&cid=' . $_GET['ezdeebee_cid']);
	$result = curl_exec($ch);
	curl_close($ch);
	print $result;
	
	if ($_GET['local_cache']) {
		// cURL SQL regen commands
			
	}
	exit;
}

$yuisrc = ($_SERVER['HTTPS']) ? 'http://ezdeebee.com/app/nm_webkit/libs/yui3.5.1/build/yui/yui-min.js' : 'http://yui.yahooapis.com/3.5.1/build/yui/yui-min.js';

wp_enqueue_script('YUI3.5.1', $yuisrc);

//$jsconfigsrc = "https://dev.ezdeebee.com/app/dbmanager/" . $siteID . "/seedfile";
$jsconfigsrc = "http://wklocal.netmusician.org/webkit1_0/dbmanager/" . $sitetoken . "/seedfile/" . $cachebuster;
wp_enqueue_script('ezdeebee_seedfile', $jsconfigsrc);
?>