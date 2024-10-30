<?php
/**
 * Class Mailmaga_x_history for history page
 */
if (!class_exists("Mailmaga_x_history")) {
	class Mailmaga_x_history extends Mailmaga_x {


		function Mailmaga_x_history() { //constructor
			parent::init();
			global $wpdb , $table_prefix;
			$this->db = $wpdb;
			$this->pre = $table_prefix;
		}

		/**
		 * Mailmaga X console page [HISTORYS]
		 */
		function index(){
			$action = $_POST['action'] ? $_POST['action'] : $_GET['action'];
			switch ($action){
				case 'delete':
					if(!empty($_POST['mail_ids'])){
						$paramers = $this->get_paramers($_POST , array('mail_ids'));
						$this->delete_history($paramers['mail_ids']);
					}else{
						$paramers = $this->get_paramers($_GET , array('mail_id'));
						$this->delete_history($paramers['mail_id']);
					}
					$location = 'admin.php?page=mailmaga_x_page_history&message=2';
					$this->redirect($location);
					break;
				default:
					$this->history_list();
					break;
			}
		}
		/**
		 * Delete mails and history data from mail_id
		 * @param $mail_id
		 */
		function delete_history($mail_id){
			if(is_array($mail_id) && count($mail_id) > 0){
				$mail_ids = join(',',$mail_id);
				$sql = "DELETE FROM {$this->pre}mailmaga_x_mails WHERE mail_id IN ( " . $mail_ids . " )";
				$this->db->query( $sql );
				$sql = "DELETE FROM {$this->pre}mailmaga_x_historys WHERE mail_id IN ( " . $mail_ids . " )";
				$this->db->query( $sql );
			}elseif(!empty($mail_id) && is_numeric($mail_id)){
				$this->db->delete($this->pre."mailmaga_x_mails" , array('mail_id' => $mail_id));
				$this->db->delete($this->pre."mailmaga_x_historys" , array('mail_id' => $mail_id));		
			}
		}
		/**
		 * Show history list page
		 */
		function history_list(){
			switch ($_GET['message']){
				case '1':
					$this->message = $this->get_message(__('Send mail completed.',MAILMAGA_NAME));
					break;
				case '2':
					$this->message = $this->get_message(__('Delete history completed.',MAILMAGA_NAME));
					break;
				default:
					break;
			}
			
			$sql = "SELECT M.mail_id,M.title,M.created_time,
						IFNULL(HS.success_count,0) AS success_count,IFNULL(HO.opened_count,0) AS opened_count,IFNULL(HE.faild_count,0) AS faild_count
						FROM {$this->pre}mailmaga_x_mails M
						LEFT JOIN
							(SELECT COUNT(history_id) AS success_count,mail_id FROM {$this->pre}mailmaga_x_historys 
							WHERE success = 1
							GROUP BY mail_id) HS
						ON M.mail_id = HS.mail_id
						LEFT JOIN
							(SELECT COUNT(history_id) AS opened_count,mail_id FROM {$this->pre}mailmaga_x_historys 
							WHERE success = 1 AND opened_time IS NOT NULL 
							GROUP BY mail_id) HO
						ON M.mail_id = HO.mail_id
						LEFT JOIN
							(SELECT COUNT(history_id) AS faild_count,mail_id FROM {$this->pre}mailmaga_x_historys 
							WHERE success <> 1
							GROUP BY mail_id) HE
						ON M.mail_id = HE.mail_id ORDER BY M.created_time DESC,M.mail_id DESC";
			
			$this->history_list = $this->db->get_results($sql,ARRAY_A);

			include_once PLUGIN_MC_PATH . 'templates/history_index.php';
		}
	}
}
?>
