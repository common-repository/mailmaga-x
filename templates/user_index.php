<?php 
	wp_enqueue_script('plupload-handlers');
	$this->insert_wp_script();
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	csvUploader();
});
function csvUploader(){
	var uploader = new plupload.Uploader({
        runtimes : 'gears,html5,flash,silverlight,browserplus',
        browse_button : 'uploadfiles',
        container : 'search_form',
        max_file_size : '2mb',
        url : '<?php echo admin_url('admin-ajax.php'); ?>?action=mailmaga_x_import_csv',
        flash_swf_url : '<?php echo includes_url('js/plupload/plupload.flash.swf'); ?>',
        silverlight_xap_url : '<?php echo includes_url('js/plupload/plupload.silverlight.xap'); ?>',
        filters : [
            {title : "Files of type", extensions : "csv"}
        ]
    });
 
    uploader.bind('Init', function(up, params) {
        console.log(params);
    });
 	uploader.init();
    uploader.bind('FilesAdded', function(up, files) {
    	jQuery('#uploadfiles').siblings('.spinner').css('display','inline');
        uploader.start();
         //e.preventDefault();
    });
    	    
    uploader.bind('FileUploaded', function(up, file , object) {
    	var data;
        try {
        	data = eval(object.response);
        } catch(err) {
        	data = eval('(' + object.response + ')');
        }
    	if(data['result'] == true){
    		location.href = 'admin.php?page=mailmaga_x_page_users&message=1&user_group='+encodeURIComponent(data['user_group']);
        }else{
        	var error = '';
			for(var i in data['error']){
				error += data['error'][i] + '\n';
			}
			alert(error);
			jQuery('#uploadfiles').siblings('.spinner').hide();
        }
    });
    		
}
</script>
<div class="wrap">
	<div id="icon-users" class="icon32"><br></div>
	<h2><?php echo __('Users',MAILMAGA_NAME);?> 
	<a href="user-new.php" class="add-new-h2"><?php echo __( 'New', MAILMAGA_NAME ); ?></a>
	</h2>
	<?php echo $this->message; ?>
	<form action="" method="get" id="search_form">
		<input type="hidden" name="action" value="" /> 
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="user_group">
					<?php foreach($this->group_list as $k => $g){
						$selectd = '';
						if($_GET['user_group'] == $k){
							$selectd = ' selected="selected"';
						}
						echo '<option value="'.$k.'"'.$selectd.'>'.$g.'</option>';
					} ?>
				</select>
				<input type="hidden" name="page" value="mailmaga_x_page_users" />&nbsp;&nbsp;
				<input type="button" name="" onclick="searchAction()" class="button" value="<?php echo __('Search');?>">
			</div>
			<div class="alignright actions">
				<input type="button" name="" onclick="exportAction()" class="button fr" value="<?php echo __('Export');?>">
				<input type="button" name="" id="uploadfiles" class="button fr" value="<?php echo __('Import');?>" style="margin-right:10px;">
				<span class="spinner" style="display:none;"></span>
			</div>
			<br class="clear">
		</div>
	</form>
	<form action="" method="post" id="list_form">
	<input type="hidden" name="action" value="mail" /> 
	<table class="wp-list-table widefat fixed users" cellspacing="0">
		<thead>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
				<label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select all',MAILMAGA_NAME); ?></label>
				<input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="username" class="manage-column column-username style="">
				<?php echo __('Name',MAILMAGA_NAME); ?>
			</th>
			<th scope="col" id="email" class="manage-column column-email" style="">
				<?php echo __('Email',MAILMAGA_NAME); ?></th>
			<th nowrap="nowrap" scope="col" id="opened" class="manage-column column-opened" style="">
				<span><?php echo __('Opened (only HTML mail)',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span></th>
			<th scope="col" id="role" class="manage-column column-role" style=""><?php echo __('Group',MAILMAGA_NAME); ?></th>
			</tr>
		</thead>
	
		<tfoot>
		<tr>
			<th scope="col" class="manage-column column-cb check-column" style="">
				<label class="screen-reader-text" for="cb-select-all-2"><?php echo __('Select all',MAILMAGA_NAME); ?></label>
				<input id="cb-select-all-2" type="checkbox"></th><th scope="col" id="username" class="manage-column column-username" style="">
				<?php echo __('Name',MAILMAGA_NAME); ?>
			</th>
			<th scope="col" class="manage-column column-email" style="">
				<?php echo __('Email',MAILMAGA_NAME); ?>
			</th>
			<th scope="col" id="opened" class="manage-column column-opened" style="">
				<span><?php echo __('Opened (only HTML mail)',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span></th>
			<th scope="col" class="manage-column column-role" style=""><?php echo __('Group',MAILMAGA_NAME); ?></th>
		</tr>
		</tfoot>
	
		<tbody id="the-list" data-wp-lists="list:user">
		<?php foreach($this->user_list as $k => $g){ ?>
			<tr id="user-<?php echo $k; ?>">
				<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-<?php echo $k; ?>"><?php echo __('Select',MAILMAGA_NAME); ?> <?php echo $g['user_name']; ?></label>
					<input type="checkbox" name="user_ids[]" id="user_<?php echo $k; ?>" class="subscriber" value="<?php echo $g['user_id']; ?>"></th>
				<td class="name column-name"><?php echo $g['user_name']; ?></td>
				<td class="email column-email"><a href="mailto:<?php echo $g['user_email']; ?>"><?php echo $g['user_email']; ?></a></td>
				<td class="role column-role"><?php echo $g['opened_rate']; ?>%</td>
				<td class="role column-role"><?php echo $g['user_group']; ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	</form>
	
	<div class="tablenav bottom">
		<div class="alignleft actions">
			<input type="button" name="" onclick="deleteAction()" id="delete" class="button action" value="<?php echo __('Delete'); ?>">&nbsp;&nbsp;
			<input type="button" name="" onclick="sendAction()" id="send" class="button button-primary button-large" value="<?php echo __('Send mail',MAILMAGA_NAME); ?>">
		</div>
		<br class="clear">
	</div>
</div>