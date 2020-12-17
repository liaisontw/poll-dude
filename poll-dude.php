<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/liaisontw/poll-dude
 * @since             1.0.0
 * @package           Poll Dude
 *
 * @wordpress-plugin
 * Plugin Name:       Poll Dude
 * Plugin URI:        https://github.com/liaisontw/poll-dude
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Liaison Chang
 * Author URI:        https://github.com/liaisontw/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       poll-dude
 * Domain Path:       /languages
 */

// Exit If Accessed Directly
if(!defined('ABSPATH')){
    exit;
}
 

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'POLL_DUDE_VERSION', '1.0.0' );

// Create Text Domain For Translations
//add_action( 'plugins_loaded', 'polldude_textdomain' );
add_action( 'admin_menu', 'polldude_textdomain' );
function polldude_textdomain() {
	load_plugin_textdomain( 'poll-dude-domain' );
}


// polldude Table Name
global $wpdb;
$wpdb->pollsq   = $wpdb->prefix.'pollsq';
$wpdb->pollsa   = $wpdb->prefix.'pollsa';
$wpdb->pollsip  = $wpdb->prefix.'pollsip';

global $poll_dude_base;
$poll_dude_base = plugin_basename(__FILE__);
$plugin_name = 'poll-dude';

if( ! function_exists( 'removeslashes' ) ) {
	function removeslashes( $string ) {
		$string = implode( '', explode( '\\', $string ) );
		return stripslashes( trim( $string ) );
	}
}

function poll_dude_time_make($fieldname /*= 'pollq_timestamp'*/) {

	$time_parse = array('_hour'   => 0, '_minute' => 0, '_second' => 0,
						'_day'    => 0, '_month'  => 0, '_year'   => 0
	);

	foreach($time_parse as $key => $value) {
		$poll_dude_time_stamp = $fieldname.$key;

		$time_parse[$key] = isset( $_POST[$poll_dude_time_stamp] ) ? 
				 (int) sanitize_key( $_POST[$poll_dude_time_stamp] ) : 0;
	}

	$return_timestamp = gmmktime( $time_parse['_hour']  , 
								  $time_parse['_minute'], 
								  $time_parse['_second'], 
								  $time_parse['_month'] , 
								  $time_parse['_day']   , 
								  $time_parse['_year']   );

	return 	$return_timestamp;					  
}


