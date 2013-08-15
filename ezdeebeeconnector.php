<?php
/*
Plugin Name: Ezdeebee WordPress Connector
Plugin URI: http://ezdeebee.com/wordpress
Description: Ezdeebee WordPress Connector Plugin
Version: 1.1.1
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

//define('WP_DEBUG', true);

$referer = ($_SERVER['HTTPS']) ? 'https://' : 'http://';
$referer .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

$ezdbdomain = "https://ezdeebee.com/app";
$cachebuster = rand(0, 10000);

$ezdeebee_options = get_option('ezdeebee_options');

if ($_GET['ezdb_initconnector']) {
	$url = $ezdbdomain . "/dbmanager/" . $_GET['ezdeebee_cid'] . "/json";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'sitetoken=' . $ezdeebee_options['ezdeebee_site_id'] . '&cid=' . $_GET['ezdeebee_cid'] . '&domain=' . $_SERVER['SERVER_NAME'] . '&HTTPS=' . $_GET['HTTPS']);
	$result = curl_exec($ch);
	curl_close($ch);
	print $result;
	
	if ($ezdeebee_options['ezdeebee_localcache']) {
		// cURL SQL regen commands
		
		$resultsjson = json_decode($result);
		$tablename = $resultsjson->tablename;
		$regdbID = $resultsjson->regdbID;
		
		// create modifications table, if necessary
		$sql = "CREATE TABLE `ezdb__modifications` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `registereddb` int(11) DEFAULT NULL,
		  `connector_id` int(11) DEFAULT NULL,
		  `tablename` varchar(255) NOT NULL,
		  `lastmodified` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		// get remote last modified date
		$url = $ezdbdomain . '/dbmanager/' . $ezdeebee_options['ezdeebee_site_id'] . '/lastmodified';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'sitetoken=' . $ezdeebee_options['ezdeebee_site_id'] . '&regdbID=' . $regdbID . '&domain=' . $_SERVER['SERVER_NAME'] . '&HTTPS=' . $_GET['HTTPS']);
		$remotelastmodified = curl_exec($ch);
		curl_close($ch);
		
		// get local last modified date
		$query = $wpdb->get_results('SELECT lastmodified FROM ezdb__modifications WHERE registereddb = "' . $regdbID . '"');
		$locallastmodified = strtotime($query[0]->lastmodified);
		$action = ($wpdb->num_rows) ? 'update' : 'insert';

		if (!$wpdb->num_rows || $locallastmodified < $remotelastmodified || !$query[0]->tablename || !$query[0]->connector_id) {
			// do sync
			$url = $ezdbdomain . '/dbmanager/' . $ezdeebee_options['ezdeebee_site_id'] . '/dump';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'sitetoken=' . $ezdeebee_options['ezdeebee_site_id'] . '&regdbID=' . $regdbID . '&domain=' . $_SERVER['SERVER_NAME'] . '&HTTPS=' . $_GET['HTTPS']);
			$sql = curl_exec($ch);
			curl_close($ch);
			
			$sql = preg_replace('/DROP TABLE IF EXISTS (.+)?/','DROP TABLE IF EXISTS ezdb_$1', $sql);
			$sql = preg_replace('/CREATE TABLE `(.+)?`/','CREATE TABLE `ezdb_$1`', $sql);
			// add ezdb prefix to insert table commands
			$sql = preg_replace('/INSERT INTO (.+)?\s\(/','INSERT INTO ezdb_$1 (', $sql);
			
			$createtablesql = preg_replace('/(.+)?INSERT INTO.+/m', '$1', $sql);
			$inserttablesql = explode("\n", str_replace($createtablesql, '', $sql));
			
			dbDelta($createtablesql);
			dbDelta($inserttablesql);
			
			// update local last modified
			if ($action == "insert") {
				// insert new modification date
				$wpdb->insert( 'ezdb__modifications', array('lastmodified' => date('Y-m-d H:i:s', $remotelastmodified), 'connector_id' => $_GET['ezdeebee_cid'], 'tablename' => $tablename, 'registereddb' => $regdbID));
			}
			else if ($action == "update") {
				// update modification date
				$wpdb->update( 'ezdb__modifications', array('lastmodified' => date('Y-m-d H:i:s', $remotelastmodified), 'connector_id' => $_GET['ezdeebee_cid'], 'tablename' => $tablename), array('registereddb' => $regdbID));	
			}
		}
	}
	exit;
}

$yuisrc = plugins_url('ezdeebee_wpconnector') . '/yui/yui/yui-min.js';

wp_enqueue_script('YUI3.11.0', $yuisrc);

$jsconfigsrc = $ezdbdomain . "/dbmanager/" . $ezdeebee_options['ezdeebee_site_id'] . "/seedfile/" . $cachebuster;
wp_enqueue_script('ezdeebee_seedfile', $jsconfigsrc);

/* Admin panel stuff */

function initEzdeebee() {
	$hook_suffix = add_options_page( 'Ezdeebee Settings', 'Ezdeebee Settings', 'manage_options', 'ezdeebee', 'ezdeebee');
	$options = get_option('ezdeebee_options');
	
	if ($options['ezdeebee_site_id'] && !$_POST['ezdeebee_options']) {
		validateSiteID($options);
	}
	add_action( 'admin_notices', 'ezdeebeeFeedback' );
	add_action( 'load-' . $hook_suffix , 'removeConfigNotice' );
	add_action( 'admin_init', 'initEzdeebeeSettings' );
}

