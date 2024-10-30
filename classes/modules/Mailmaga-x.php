<?php
/**
 * Class Mailmaga_x (Main class)
 */
if (!class_exists("Mailmaga_x")) {
	class Mailmaga_x {
		public  $param;
		private $db;
		private $pre;
		private $message = '';

		function init() { //constructor
			
		}

		/**
		 * Class init function
		 */
		function mailmaga_x_init(){
			//$this->upgrade_database();
			load_plugin_textdomain(MAILMAGA_NAME,false,MAILMAGA_NAME.'/languages/');
		}
		function insert_wp_script(){
			if (function_exists('wp_enqueue_script')) {
				wp_enqueue_script('mailmaga_x_function', get_bloginfo('wpurl') . '/wp-content/plugins/mailmaga-x/js/functions.js', array('jquery'), '1.0');
			}
			echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/mailmaga-x/css/style.css" />';
		}
		/**
		 * Output menu content
		 */
		function mailmaga_x_admin_menu(){
			add_menu_page(__('Mailmaga-X'),__('Mailmaga-X',MAILMAGA_NAME),8,__FILE__,'mailmaga_x_admin');
			add_submenu_page(__FILE__,__('Users',MAILMAGA_NAME),__('Users',MAILMAGA_NAME),8,'mailmaga_x_page_users','mailmaga_x_page_users');
			add_submenu_page(__FILE__,__('Servers',MAILMAGA_NAME),__('Servers',MAILMAGA_NAME),8,'mailmaga_x_page_servers','mailmaga_x_page_servers');
			add_submenu_page(__FILE__,__('History',MAILMAGA_NAME),__('History',MAILMAGA_NAME),8,'mailmaga_x_page_history','mailmaga_x_page_history');
		}
		/**
		 * Redirect function(wp_redirect is not working)
		 * @param $location
		 */
		function redirect($location){
			echo "<meta http-equiv='refresh' content='0;url=$location' />";
			echo "loading...";
			exit;
		}
		/**
		 * Mailmaga X console index page
		 */
		function index(){
			include_once PLUGIN_MC_PATH . 'templates/index.php';
		}
		/**
		 * Format massage html for page
		 * @param $massage
		 */
		function get_message($massage){
			return '<div id="message" class="updated"><p>'.$massage.'</p></div>';
		}
		/**
		 *
		 * @param $from
		 * @param $keys
		 */
		function get_paramers($from, $keys = array()){
			$paramers = array();
			if(count($keys) > 0){
				foreach ($keys as $key){
					$paramers[$key] = $from[$key];
				}
			}else{
				foreach ($from as $key => $val){
					$paramers[$key] = $from[$key];
				}
			}
			return $paramers;
		}
		/**
		 * Encode / Decode password string
		 * @param string $string
		 * @param string $operation
		 * @param string $key
		 * @param int $expiry
		 */
		function authcode($string, $operation = 'DECODE', $key = AUTH_KEY, $expiry = 0)
		{
			$ckey_length = 4;
			// 随机密钥长度 取值 0-32;  
			// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。  
			// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方  
			// 当此值为 0 时，则不产生随机密钥  

			$key = md5($key ? $key : 'KEY');
			$keya = md5(substr($key, 0, 16));
			$keyb = md5(substr($key, 16, 16));
			$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

			$cryptkey = $keya.md5($keya.$keyc);
			$key_length = strlen($cryptkey);

			$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
			$string_length = strlen($string);

			$result = '';
			$box = range(0, 255);

			$rndkey = array();
			for($i = 0; $i <= 255; $i++)
			{
				$rndkey[$i] = ord($cryptkey[$i % $key_length]);
			}

			for($j = $i = 0; $i < 256; $i++)
			{
				$j = ($j + $box[$i] + $rndkey[$i]) % 256;
				$tmp = $box[$i];
				$box[$i] = $box[$j];
				$box[$j] = $tmp;
			}

			for($a = $j = $i = 0; $i < $string_length; $i++)
			{
				$a = ($a + 1) % 256;
				$j = ($j + $box[$a]) % 256;
				$tmp = $box[$a];
				$box[$a] = $box[$j];
				$box[$j] = $tmp;
				$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
			}

			if($operation == 'DECODE')
			{
				if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
				{
					return substr($result, 26);
				}
				else
				{
					return '';
				}
			}
			else
			{
				return $keyc.str_replace('=', '', base64_encode($result));
			}
		}
	}
}
require_once 'Mailmaga-x-install.php';
require_once 'Mailmaga-x-user.php';
require_once 'Mailmaga-x-server.php';
require_once 'Mailmaga-x-history.php';
require_once 'Mailmaga-x-mailer.php';
require_once 'Mailmaga-x-csv.php';
?>