// poll_dude_time_select
function poll_dude_time_select($poll_dude_time, $fieldname = 'pollq_timestamp', $display = 'block') {
	
	$time_select = array(
		'_hour'   => array('unit'=>'H', 'min'=>0   , 'max'=>24  , 'padding'=>'H:'),
		'_minute' => array('unit'=>'i', 'min'=>0   , 'max'=>61  , 'padding'=>'M:'),
		'_second' => array('unit'=>'s', 'min'=>0   , 'max'=>61  , 'padding'=>'S@'),
		'_day'    => array('unit'=>'j', 'min'=>0   , 'max'=>32  , 'padding'=>'D&nbsp;'),
		'_month'  => array('unit'=>'n', 'min'=>0   , 'max'=>13  , 'padding'=>'M&nbsp;'),
		'_year'   => array('unit'=>'Y', 'min'=>2010, 'max'=>2030, 'padding'=>'Y')
	);

	echo '<div id="'.$fieldname.'" style="display: '.$display.'">'."\n";
	echo '<span dir="ltr">'."\n";

	foreach($time_select as $key => $value) {
		$time_value = (int) gmdate($value['unit'], $poll_dude_time);
		$time_stamp = $fieldname.$key;
		echo "<select name=\"$time_stamp\" size=\"1\">"."\n";
		for($i = $value['min']; $i < $value['max']; $i++) {
			if($time_value === $i) {
				echo "<option value=\"$i\" selected=\"selected\">$i</option>\n";
			} else {
				echo "<option value=\"$i\">$i</option>\n";
			}
		}
		echo '</select>&nbsp;'.$value['padding']."\n";		
	}

	echo '</span>'."\n";
	echo '</div>'."\n";
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
//require plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude.php';

### Function: Poll Administration Menu


add_action( 'admin_menu', 'polldude_menu' );
function polldude_menu() {
	$page_title = __( 'Poll Dude', 'poll-dude-domain' );
	$menu_title = __( 'Poll Dude', 'poll-dude-domain' );
	$capability = 'manage_options';
	$menu_slug  = 'poll_dude_manager';

	add_menu_page(
		$page_title,
		$menu_title,
		$capability,
		$menu_slug,
		'',
		'dashicons-chart-bar'
	);

	$parent_slug = 'poll_dude_manager';
	$page_title  = __( 'Add Poll', 'poll-dude-domain' );
	$menu_title  = __( 'Add Poll', 'poll-dude-domain' );
	$capability  = 'manage_options';
	$menu_slug   = plugin_dir_path(__FILE__) . '/includes/page-poll-dude-add-form.php';

	
	add_submenu_page( 
		$parent_slug, 
		$page_title, 
		$menu_title, 
		$capability, 
		$menu_slug 
	);

	$parent_slug = 'poll_dude_manager';
	$page_title  = __( 'Control Panel', 'poll-dude-domain' );
	$menu_title  = __( 'Control Panel', 'poll-dude-domain' );
	$capability  = 'manage_options';
	$menu_slug   = plugin_dir_path(__FILE__) . '/includes/page-poll-dude-control-panel.php';

	
	add_submenu_page( 
		$parent_slug, 
		$page_title, 
		$menu_title, 
		$capability, 
		$menu_slug 
	);

	$parent_slug = 'poll_dude_manager';
	$page_title  = __( 'Poll Setting', 'poll-dude-domain' );
	$menu_title  = __( 'Poll Setting', 'poll-dude-domain' );
	$capability  = 'manage_options';
	$menu_slug   = 'setting_polls';

	add_submenu_page( 
		$parent_slug, 
		$page_title, 
		$menu_title, 
		$capability, 
		$menu_slug 
	);
	

}




// Load Shortcodes
require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-shortcodes.php');

// Check if admin and include admin scripts
add_action('admin_init','poll_dude_scripts_admin');
function poll_dude_scripts_admin(){
	//wp_enqueue_script('poll-dude-admin', plugin_dir_url( __FILE__ ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);
	wp_enqueue_script('poll-dude-admin', plugin_dir_url( __FILE__ ) . 'admin/js/poll-dude-admin.js', array( 'jquery' ), POLL_DUDE_VERSION, true);

	wp_localize_script('poll-dude-admin', 'pollsAdminL10n', array(
			'admin_ajax_url' => admin_url('admin-ajax.php'),
			'text_direction' => is_rtl() ? 'right' : 'left',
			'text_delete_poll' => __('Delete Poll', 'wp-polls'),
			'text_no_poll_logs' => __('No poll logs available.', 'wp-polls'),
			'text_delete_all_logs' => __('Delete All Logs', 'wp-polls'),
			'text_checkbox_delete_all_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs.', 'wp-polls'),
			'text_delete_poll_logs' => __('Delete Logs For This Poll Only', 'wp-polls'),
			'text_checkbox_delete_poll_logs' => __('Please check the \\\'Yes\\\' checkbox if you want to delete all logs for this poll ONLY.', 'wp-polls'),
			'text_delete_poll_ans' => __('Delete Poll Answer', 'wp-polls'),
			'text_open_poll' => __('Open Poll', 'wp-polls'),
			'text_close_poll' => __('Close Poll', 'wp-polls'),
			'text_answer' => __('Answer', 'wp-polls'),
			'text_remove_poll_answer' => __('Remove', 'wp-polls')
		));

}

### Funcion: Get Latest Poll ID
/*
function polls_latest_id() {
	global $wpdb;
	$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY pollq_timestamp DESC LIMIT 1");
	return (int) $poll_id;
}
*/

