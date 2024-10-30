<?php
/**
 * Class Mailmaga_x_user for users page
 */
if (!class_exists("Mailmaga_x_user")) {
	class Mailmaga_x_user extends Mailmaga_x {

		private $user_list = array();
		private $group_list = array();

		function Mailmaga_x_user() { //constructor
			parent::init();
			global $wpdb , $table_prefix;
			$this->db = $wpdb;
			$this->pre = $table_prefix;
		}

		/**
		 * Mailmaga X console page [USERS]
		 */
		function index(){			
			$action = $_POST['action'] ? $_POST['action'] : $_GET['action'];
			switch ($action){
				case 'delete':
					if(!empty($_POST['user_ids'])){
						$this->param = $this->get_paramers($_GET , array('user_group','keyword'));
						$this->delete_user($_POST['user_ids']);
					}else{
						$this->param = $this->get_paramers($_GET , array('user_id','user_group','keyword'));
						$this->delete_user($this->param['user_id']);
					}
					$this->user_list = $this->user_list($this->param);
					include_once PLUGIN_MC_PATH . 'templates/user_index.php';
					break;
				case 'mail':
					$this->param = $this->get_paramers($_POST , array('user_ids'));
					$this->mail_input();
					break;
				case 'resend':
					$paramers = $this->get_paramers($_GET , array('mail_id'));
					$this->param = $this->get_mail_info($paramers['mail_id']);
					$this->mail_input();
					break;
				case 'retry':
					$paramers = $this->get_paramers($_GET , array('mail_id'));
					$this->mail_id = $paramers['mail_id'];
					$this->param = $this->get_mail_info($paramers['mail_id'], true);
					$this->mail_input();
					break;
				case 'completed':
					$this->param = $this->get_paramers($_POST , array('title','content','user_ids'));
					$this->message = $this->get_message(__('Send mail completed.',MAILMAGA_NAME));
					$this->mail_input();
					break;
				default:
					switch ($_GET['message']){
						case '1':
							$this->message = $this->get_message(__('Import CSV completed.',MAILMAGA_NAME));
							break;
						default:
							break;
					}
					$this->param = $this->get_paramers($_GET , array('user_id','user_group','keyword'));
					$this->user_list = $this->user_list($this->param);
					include_once PLUGIN_MC_PATH . 'templates/user_index.php';
					break;
			}

		}
		/**
		 * Show user list page
		 * @param $paramers
		 */
		function user_list($paramers){
			$pre = (empty($this->pre)) ? 'wp_' : $this->pre;

			$this->group_list['wp'] = 'WordPress Users';
			$sql = "SELECT DISTINCT user_group FROM {$this->pre}mailmaga_x_users";
			$groups = $this->db->get_results($sql);
			if(count($groups) > 0){
				foreach($groups as $g){
					$this->group_list[$g->user_group] = $g->user_group;
				}
			}
			$open_col = ", (SELECT IFNULL(COUNT(history_id),0) AS opened_count FROM {$this->pre}mailmaga_x_historys HO
							WHERE success = 1 AND is_html = 1 AND opened_time IS NOT NULL AND HO.user_id IN 
								(
								SELECT CONCAT('{$pre}',ID) AS user_id FROM {$this->pre}users CU_1 WHERE CU_1.user_email = U.user_email
								UNION 
								SELECT user_id FROM {$this->pre}mailmaga_x_users CU_2 WHERE CU_2.user_email = U.user_email
								)
							) AS opened_count
						, (SELECT IFNULL(COUNT(history_id),0) AS sent_count FROM {$this->pre}mailmaga_x_historys HO
							WHERE success = 1 AND is_html = 1 AND HO.user_id IN 
								(
								SELECT CONCAT('{$pre}',ID) AS user_id FROM {$this->pre}users CU_1 WHERE CU_1.user_email = U.user_email
								UNION 
								SELECT user_id FROM {$this->pre}mailmaga_x_users CU_2 WHERE CU_2.user_email = U.user_email
								)
							) AS sent_count";
			
			if($paramers['user_group'] == 'wp' || empty($paramers['user_group'])){
				$sql = "SELECT CONCAT('{$pre}',ID) AS user_id, display_name AS user_name, user_email
						{$open_col}
						, 'WordPress Users' AS user_group FROM {$this->pre}users U";
			}else{
				$sql = "SELECT user_id, user_name, user_email
						{$open_col}
						, user_group FROM {$this->pre}mailmaga_x_users U WHERE user_group = '".$paramers['user_group']."'";
			}
			$sql = "SELECT T.user_id, T.user_name, T.user_email, T.user_group
					, CASE T.sent_count WHEN 0 THEN 0
						ELSE ROUND(T.opened_count / T.sent_count * 100,0)
					  END AS opened_rate
					 FROM ({$sql}) AS T ORDER BY opened_rate DESC";
			return $this->db->get_results($sql,ARRAY_A);

		}
		/**
		 * Delete user by id or array
		 * @param $user_id
		 */
		function delete_user($user_id){
			if(is_array($user_id) && count($user_id) > 0){
				$user_ids = join(',',$user_id);
				$sql = "DELETE FROM {$this->pre}mailmaga_x_users WHERE user_id IN ( " . $user_ids . " )";
				$this->db->query( $sql );
			}elseif(!empty($user_id) && is_numeric($user_id)){
				$this->db->delete($this->pre."mailmaga_x_users" , array('user_id' => $user_id));	
			}
			$this->message = $this->get_message(__('Delete user completed.',MAILMAGA_NAME));
		}
		/**
		 * Show mail input page
		 */
		function mail_input() {
			$this->mail_sender = get_option('admin_email');
			$sql = "SELECT server_id, server_name FROM {$this->pre}mailmaga_x_servers";
			$this->server_list = $this->db->get_results($sql,ARRAY_A);
			include_once PLUGIN_MC_PATH . 'templates/mail_input.php';
		}
		/**
		 * Resend mail from history
		 * @param $mail_id
		 * @param $only_failed
		 * @return Array
		 */
		function get_mail_info($mail_id, $only_failed = false){
			if(empty($mail_id) || !is_numeric($mail_id)){
				return;
			}
			$sql = "SELECT * FROM {$this->pre}mailmaga_x_mails WHERE mail_id = '".$mail_id."'";
			$mail_info = $this->db->get_row($sql,ARRAY_A);
			if($only_failed == true){
				//Retry sending mail
				$sql = "SELECT user_id FROM {$this->pre}mailmaga_x_historys WHERE success = 0 AND mail_id = '".$mail_id."'";
				$failed_users = $this->db->get_results($sql,ARRAY_A);
				if(!empty($failed_users)){
					$mail_info['user_ids'] = array();
					foreach($failed_users as $u){
						$mail_info['user_ids'][] = $u['user_id'];
					}
				}
			}else{
				$mail_info['user_ids'] = explode(',',$mail_info['user_ids']);
			}
			$mail_info['server_ids'] = explode(',',$mail_info['server_ids']);
			return $mail_info;
		}
		/**
		 * Import csv file
		 */
		function import_csv(){
			$file = $_FILES['file'];
			if($file['size'] == 0 || !file_exists($file['tmp_name'])){
				$json['error'][] = __("CSV file is empty.",MAILMAGA_NAME);
				$json['result'] = false;
				echo json_encode($json);
				exit;
			}
			$csv = new Mailmaga_x_csv($file['tmp_name']);
			if(!is_array($csv->data)){
				$json['error'][] = __("CSV file is empty.",MAILMAGA_NAME);
				$json['result'] = false;
				echo json_encode($json);
				exit;
			}
			$user_data = array();
			foreach($csv->data as $k => $c){
				if(empty($c['name'])){
					$json['error'][] = __('Name cannot be empty',MAILMAGA_NAME);
				}
				if(empty($c['email'])){
					$json['error'][] = __('Email cannot be empty',MAILMAGA_NAME);
				}elseif(!preg_match("/^[a-zA-Z0-9_\.@\+\?-]+$/i",$c['email'])){
					$json['error'][] = __('Email is not correct.',MAILMAGA_NAME);
				}
				if(empty($c['group'])){
					$json['error'][] = __('Group cannot be empty',MAILMAGA_NAME);
				}
				if(!empty($json['error'])){
					$json['result'] = false;
					echo json_encode($json);
					exit;
				}
				$insert = array();
				$insert['user_name'] = $c['name'];
				$insert['user_email'] = $c['email'];
				$insert['user_group'] = $c['group'];
				$json['user_group'] = $insert['user_group'];
				$user_data[] = $insert;
			}

			foreach($user_data as $k => $u){
				$this->db->insert($this->pre.'mailmaga_x_users', $u);
			}
			//TODO rollback
			$json['result'] = true;
			echo json_encode($json);
			exit;
		}
		/**
		 * Export user data to csv file
		 */
		function export_csv(){
			$this->param = $this->get_paramers($_GET , array('user_id','user_group','keyword'));
			$this->user_list = $this->user_list($this->param);
			
			$csv_data = array();
			$header = array('name','email','group');
			$csv_data[] = $header;
			if(!empty($this->user_list) && count($this->user_list) > 0){
				foreach($this->user_list as $k => $u){
					$user = array();
					$user['user_name'] = $u['user_name'];
					$user['user_email'] = $u['user_email'];
					$user['user_group'] = $u['user_group'];
					$csv_data[] = $user;
				}
			}
			$csv = new Mailmaga_x_csv();
			$csv->output ('user_list.csv', $csv_data);
			exit;
		}
		/**
		 * Check mail form input error
		 * @param $param
		 */
		function check_error($param){
			$errors = array();
			if(empty($param['title'])){
				$errors[] = __('Mail subject cannot be empty.',MAILMAGA_NAME);
			}
			if(empty($param['content'])){
				$errors[] = __('Mail body cannot be empty.',MAILMAGA_NAME);
			}
			if(empty($param['mail_servers']) || count($param['mail_servers']) == 0){
				$errors[] = __('Mail server must be selected.',MAILMAGA_NAME);
			}
			return  $errors;
		}
		/**
		 * Format rate string
		 * @param $total
		 * @param $open
		 */
		function get_rate($total,$open){
			if($total == 0) return '0%';
			return (round($open/$total,2) * 100) . '%';
		}
	}
}
?>
