<?php
/**
 * Class Mailmaga_x_server for servers page
 */
if (!class_exists("Mailmaga_x_server")) {
	class Mailmaga_x_server extends Mailmaga_x {

		private $server_list = array();

		function Mailmaga_x_server() { //constructor
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
					if(!empty($_POST['server_ids'])){
						$this->delete_server($_POST['server_ids']);
					}else{
						$this->param = $this->get_paramers($_GET , array('server_id'));
						$this->delete_server($this->param['server_id']);
					}
					$this->server_list();
					break;
				case 'edit':
				case 'new':
					$this->init_master_data();
					$paramers = $this->get_paramers($_POST , array('server_name','type','sleep','host','auth','port','account','password'));
					if($_POST['mode'] == 'update'){
						$error = $this->check_error($paramers);
						if(count($error) > 0){
							$this->message = $this->get_message(join('<br />',$error));
							$this->form = $paramers;
							$this->server_id = $_POST['server_id'];
							include_once PLUGIN_MC_PATH . 'templates/server_input.php';
							exit;
						}
						
						$this->server_update($paramers);
					}else{
						$this->server_input();
					}
					break;
				case 'send':
					break;
				default:
					if($_GET['message'] == '1'){
						$this->message = $this->get_message(__('Create server completed.',MAILMAGA_NAME));
					}elseif($_GET['message'] == '2'){
						$this->message = $this->get_message(__('Update server completed.',MAILMAGA_NAME));
					}elseif($_GET['message'] == '3'){
						$this->message = $this->get_message(__('Delete server completed.',MAILMAGA_NAME));
					}
					$this->server_list();
					break;
			}
		}
		/**
		 * Show user list page
		 */
		function server_list(){
			$sql = "SELECT server_id, server_name, type, host, account FROM {$this->pre}mailmaga_x_servers";
			$this->user_list = $this->db->get_results($sql,ARRAY_A);

			include_once PLUGIN_MC_PATH . 'templates/server_index.php';
		}
		function get_server_by_id($server_id){
			if(!is_numeric($server_id) || empty($server_id)) return array();
			$sql = "SELECT * FROM {$this->pre}mailmaga_x_servers WHERE server_id = '{$server_id}'";
			$server_info = $this->db->get_row($sql,ARRAY_A);
			return $server_info;
		}
		/**
		 * Delete the selected server
		 */
		function delete_server($server_id){
			if(is_array($server_id) && count($server_id) > 0){
				$server_ids = join(',',$server_id);
				$sql = "DELETE FROM {$this->pre}mailmaga_x_servers WHERE server_id IN ( " . $server_ids . " )";
				$this->db->query( $sql );
			}elseif(!empty($server_id) && is_numeric($server_id)){
				$this->db->delete($this->pre."mailmaga_x_servers" , array('server_id' => $server_id));	
			}
			$this->message = $this->get_message('Delete server completed.',MAILMAGA_NAME);
		}
		/**
		 * Show server input page
		 */
		function server_input() {
				
			$this->server_info = $this->get_server_by_id($_GET['server_id']);
			$this->server_info['password'] = $this->authcode($this->server_info['password']);
			if(!empty($this->server_info['server_id'])){
				$this->server_id = $this->server_info['server_id'];
				$this->form = $this->server_info;
			}

			include_once PLUGIN_MC_PATH . 'templates/server_input.php';
		}
		/**
		 * Update or new server info
		 */
		function server_update($paramers){
			
			$server_id = $_POST['server_id'];
			if($paramers['type'] == 'mail'){
				$paramers['host'] = 'localhost';
			}
			if(!empty($paramers['password'])){
				$paramers['password'] = $this->authcode($paramers['password'],'ENCODE');
			}
			
			if(!empty($server_id) && is_numeric($server_id)){
				//UPDATE
				$this->db->update($this->pre.'mailmaga_x_servers',
				$paramers,
				array('server_id' => $server_id));
				$location = 'admin.php?page=mailmaga_x_page_servers&message=2';
			}else{
				$this->db->insert($this->pre.'mailmaga_x_servers', $paramers);
				$location = 'admin.php?page=mailmaga_x_page_servers&message=1';
			}
			$this->redirect($location);
		}

		/**
		 * Master data
		 */
		function init_master_data(){
			$this->server_types = array('mail','smtp');
			$this->server_intervals = array('0','1','2','3','4','5','6','7','8','9','10');
		}
		/**
		 * Check server form input error
		 * @param $param
		 */
		function check_error($param){
			$errors = array();
			if(empty($param['server_name'])){
				$errors[] = __('Server name cannot be empty.',MAILMAGA_NAME);
			}
			if($param['type'] != 'mail'){
				if(empty($param['host'])){
					$errors[] = __('Server host cannot be empty.',MAILMAGA_NAME);
				}
				if(empty($param['port'])){
					$errors[] = __('Server port cannot be empty.',MAILMAGA_NAME);
				}elseif(!is_numeric($param['port'])){
					$errors[] = __('Server port must be number.',MAILMAGA_NAME);
				}
				if(empty($param['account'])){
					$errors[] = __('Server account cannot be empty.',MAILMAGA_NAME);
				}
				if(empty($param['password'])){
					$errors[] = __('Server password cannot be empty.',MAILMAGA_NAME);
				}
			}
			return  $errors;
		}
		
	}
}
?>