function poll_dude_latest_id() {
	global $wpdb;
	$poll_id = $wpdb->get_var("SELECT pollq_id FROM $wpdb->pollsq WHERE pollq_active = 1 ORDER BY pollq_timestamp DESC LIMIT 1");
	return (int) $poll_id;
}


### Function: Manage Polls
add_action('wp_ajax_poll-dude-control', 'poll_dude_control_panel');
function poll_dude_control_panel() {
	global $wpdb;
	
	### Form Processing
	if( isset( $_POST['action'] ) && sanitize_key( $_POST['action'] ) === 'poll-dude-control' ) {
	//if( isset( $_POST['action'] ) && sanitize_key( $_POST['action'] ) === 'poll-dude' ) {
		if( ! empty( $_POST['do'] ) ) {
			// Set Header
			header('Content-Type: text/html; charset='.get_option('blog_charset').'');

			// Decide What To Do
			switch($_POST['do']) {
				// Delete Polls Logs
				case __('Delete All Logs', 'wp-polls'):
					check_ajax_referer('wp-polls_delete-polls-logs');
					if( sanitize_key( trim( $_POST['delete_logs_yes'] ) ) === 'yes') {
						$delete_logs = $wpdb->query("DELETE FROM $wpdb->pollsip");
						if($delete_logs) {
							echo '<p style="color: green;">'.__('All Polls Logs Have Been Deleted.', 'wp-polls').'</p>';
						} else {
							echo '<p style="color: red;">'.__('An Error Has Occurred While Deleting All Polls Logs.', 'wp-polls').'</p>';
						}
					}
					break;
				// Delete Poll Logs For Individual Poll
				case __('Delete Logs For This Poll Only', 'wp-polls'):
					check_ajax_referer('wp-polls_delete-poll-logs');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					if( sanitize_key( trim( $_POST['delete_logs_yes'] ) ) === 'yes') {
						$delete_logs = $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
						if( $delete_logs ) {
							echo '<p style="color: green;">'.sprintf(__('All Logs For \'%s\' Has Been Deleted.', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
						} else {
							echo '<p style="color: red;">'.sprintf(__('An Error Has Occurred While Deleting All Logs For \'%s\'', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
						}
					}
					break;
				// Delete Poll's Answer
				case __('Delete Poll Answer', 'wp-polls'):
					check_ajax_referer('wp-polls_delete-poll-answer');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$polla_aid = (int) sanitize_key( $_POST['polla_aid'] );
					$poll_answers = $wpdb->get_row( $wpdb->prepare( "SELECT polla_votes, polla_answers FROM $wpdb->pollsa WHERE polla_aid = %d AND polla_qid = %d", $polla_aid, $pollq_id ) );
					$polla_votes = (int) $poll_answers->polla_votes;
					$polla_answers = wp_kses_post( removeslashes( trim( $poll_answers->polla_answers ) ) );
					$delete_polla_answers = $wpdb->delete( $wpdb->pollsa, array( 'polla_aid' => $polla_aid, 'polla_qid' => $pollq_id ), array( '%d', '%d' ) );
					$delete_pollip = $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id, 'pollip_aid' => $polla_aid ), array( '%d', '%d' ) );
					$update_pollq_totalvotes = $wpdb->query( "UPDATE $wpdb->pollsq SET pollq_totalvotes = (pollq_totalvotes - $polla_votes) WHERE pollq_id = $pollq_id" );
					if($delete_polla_answers) {
						//echo '<p style="color: green;">'.sprintf(__('Poll Answer \'%s\' Deleted Successfully.', 'wp-polls'), $polla_answers).'</p>';
						echo '<p style="color: green;">'.sprintf(__('Poll Answer Deleted Successfully.', 'wp-polls')).'</p>';
					} else {
						echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll Answer \'%s\'.', 'wp-polls'), $polla_answers).'</p>';
					}
					break;
				// Open Poll
				case __('Open Poll', 'wp-polls'):
					check_ajax_referer('wp-polls_open-poll');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					$open_poll = $wpdb->update(
						$wpdb->pollsq,
						array(
							'pollq_active' => 1
						),
						array(
							'pollq_id' => $pollq_id
						),
						array(
							'%d'
						),
						array(
							'%d'
						)
					);
					if( $open_poll ) {
						echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Opened', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					} else {
						echo '<p style="color: red;">'.sprintf(__('Error Opening Poll \'%s\'', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					break;
				// Close Poll
				case __('Close Poll', 'wp-polls'):
					check_ajax_referer('wp-polls_close-poll');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					$close_poll = $wpdb->update(
						$wpdb->pollsq,
						array(
							'pollq_active' => 0
						),
						array(
							'pollq_id' => $pollq_id
						),
						array(
							'%d'
						),
						array(
							'%d'
						)
					);
					if( $close_poll ) {
						echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Is Now Closed', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					} else {
						echo '<p style="color: red;">'.sprintf(__('Error Closing Poll \'%s\'', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					break;
				// Delete Poll
				case __('Delete Poll', 'wp-polls'):
					check_ajax_referer('wp-polls_delete-poll');
					$pollq_id  = (int) sanitize_key( $_POST['pollq_id'] );
					$pollq_question = $wpdb->get_var( $wpdb->prepare( "SELECT pollq_question FROM $wpdb->pollsq WHERE pollq_id = %d", $pollq_id ) );
					$delete_poll_question = $wpdb->delete( $wpdb->pollsq, array( 'pollq_id' => $pollq_id ), array( '%d' ) );
					$delete_poll_answers =  $wpdb->delete( $wpdb->pollsa, array( 'polla_qid' => $pollq_id ), array( '%d' ) );
					$delete_poll_ip =	   $wpdb->delete( $wpdb->pollsip, array( 'pollip_qid' => $pollq_id ), array( '%d' ) );
					$poll_option_lastestpoll = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'poll_latestpoll'");
					if(!$delete_poll_question) {
						echo '<p style="color: red;">'.sprintf(__('Error In Deleting Poll \'%s\' Question', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					if(empty($text)) {
						echo '<p style="color: green;">'.sprintf(__('Poll \'%s\' Deleted Successfully', 'wp-polls'), wp_kses_post( removeslashes( $pollq_question ) ) ).'</p>';
					}
					
					// Update Lastest Poll ID To Poll Options
					//update_option( 'poll_latestpoll', polls_latest_id() );
					update_option( 'poll_latestpoll', poll_dude_latest_id() );
					do_action( 'wp_polls_delete_poll', $pollq_id );
					//die('Debug');
					
					break;
			}
			exit();
		}
	}
}

if ( is_admin() ) {
	//add_action('admin_enqueue_scripts', 'poll_dude_scripts_admin');
	
	
	// Load Custom Post Type
	//require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-custom-post-type.php');

	// Load Settings
	//require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-settings.php');
	
	// Load Post Fields
	//require_once(plugin_dir_path(__FILE__) . '/includes/class-poll-dude-fields.php');
}


	

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-poll-dude-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-activator.php';
	Plugin_Name_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-poll-dude-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-poll-dude-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

//register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

### Function: Activate Plugin
register_activation_hook( __FILE__, 'poll_dude_activation' );
function poll_dude_activation( $network_wide ) {
	if ( is_multisite() && $network_wide ) {
		$ms_sites = wp_get_sites();

		if( 0 < count( $ms_sites ) ) {
			foreach ( $ms_sites as $ms_site ) {
				switch_to_blog( $ms_site['blog_id'] );
				poll_dude_activate();
				restore_current_blog();
			}
		}
	} else {
		poll_dude_activate();
	}
}

function poll_dude_activate() {
	global $wpdb;

	if(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}

	// Create Poll Tables (3 Tables)
	$charset_collate = $wpdb->get_charset_collate();

	$create_table = array();
	$create_table['pollsq'] = "CREATE TABLE $wpdb->pollsq (".
							  "pollq_id int(10) NOT NULL auto_increment," .
							  "pollq_question varchar(200) character set utf8 NOT NULL default ''," .
							  "pollq_timestamp varchar(20) NOT NULL default ''," .
							  "pollq_totalvotes int(10) NOT NULL default '0'," .
							  "pollq_active tinyint(1) NOT NULL default '1'," .
							  "pollq_expiry int(10) NOT NULL default '0'," .
							  "pollq_multiple tinyint(3) NOT NULL default '0'," .
							  "pollq_totalvoters int(10) NOT NULL default '0'," .
							  "PRIMARY KEY  (pollq_id)" .
							  ") $charset_collate;";
	$create_table['pollsa'] = "CREATE TABLE $wpdb->pollsa (" .
							  "polla_aid int(10) NOT NULL auto_increment," .
							  "polla_qid int(10) NOT NULL default '0'," .
							  "polla_answers varchar(200) character set utf8 NOT NULL default ''," .
							  "polla_votes int(10) NOT NULL default '0'," .
							  "PRIMARY KEY  (polla_aid)" .
							  ") $charset_collate;";
	$create_table['pollsip'] = "CREATE TABLE $wpdb->pollsip (" .
							   "pollip_id int(10) NOT NULL auto_increment," .
							   "pollip_qid int(10) NOT NULL default '0'," .
							   "pollip_aid int(10) NOT NULL default '0'," .
							   "pollip_ip varchar(100) NOT NULL default ''," .
							   "pollip_host VARCHAR(200) NOT NULL default ''," .
							   "pollip_timestamp int(10) NOT NULL default '0'," .
							   "pollip_user tinytext NOT NULL," .
							   "pollip_userid int(10) NOT NULL default '0'," .
							   "PRIMARY KEY  (pollip_id)," .
							   "KEY pollip_ip (pollip_ip)," .
							   "KEY pollip_qid (pollip_qid)," .
							   "KEY pollip_ip_qid (pollip_ip, pollip_qid)" .
							   ") $charset_collate;";
	dbDelta( $create_table['pollsq'] );
	dbDelta( $create_table['pollsa'] );
	dbDelta( $create_table['pollsip'] );
	// Check Whether It is Install Or Upgrade
	$first_poll = $wpdb->get_var( "SELECT pollq_id FROM $wpdb->pollsq LIMIT 1" );
	// If Install, Insert 1st Poll Question With 5 Poll Answers
	if ( empty( $first_poll ) ) {
		// Insert Poll Question (1 Record)
		$insert_pollq = $wpdb->insert( $wpdb->pollsq, array( 'pollq_question' => __( 'How Is My Site?', 'wp-polls' ), 'pollq_timestamp' => current_time( 'timestamp' ) ), array( '%s', '%s' ) );
		if ( $insert_pollq ) {
			// Insert Poll Answers  (5 Records)
			$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Good', 'wp-polls' ) ), array( '%d', '%s' ) );
			$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Excellent', 'wp-polls' ) ), array( '%d', '%s' ) );
			$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Bad', 'wp-polls' ) ), array( '%d', '%s' ) );
			$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'Can Be Improved', 'wp-polls' ) ), array( '%d', '%s' ) );
			$wpdb->insert( $wpdb->pollsa, array( 'polla_qid' => $insert_pollq, 'polla_answers' => __( 'No Comments', 'wp-polls' ) ), array( '%d', '%s' ) );
		}
	}
	// Add In Options (16 Records)
	add_option('poll_template_voteheader', '<p style="text-align: center;"><strong>%POLL_QUESTION%</strong></p>'.
	'<div id="polls-%POLL_ID%-ans" class="wp-polls-ans">'.
	'<ul class="wp-polls-ul">');
	add_option('poll_template_votebody', '<li><input type="%POLL_CHECKBOX_RADIO%" id="poll-answer-%POLL_ANSWER_ID%" name="poll_%POLL_ID%" value="%POLL_ANSWER_ID%" /> <label for="poll-answer-%POLL_ANSWER_ID%">%POLL_ANSWER%</label></li>');
	add_option('poll_template_votefooter', '</ul>'.
	'<p style="text-align: center;"><input type="button" name="vote" value="   '.__('Vote', 'wp-polls').'   " class="Buttons" onclick="poll_vote(%POLL_ID%);" /></p>'.
	'<p style="text-align: center;"><a href="#ViewPollResults" onclick="poll_result(%POLL_ID%); return false;" title="'.__('View Results Of This Poll', 'wp-polls').'">'.__('View Results', 'wp-polls').'</a></p>'.
	'</div>');
	add_option('poll_template_resultheader', '<p style="text-align: center;"><strong>%POLL_QUESTION%</strong></p>'.
	'<div id="polls-%POLL_ID%-ans" class="wp-polls-ans">'.
	'<ul class="wp-polls-ul">');
	add_option('poll_template_resultbody', '<li>%POLL_ANSWER% <small>(%POLL_ANSWER_PERCENTAGE%%'.__(',', 'wp-polls').' %POLL_ANSWER_VOTES% '.__('Votes', 'wp-polls').')</small><div class="pollbar" style="width: %POLL_ANSWER_IMAGEWIDTH%%;" title="%POLL_ANSWER_TEXT% (%POLL_ANSWER_PERCENTAGE%% | %POLL_ANSWER_VOTES% '.__('Votes', 'wp-polls').')"></div></li>');
	add_option('poll_template_resultbody2', '<li><strong><i>%POLL_ANSWER% <small>(%POLL_ANSWER_PERCENTAGE%%'.__(',', 'wp-polls').' %POLL_ANSWER_VOTES% '.__('Votes', 'wp-polls').')</small></i></strong><div class="pollbar" style="width: %POLL_ANSWER_IMAGEWIDTH%%;" title="'.__('You Have Voted For This Choice', 'wp-polls').' - %POLL_ANSWER_TEXT% (%POLL_ANSWER_PERCENTAGE%% | %POLL_ANSWER_VOTES% '.__('Votes', 'wp-polls').')"></div></li>');
	add_option('poll_template_resultfooter', '</ul>'.
	'<p style="text-align: center;">'.__('Total Voters', 'wp-polls').': <strong>%POLL_TOTALVOTERS%</strong></p>'.
	'</div>');
	add_option('poll_template_resultfooter2', '</ul>'.
	'<p style="text-align: center;">'.__('Total Voters', 'wp-polls').': <strong>%POLL_TOTALVOTERS%</strong></p>'.
	'<p style="text-align: center;"><a href="#VotePoll" onclick="poll_booth(%POLL_ID%); return false;" title="'.__('Vote For This Poll', 'wp-polls').'">'.__('Vote', 'wp-polls').'</a></p>'.
	'</div>');
	add_option('poll_template_disable', __('Sorry, there are no polls available at the moment.', 'wp-polls'));
	add_option('poll_template_error', __('An error has occurred when processing your poll.', 'wp-polls'));
	add_option('poll_currentpoll', 0);
	add_option('poll_latestpoll', 1);
	add_option('poll_archive_perpage', 5);
	add_option('poll_ans_sortby', 'polla_aid');
	add_option('poll_ans_sortorder', 'asc');
	add_option('poll_ans_result_sortby', 'polla_votes');
	add_option('poll_ans_result_sortorder', 'desc');
	// Database Upgrade For WP-Polls 2.1
	add_option('poll_logging_method', '3');
	add_option('poll_allowtovote', '2');
	// Database Upgrade For WP-Polls 2.12
	add_option('poll_archive_url', site_url('pollsarchive'));
	// Database Upgrade For WP-Polls 2.13
	add_option('poll_bar', array('style' => 'default', 'background' => 'd8e1eb', 'border' => 'c8c8c8', 'height' => 8));
	// Database Upgrade For WP-Polls 2.14
	add_option('poll_close', 1);
	// Database Upgrade For WP-Polls 2.20
	add_option('poll_ajax_style', array('loading' => 1, 'fading' => 1));
	add_option('poll_template_pollarchivelink', '<ul>'.
	'<li><a href="%POLL_ARCHIVE_URL%">'.__('Polls Archive', 'wp-polls').'</a></li>'.
	'</ul>');
	add_option('poll_archive_displaypoll', 2);
	add_option('poll_template_pollarchiveheader', '');
	add_option('poll_template_pollarchivefooter', '<p>'.__('Start Date:', 'wp-polls').' %POLL_START_DATE%<br />'.__('End Date:', 'wp-polls').' %POLL_END_DATE%</p>');

	$pollq_totalvoters = (int) $wpdb->get_var( "SELECT SUM(pollq_totalvoters) FROM $wpdb->pollsq" );
	if ( 0 === $pollq_totalvoters ) {
		$wpdb->query( "UPDATE $wpdb->pollsq SET pollq_totalvoters = pollq_totalvotes" );
	}

	// Database Upgrade For WP-Polls 2.30
	add_option('poll_cookielog_expiry', 0);
	add_option('poll_template_pollarchivepagingheader', '');
	add_option('poll_template_pollarchivepagingfooter', '');
	// Database Upgrade For WP-Polls 2.50
	delete_option('poll_archive_show');

	// Index
	$index = $wpdb->get_results( "SHOW INDEX FROM $wpdb->pollsip;" );
	$key_name = array();
	if( count( $index ) > 0 ) {
		foreach( $index as $i ) {
			$key_name[]= $i->Key_name;
		}
	}
	if ( ! in_array( 'pollip_ip', $key_name, true ) ) {
		$wpdb->query( "ALTER TABLE $wpdb->pollsip ADD INDEX pollip_ip (pollip_ip);" );
	}
	if ( ! in_array( 'pollip_qid', $key_name, true ) ) {
		$wpdb->query( "ALTER TABLE $wpdb->pollsip ADD INDEX pollip_qid (pollip_qid);" );
	}
	if ( ! in_array( 'pollip_ip_qid_aid', $key_name, true ) ) {
		$wpdb->query( "ALTER TABLE $wpdb->pollsip ADD INDEX pollip_ip_qid_aid (pollip_ip, pollip_qid, pollip_aid);" );
	}
	// No longer needed index
	if ( in_array( 'pollip_ip_qid', $key_name, true ) ) {
		$wpdb->query( "ALTER TABLE $wpdb->pollsip DROP INDEX pollip_ip_qid;" );
	}

	// Change column datatype for wp_pollsip
	$col_pollip_qid = $wpdb->get_row( "DESCRIBE $wpdb->pollsip pollip_qid" );
	if( 'varchar(10)' === $col_pollip_qid->Type ) {
		$wpdb->query( "ALTER TABLE $wpdb->pollsip MODIFY COLUMN pollip_qid int(10) NOT NULL default '0';" );
		$wpdb->query( "ALTER TABLE $wpdb->pollsip MODIFY COLUMN pollip_aid int(10) NOT NULL default '0';" );
		$wpdb->query( "ALTER TABLE $wpdb->pollsip MODIFY COLUMN pollip_timestamp int(10) NOT NULL default '0';" );
		$wpdb->query( "ALTER TABLE $wpdb->pollsq MODIFY COLUMN pollq_expiry int(10) NOT NULL default '0';" );
	}

	// Set 'manage_polls' Capabilities To Administrator
	$role = get_role( 'administrator' );
	if( ! $role->has_cap( 'manage_polls' ) ) {
		$role->add_cap( 'manage_polls' );
	}
	//cron_polls_place();
}
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Plugin_Name();
	$plugin->run();

}
//run_plugin_name();