function initEzdeebeeSettings() {
	register_setting('ezdeebee_options', 'ezdeebee_options', 'validateSiteID'); 
	add_settings_section('ezdeebee_main', '', 'ezdeebeeSectionText', 'ezdeebee'); 
	add_settings_field('ezdeebee_site_id', 'Ezdeebee Site ID', 'renderSiteID', 'ezdeebee', 'ezdeebee_main');
	add_settings_field('ezdeebee_localcache', 'Cache to Local Database<br /><small>(for advanced usage and search engine optimization)</small>', 'renderLocalCache', 'ezdeebee', 'ezdeebee_main');
}


function ezdeebeeSectionText() {

}

function validateSiteID($input) {
	global $ezdbdomain, $cachebuster;
	$checkurl = $ezdbdomain . "/dbmanager/" . $input['ezdeebee_site_id'] . "/connectorconfigs/" . $cachebuster;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_URL, $checkurl);
	curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$result = curl_exec($ch);
	curl_close($ch);
	if (!$result || !$input['ezdeebee_site_id']) {
	    $type = 'error';
	    $message = __( 'ERROR: invalid Ezdeebee Site ID, or else there are no enabled Ezdeebee connectors. Your Ezdeebee Site ID can be found in the "Settings" section of <a href="https://ezdeebee.com/app">your Ezdeebee Beta account</a>, and connectors can be created/enabled within the "Connector Settings" Data Collection Operation/Settings page of your Ezdeebee Beta account', 'Ezdeebee Site ID' );
		
		add_settings_error(
			'ezdeebee_error',
			esc_attr( 'settings_updated' ),
			$message,
			$type
		);	
	}
	return $input;
}

function ezdeebeeFeedback() {
	$options = get_option('ezdeebee_options');
	if (!$options['ezdeebee_site_id']) {
		echo "<div id='notice' class='updated fade'><p>In order to use Ezdeebee on your site you must provide your Ezdeebee Site ID which can be accessed within the \"Settings\" section of <a href=\"https://ezdeebee.com/app\">your Ezdeebee Beta account</a>. If you do not have an account, please <a href=\"http://ezdeebee.com/contactus\">contact us</a> to request your (free) invitation to Ezdeebee beta!</p></div>\n";	
	}
}

function removeConfigNotice() {
	remove_action( 'admin_notices', 'ezdeebeeFeedback' );	
}

function ezdeebee() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Ezdeebee Settings</h2>
		<form method="post" action="options.php"> 
			<?php
				settings_fields( 'ezdeebee_options' );
				do_settings_sections('ezdeebee');
				submit_button();
			?>
		</form>
	</div>
	<?php
}

function renderSiteID() {
	$options = get_option('ezdeebee_options');
	echo "<input id='ezdeebee_site_id' name='ezdeebee_options[ezdeebee_site_id]' size='40' type='text' value='{$options['ezdeebee_site_id']}' />";
}

function renderLocalCache() {
	$options = get_option('ezdeebee_options');
	echo "<input id='ezdeebee_localcache' name='ezdeebee_options[ezdeebee_localcache]' type='checkbox' value='1'";
	if ($options['ezdeebee_localcache']) {
		echo " checked='checked'";
	}
	echo " />";
}

function cacheTable($table) {
	global $wpdb;
	$query = $wpdb->get_results('SELECT tablename FROM ezdb__modifications WHERE connector_id = "' . $table . '"');
	$tablename = $query[0]->tablename;

	$query = $wpdb->get_results('SELECT * FROM ' . $tablename);
	if ($wpdb->num_rows) {
		return $query;
	}
	return false;
}

function ezdeebeeShortcode( $atts ) {
	global $ezdeebee_options;
	extract( shortcode_atts( array(
		'table' => '',
		'form' => '',
	), $atts ) );
	
	if ($table) {
		$cachetable = cacheTable($table);
		
		$html = '<div class="ezdbtemplate" id="ezdbtable_' . $table . '"></div>';
		
		if ($ezdeebee_options['ezdeebee_localcache'] && $cachetable) {
			$fields = get_object_vars($cachetable[0]);
			
			$html .= '<div class="cachetable ezdb_displaynone">';
			$html .= '<table>';
			foreach ($cachetable as $thisrow) {
				$html .= '<tr>';
				foreach ($fields as $thisfield=>$fieldval) {
					switch ($thisfield) {
						case 'listorder':
						case 'updated_at':
						case 'created_at':
						break;
					
						default:
						$html .= '<td>' . htmlspecialchars_decode($thisrow->{$thisfield}, ENT_QUOTES) . '</td>';
						break;
					}
				}
				$html .= '</tr>';
			}
			$html .= '</table></div>';	
		}
	}
	else if ($form) {
		$html = '<div class="ezdbtemplate" id="ezdbform_' . $form . '"></div>';
	}
	
	return $html;
}

add_action( 'admin_menu', 'initEzdeebee' );
add_shortcode( 'ezdb', 'ezdeebeeShortcode' );
?>