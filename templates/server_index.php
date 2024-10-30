<?php 
	$this->insert_wp_script();
?>
<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2><?php echo __('Servers',MAILMAGA_NAME);?> 
	<a href="admin.php?page=mailmaga_x_page_servers&action=new" class="add-new-h2"><?php echo __( 'New', MAILMAGA_NAME ); ?></a>
	</h2><br />
	<?php echo $this->message; ?>
	<form action="" method="post" id="list_form">
	<input type="hidden" name="action" value="mail" /> 
	<table class="wp-list-table widefat fixed users" cellspacing="0">
		<thead>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
				<label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select all',MAILMAGA_NAME); ?></label>
				<input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="servername" class="manage-column column-servername">
				<span><?php echo __('Server name',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span>
			</th>
			<th scope="col" id="host" class="manage-column column-host" style="">
				<span><?php echo __('Host',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span></th>
			<th scope="col" id="role" class="manage-column column-role" style=""><?php echo __('Account',MAILMAGA_NAME); ?></th>
			</tr>
		</thead>
	
		<tfoot>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
				<label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select all',MAILMAGA_NAME); ?></label>
				<input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="servername" class="manage-column column-servername">
				<span><?php echo __('Server name',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span>
			</th>
			<th scope="col" id="host" class="manage-column column-host" style="">
				<span><?php echo __('Host',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span></th>
			<th scope="col" id="role" class="manage-column column-role" style=""><?php echo __('Account',MAILMAGA_NAME); ?></th>
			</tr>
		</tfoot>
	
		<tbody id="the-list" data-wp-lists="list:user">
		<?php foreach($this->user_list as $k => $g){ ?>
			<tr id="user-<?php echo $k; ?>">
				<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-<?php echo $k; ?>"><?php echo __('Select',MAILMAGA_NAME); ?> <?php echo $g['server_name']; ?></label>
					<input type="checkbox" name="server_ids[]" id="server_<?php echo $k; ?>" class="mail_server" value="<?php echo $g['server_id']; ?>"></th>
				<td class="name column-name"><?php echo $g['server_name']; ?>
				<div class="row-actions">
				<span class="trash"><a class="submitdelete" title="<?php echo __('Delete'); ?>" href="admin.php?page=mailmaga_x_page_servers&action=delete&server_id=<?php echo $g['server_id']; ?>"><?php echo __('Delete'); ?></a> | </span>
				<span class="edit"><a href="admin.php?page=mailmaga_x_page_servers&action=edit&server_id=<?php echo $g['server_id']; ?>" title="<?php echo __('Edit'); ?>"><?php echo __('Edit'); ?></a></span>
				</div>
				</td>
				<td class="host column-email"><?php echo $g['host']; ?></td>
				<td class="role column-role"><?php echo $g['account']; ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	</form>
	
	<div class="tablenav bottom">
		<div class="alignleft actions">
			<input type="button" name="" onclick="deleteAction()" id="delete" class="button action" value="<?php echo __('Delete'); ?>">&nbsp;&nbsp;
		</div>
		<br class="clear">
	</div>
</div>