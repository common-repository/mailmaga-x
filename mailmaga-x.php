<?php
/*
Plugin Name: Mailmaga-X
Plugin URI: http://www.xgeek.net/mailmaga-x/
Description: Mailmaga-X is a simple and powerful plugin for sending Mail Magazines to your customers.
Author: xgeek
Version: 1.0.2
Author URI: http://www.xgeek.net/
*/

define('PLUGIN_MC_PATH', rtrim(realpath(rtrim(realpath(dirname(__FILE__)), '/\\') . '/'), '/\\') . '/');
define('MAILMAGA_NAME','mailmaga-x');

if(file_exists(PLUGIN_MC_PATH.'classes/modules/Mailmaga-x.php')){
	require_once PLUGIN_MC_PATH.'classes/modules/Mailmaga-x.php';
}
global $mailmaga_x_page;

if (class_exists("Mailmaga_x")) {
	$mailmaga_x_page = new Mailmaga_x();
	register_activation_hook( __FILE__, 'mailmaga_x_activate' );  
	register_deactivation_hook( __FILE__, 'mailmaga_x_deactivation' );  
	register_uninstall_hook( __FILE__, 'mailmaga_x_uninstall' );  
	add_action( 'init', array(&$mailmaga_x_page, 'mailmaga_x_init') );
	add_action( 'admin_menu', array(&$mailmaga_x_page, 'mailmaga_x_admin_menu') );
	
	$ajax_page_1 = new Mailmaga_x_mailer();
	add_action('wp_ajax_mailmaga_x_send_mail', array(&$ajax_page_1, 'index'));
	add_action('plugins_loaded', array(&$ajax_page_1, 'mail_record'));
	
	$ajax_page_2 = new Mailmaga_x_user();
	add_action('wp_ajax_mailmaga_x_export_csv', array(&$ajax_page_2, 'export_csv'));
	add_action('wp_ajax_mailmaga_x_import_csv', array(&$ajax_page_2, 'import_csv'));

	//add_action('mailmaga_x_cron', 'cron_send_mail'); 
	//wp_schedule_event(time()+5, 'hourly', 'mailmaga_x_cron'); 
	//wp_schedule_single_event(time()+10, 'mailmaga_x_cron'); 
}

/**
 * Plugin activate hook function
 */
function mailmaga_x_activate(){
	$mailmaga_x_data = new Mailmaga_x_install();
	$mailmaga_x_data->activate();
}
/**
 * Plugin deactivation hook funtion
 */
function mailmaga_x_deactivation(){
	$mailmaga_x_data = new Mailmaga_x_install();
	$mailmaga_x_data->deactivation();
}
/**
 * Plugin uninstall hook function
 */
function mailmaga_x_uninstall(){
	$mailmaga_x_data = new Mailmaga_x_install();
	$mailmaga_x_data->uninstall();
}
/**
 * Page for index
 */
function mailmaga_x_admin(){
	global $mailmaga_x_page;
	
	$mailmaga_x_page = new Mailmaga_x();
	$mailmaga_x_page->index();	
}
/**
 * Page for users
 */
function mailmaga_x_page_users(){
	global $mailmaga_x_page;
	
	$mailmaga_x_page = new Mailmaga_x_user();
	$mailmaga_x_page->index();	
}
/**
 * Page for servers
 */
function mailmaga_x_page_servers(){
	global $mailmaga_x_page;
	
	$mailmaga_x_page = new Mailmaga_x_server();
	$mailmaga_x_page->index();
}
/**
 * Page for history
 */
function mailmaga_x_page_history(){
	global $mailmaga_x_page;
	
	$mailmaga_x_page = new Mailmaga_x_history();
	$mailmaga_x_page->index();
}
?>
