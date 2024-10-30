<?php
/**
 * Class Mailmaga_x_install for installation
 */

define('MAILMAGA_X_DB_VERSION','1');

if (!class_exists("Mailmaga_x_install")) {
	class Mailmaga_x_install extends Mailmaga_x {
		var $db_version = MAILMAGA_X_DB_VERSION;

		function Mailmaga_x_install() { //constructor
			parent::init();
				
		}

		/**
		 * Mailmaga X activate function
		 */
		function activate(){
			$this->upgrade_check();
		}
		function deactivation(){

		}
		function uninstall(){
			global $wpdb, $table_prefix;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			
			$wpdb->query("DROP TABLE IF EXISTS {$table_prefix}mailmaga_x_historys");
			$wpdb->query("DROP TABLE IF EXISTS {$table_prefix}mailmaga_x_mails");
			$wpdb->query("DROP TABLE IF EXISTS {$table_prefix}mailmaga_x_servers");
			$wpdb->query("DROP TABLE IF EXISTS {$table_prefix}mailmaga_x_users");
			
			update_option('mailmaga_x_db_version', '0');
		}
		/**
		 * DB creator
		 */
		function upgrade_check() {
			global $wpdb, $table_prefix;

			$installed_version = get_option('mailmaga_x_db_version');
			//DB initialize
			if (empty($installed_version)) {
				if (!empty($wpdb->charset))
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
				if (!empty($wpdb->collate))
				$charset_collate .= " COLLATE $wpdb->collate";

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';

				$sqls = array();
				$sqls[] = "CREATE TABLE {$table_prefix}mailmaga_x_users (
                        user_id BIGINT( 20 ) unsigned NOT NULL AUTO_INCREMENT,
                        user_name VARCHAR( 100 ) DEFAULT NULL ,
                        user_email VARCHAR( 100 ) NOT NULL DEFAULT '' ,
                        user_group VARCHAR( 100 ) DEFAULT NULL ,
                        PRIMARY KEY  (user_id)
                        ) {$charset_collate};";
				
				$sqls[] = "CREATE TABLE {$table_prefix}mailmaga_x_servers (
                        server_id BIGINT( 20 ) unsigned NOT NULL AUTO_INCREMENT,
                        server_name VARCHAR( 100 ) NOT NULL DEFAULT '' ,
                        type VARCHAR( 50 ) DEFAULT NULL ,
                        sleep INT( 2 ) DEFAULT '0' ,
                        host VARCHAR( 50 ) DEFAULT NULL ,
                        auth INT( 1 ) DEFAULT 1 ,
                        port VARCHAR( 10 ) DEFAULT NULL ,
                        account VARCHAR( 100 ) DEFAULT NULL ,
                        password VARCHAR( 100 ) DEFAULT NULL ,
                        PRIMARY KEY  (server_id)
                        ) {$charset_collate};";
				
				$sqls[] = "CREATE TABLE {$table_prefix}mailmaga_x_mails (
                        mail_id BIGINT( 20 ) unsigned NOT NULL AUTO_INCREMENT,
                        title VARCHAR( 255 ) NOT NULL DEFAULT '',
                        content TEXT NOT NULL DEFAULT '' ,
                        user_ids TEXT NOT NULL DEFAULT '' ,
                        server_ids TEXT NOT NULL DEFAULT '' ,
                        created_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ,
                        PRIMARY KEY  (mail_id)
                        ) {$charset_collate};";
				
				$sqls[] = "CREATE TABLE {$table_prefix}mailmaga_x_historys (
                        history_id BIGINT( 20 ) unsigned NOT NULL AUTO_INCREMENT,
                        mail_id BIGINT( 20 ) NOT NULL ,
                        user_id VARCHAR( 50 ) NOT NULL ,
                        secret_key VARCHAR( 100 ) NOT NULL DEFAULT '' ,
                        success INT( 1 ) DEFAULT 0 ,
                        error TEXT DEFAULT NULL ,
                        created_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ,
                        opened_time datetime DEFAULT NULL ,
                        is_html INT( 1 ) DEFAULT 0 ,
                        PRIMARY KEY  (history_id)
                        ) {$charset_collate};";
				
				$sqls[] = "INSERT INTO {$table_prefix}mailmaga_x_servers (
						server_id, server_name, type, sleep, host, auth, port, account, password)
						 VALUES
						(NULL, '" . __('localhost') . "', 'mail', 0, 'localhost', 0, '', '', '');";
				
				dbDelta($sqls);
				
				update_option('mailmaga_x_db_version', $this->db_version);
			}
		}
	}
}
?>
