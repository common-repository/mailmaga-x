<?php
/**
 * Class Mailmaga_x_mailer for mail sending
 */
if (!class_exists("Mailmaga_x_mailer")) {
	class Mailmaga_x_mailer extends Mailmaga_x {

		function Mailmaga_x_mailer() { //constructor
			parent::init();
			global $wpdb , $table_prefix;
			$this->db = $wpdb;
			$this->pre = $table_prefix;
		}
		/**
		 *
		 */

		function index(){
			$mode = $_POST['mode'] ? $_POST['mode'] : $_GET['mode'];
			switch ($mode){
				case 'preview':
					//HTML output
					if(!empty($_GET['mail_id'])){
						$paramers = $this->get_paramers($_GET , array('mail_id'));
					}else{
						$paramers = $this->get_paramers($_POST , array('content'));
						$paramers['content'] = urldecode($paramers['content']);
					}
					$this->preview_mail($paramers['mail_id'] ,$paramers['content']);
					exit;
					break;
				case 'mail_json':
					//JSON output
					$json = array();
					$paramers = $this->get_paramers($_POST , array('title','content','mail_servers','mail_id','user_ids'));
					$error = $this->check_error($paramers);
					if(count($error) > 0){
						$json['error'] = $error;
						$json['result'] = false;
						echo json_encode($json);
						exit;
					}
					$json['result'] = true;
					$json['mail_json'] = $this->get_mail_json($paramers);
					echo json_encode($json);
					exit;
				case 'cron_send':
					$paramers = $this->get_paramers($_POST , array('mail_id'));
					if(empty($paramers['mail_id']) || !is_numeric($paramers['mail_id'])){
						exit;
					}
					set_time_limit(0);
					ignore_user_abort(true);
					$this->cron_send_mail($paramers['mail_id']);
					die();
				case 'cron_process':
					//JSON output
					$json = array();
					$paramers = $this->get_paramers($_POST , array('mail_id'));
					if(empty($paramers['mail_id']) || !is_numeric($paramers['mail_id'])){
						$json['result'] = false;
						echo json_encode($json);
						exit;
					}
					$json['process'] = $this->get_process_json($paramers['mail_id']);
					$json['result'] = true;
					echo json_encode($json);
					exit;
				case 'send':
					//JSON output
					$paramers = $this->get_paramers($_POST , array('mail_id','server_id','user_id','user_name','user_email','is_test'));
					$is_test = ($paramers['is_test'] == '1') ? true : false;
					$json = $this->send_mail($paramers, $is_test);
					echo json_encode($json);
					exit;
					break;
				default:

					break;
			}
		}
		/**
		 * Preview mail by GET mail_id or POST content
		 * @param $mail_id
		 * @param $content
		 */
		function preview_mail($mail_id = null, $content = null){
			if(!empty($mail_id) && is_numeric($mail_id)){
				$sql = 'SELECT content FROM '.$this->pre.'mailmaga_x_mails WHERE mail_id = '.$mail_id;
				$mails = $this->db->get_row($sql,ARRAY_A);
				$content = $mails['content'];
			}
			if(!empty($content)){
				if($this->is_html($content)){
					echo $content;
				}else{
					echo nl2br($content);
				}
			}else{
				echo __('Mail body cannot be empty.',MAILMAGA_NAME);
			}
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
		 *
		 * @param $paramers
		 * @param $test_mail
		 */
		function get_mail_json($paramers, $test_mail = false){
			$mail_json = array();
			$pre = (empty($this->pre)) ? 'wp_' : $this->pre;

			$wp_user_ids = array();
			$mail_user_ids = array();
			foreach ($paramers['user_ids'] as $user_id){
				if(stristr($user_id,$pre) !== false){
					$wp_user_ids[] = str_replace($pre,'',$user_id);
				}elseif(is_numeric($user_id)){
					$mail_user_ids[] = $user_id;
				}
			}
			$mail_users = array();
			if(count($wp_user_ids) > 0){
				$sql = "SELECT CONCAT('{$pre}',ID) AS user_id, display_name AS user_name, user_email FROM {$this->pre}users WHERE ID IN (" . join(',',$wp_user_ids) . ")";
				$result = $this->db->get_results($sql,ARRAY_A);
				$mail_users = array_merge($mail_users,$result);
			}
			if(count($mail_user_ids) > 0){
				$sql = "SELECT user_id, user_name, user_email, user_group FROM {$this->pre}mailmaga_x_users WHERE user_id IN (" . join(',',$mail_user_ids) . ")";
				$result = $this->db->get_results($sql,ARRAY_A);
				$mail_users = array_merge($mail_users,$result);
			}

			$mail_json['send_to'] = $mail_users;
			$mail_json['mail_servers'] = $paramers['mail_servers'];

			//Create mail_id
			if(empty($paramers['mail_id']) || !is_numeric($paramers['mail_id'])){
				$mails_data = array();
				$sql = "SELECT IFNULL(MAX(mail_id),0)+1 AS mail_id FROM {$this->pre}mailmaga_x_mails";
				$max_mail = $this->db->get_row($sql,ARRAY_A);
				$mails_data['mail_id'] = $max_mail['mail_id'];
				$mails_data['title'] = urldecode($paramers['title']);
				$mails_data['content'] = urldecode($paramers['content']);
				$mails_data['user_ids'] = join(',',$paramers['user_ids']);
				$mails_data['server_ids'] = join(',',$paramers['mail_servers']);
				$mails_data['created_time'] = current_time('mysql');
				$this->db->insert($this->pre.'mailmaga_x_mails', $mails_data);
				$mail_json['mail_id'] = $mails_data['mail_id'];
			}else{
				//Retry sending mail handle
				$mail_json['mail_id'] = $paramers['mail_id'];
				//Delete failed history data
				$this->db->delete($this->pre.'mailmaga_x_historys', array('mail_id' => $paramers['mail_id'], 'success' => '0'));
			}
			return $mail_json;
		}
		/**
		 * Get mail sending process data
		 * @param $mail_id
		 */
		function get_process_json($mail_id){
			$process = array();

			$mail_info = $this->get_mail_info($mail_id);
			$process['total'] = (count($mail_info['user_ids'])) ? count($mail_info['user_ids']) : 0;
			$process['sent'] = (count($mail_info['sent_user_ids'])) ? count($mail_info['sent_user_ids']) : 0;
			$process['unsent'] = (count($mail_info['unsent_user_ids'])) ? count($mail_info['unsent_user_ids']) : 0;
			return $process;
		}
		/**
		 * Send mail in backend
		 * @param $mail_id
		 * TODO Server looping improve
		 */
		function cron_send_mail($mail_id){
			$pre = (empty($this->pre)) ? 'wp_' : $this->pre;

			$mail_info = $this->get_mail_info($mail_id);
			$json['mail'] = $mail_info;
			$skey = 0;
			foreach($mail_info['unsent_user_ids'] as $k => $user_id){
				if(stristr($user_id,$pre) !== false){
					$sql = "SELECT CONCAT('{$pre}',ID) AS user_id, display_name AS user_name, user_email FROM {$this->pre}users WHERE ID = '".str_replace($pre,'',$user_id)."'";
				}elseif(is_numeric($user_id)){
					$sql = "SELECT user_id,user_name,user_email FROM {$this->pre}mailmaga_x_users WHERE user_id = '".$user_id."'";
				}
				$paramers = $this->db->get_row($sql,ARRAY_A);
				if(empty($paramers)) continue;
				//TODO when user has been deleted
				$paramers['mail_id'] = $mail_id;
				if(($skey + 1) > count($mail_info['server_ids'])){
					$skey = 0;
				}
				$paramers['server_id'] = $mail_info['server_ids'][$skey];
				$skey++;
				$this->send_mail($paramers);
			}
		}
		/**
		 * Get mail info from mail_id which includes unsent users and sent users
		 * @param $mail_id
		 */
		function get_mail_info($mail_id ){
			$sql = "SELECT mail_id,user_ids,server_ids FROM {$this->pre}mailmaga_x_mails WHERE mail_id = '".$mail_id."'";
			$mail_info = $this->db->get_row($sql,ARRAY_A);
			$mail_user_ids = explode(',',$mail_info['user_ids']);

			$sql = "SELECT user_id FROM {$this->pre}mailmaga_x_historys WHERE mail_id = '".$mail_id."'";
			$historys = $this->db->get_results($sql,ARRAY_A);
			$user_ids = array();
			$sent_users = array();
			if(!empty($historys)){
				foreach ($historys as $h){
					$sent_users[] = $h['user_id'];
				}
			}
			foreach ($mail_user_ids as $u){
				if(!in_array($u,$sent_users)){
					//Create unsent user array
					$user_ids[] = $u;
				}
			}
				
			$mail_info['server_ids'] = explode(',',$mail_info['server_ids']);
			$mail_info['user_ids'] = $mail_user_ids;
			$mail_info['sent_user_ids'] = $sent_users;
			$mail_info['unsent_user_ids'] = $user_ids;
			return $mail_info;
		}
		/**
		 * Send Mail to single user
		 * @param $paramers
		 * @param $is_test , Send test mail when $is_test = true
		 */
		function send_mail($paramers , $is_test = false){
			$json = array();
			if((empty($paramers['mail_id']) || !is_numeric($paramers['mail_id'])) && $is_test == false ){
				$json['error'][]  = __('mail_id cannot be empty.',MAILMAGA_NAME);
			}
			if(empty($paramers['server_id']) || !is_numeric($paramers['server_id'])){
				$json['error'][]  = __('server_id cannot be empty.',MAILMAGA_NAME);
			}
			if(!empty($json['error'])){
				$json['result'] = false;
				return $json;
			}
			if($is_test == true){
				$sql = "SELECT '" . __('Test mail from Mailmaga-X') . "' AS title";
				$sql .= ",'" . __('The test mail content sent from Mailmaga-X plugin.') . "' AS content";
				$sql .= ',S.server_name,S.type,S.host,S.auth,IFNULL(S.sleep,0) AS sleep,
							S.port,S.account,S.password 
					FROM '.$this->pre.'mailmaga_x_servers S
					WHERE S.server_id = ' . $paramers['server_id'] . '
						LIMIT 1';
			}else{
				$sql = 'SELECT M.title,M.content,
							S.server_name,S.type,S.host,S.auth,IFNULL(S.sleep,0) AS sleep,
							S.port,S.account,S.password 
					FROM '.$this->pre.'mailmaga_x_mails M,'.$this->pre.'mailmaga_x_servers S
					WHERE M.mail_id = ' . $paramers['mail_id'] . ' AND S.server_id = ' . $paramers['server_id'] . '
						LIMIT 1';
			}
			$mail_info = $this->db->get_row($sql,ARRAY_A);
			if(empty($mail_info)){
				$json['error'][] = __('Could not get server data.');
				$json['result'] = false;
				return $json;
			}
			if($mail_info['type'] == 'smtp'){
				if($mail_info['auth'] == 1) $this->SMTPAuth = true;
				$this->Port = (!empty($mail_info['port'])) ? $mail_info['port'] : 25;
				$this->Host = $mail_info['host'];
				$this->Username = $mail_info['account'];
				$this->Password = $this->authcode($mail_info['password']);

				add_action('phpmailer_init',array(&$this, 'set_smtp_server'));
			}
			$from_name = get_option('blogname');
			$from_mail = get_option('admin_email');
			if(empty($from_mail) && !empty($mail_info['account'])){
				$from_mail = $mail_info['account'];
			}
			$to = $paramers['user_email'];
			if($this->is_html($mail_info['content'])){
				//HTML mail handle
				$is_html = true;
				$headers[] = 'Content-type: text/html';
				$secret_key = md5(date(time()) . wp_rand(10000,99999));
				$img_record = '<img src="' . get_bloginfo('wpurl') . '/?act=xrc&m='.$paramers['mail_id'].'&u='.$paramers['user_id'].'&key='.$secret_key.'" width="1" />';
				$message = str_replace('</body>',$img_record.'</body>',$mail_info['content']);
			}else{
				$is_html = false;
				$message = $mail_info['content'];
			}
			$headers[] = "From: {$from_name} <{$from_mail}>" . "\r\n";
			$subject = $mail_info['title'];

			//insert secret_key

			$json['result'] = wp_mail($to, $subject, $message,$headers);
			if($json['result'] == false){
				//$json['debug'] = $mail_info;
				$json['error'][] = '[' . $mail_info['server_name'] . ']' . $this->phpmailer->ErrorInfo;
			}
			//Return when is test mail
			if($is_test == true) return $json;

			$sql = "SELECT history_id FROM {$this->pre}mailmaga_x_historys WHERE mail_id = '".$paramers['mail_id']."' AND user_id = '".$paramers['user_id']."'";
			$history_id = $this->db->get_var($sql);
			$history = array();
			$history['secret_key'] = $secret_key;
			if($json['result'] == false){
				$history['error'] = '[' . $mail_info['server_name'] . ']' . $this->phpmailer->ErrorInfo;
			}else{
				$history['error'] = null;
			}
			$history['success'] = $json['result'];
			$history['is_html'] = $is_html;
			$history['created_time'] = current_time('mysql');
			if(empty($history_id)){
				//Save to history
				$history['mail_id'] = $paramers['mail_id'];
				$history['user_id'] = $paramers['user_id'];
				$this->db->insert($this->pre.'mailmaga_x_historys', $history,array('%s','%s','%s','%s','%s','%d','%s'));
			}else{
				//Update history
				$this->db->update($this->pre.'mailmaga_x_historys', $history, array('mail_id' => $paramers['mail_id'],'user_id' => $paramers['user_id']));
			}

			if($mail_info['sleep'] > 0) sleep($mail_info['sleep']);
			return $json;
		}
		/**
		 * Hook action
		 * @param $phpmailer
		 */
		function set_smtp_server($phpmailer){
			$phpmailer->IsSMTP();
			$phpmailer->SMTPAuth = $this->SMTPAuth;
			$phpmailer->Port = $this->Port;
			$phpmailer->Host = $this->Host;
			$phpmailer->Username = $this->Username;
			$phpmailer->Password = $this->Password;
			$this->phpmailer = $phpmailer;
		}

		/**
		 * Record when user is opening mail
		 * TODO 改善
		 */
		function mail_record(){
			$pre = (empty($this->pre)) ? 'wp_' : $this->pre;
			$paramers = $this->get_paramers($_GET , array('act','m','u','key'));
			if(!empty($paramers['u'])){
				$user_id = str_replace($pre,'',$paramers['u']);
			}
			if($paramers['act'] !== 'xrc' || empty($paramers['m']) || !is_numeric($paramers['m']) || $paramers['m'] <= 0
			|| empty($user_id) || !is_numeric($user_id) || $user_id <= 0
			|| empty($paramers['key']) || strlen($paramers['key']) < 30
			){
				return;
			}
			$sql = "SELECT history_id,secret_key FROM {$this->pre}mailmaga_x_historys WHERE opened_time IS NULL AND mail_id = '" . $paramers['m'] . "' AND user_id = '" . $paramers['u'] . "'";
			$history = $this->db->get_row($sql,ARRAY_A);
			if($paramers['key'] !== $history['secret_key'] || empty($history['secret_key'])){
				$this->show_px_png();
			}
			$update = array();
			$update['opened_time'] = current_time('mysql');
			$this->db->update($this->pre.'mailmaga_x_historys',
			$update,
			array('history_id' => $history['history_id']));
			$this->show_px_png();
		}
		/**
		 * Load and show 1px png file
		 */
		function show_px_png(){
			$png_file = PLUGIN_MC_PATH . 'images/record.png';
			if(!file_exists($png_file)){
				return;
			}
			Header("Content-type: image/png");
			header('Content-Length: ' . filesize($png_file));
			readfile($png_file);
			exit;
		}
		/**
		 * Check content has html tag
		 * @param $string
		 */
		function is_html($string){
			return preg_match("/<[^<]+>/",$string,$m) != 0;
		}
	}
}
?>
