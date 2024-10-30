<?php 
	$this->insert_wp_script();
?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br></div>
	<h2><?php echo __('History',MAILMAGA_NAME);?></h2><br />
	<?php echo $this->message; ?>
	<form action="" method="post" id="list_form">
	<input type="hidden" name="action" value="" /> 
	<table class="wp-list-table widefat fixed users" cellspacing="0">
		<thead>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
				<label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select all',MAILMAGA_NAME); ?></label>
				<input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="mail-id" class="manage-column column-mail-id">
				<span><?php echo __('ID'); ?></span>
			</th>
			<th scope="col" id="role" class="manage-column column-mail-title" style="width:30%;"><?php echo __('Mail subject',MAILMAGA_NAME); ?></th>
			<th nowrap="nowrap" scope="col" id="host" class="manage-column column-success" style="">
				<span><?php echo __('Success count',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span></th>
				<th scope="col" id="role" class="manage-column column-open" style=""><?php echo __('Opened count (only HTML mail)',MAILMAGA_NAME); ?></th>
			<th scope="col" id="role" class="manage-column column-faild" style=""><?php echo __('Faild count',MAILMAGA_NAME); ?></th>
			<th scope="col" id="role" class="manage-column column-time" style=""><?php echo __('Sent time',MAILMAGA_NAME); ?></th>
			</tr>
		</thead>
	
		<tfoot>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
				<label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select all',MAILMAGA_NAME); ?></label>
				<input id="cb-select-all-1" type="checkbox"></th><th scope="col" id="mail-id" class="manage-column column-mail-id">
				<span><?php echo __('ID'); ?></span><span class="sorting-indicator"></span>
			</th>
			<th scope="col" id="role" class="manage-column column-mail-title" style=""><?php echo __('Mail subject',MAILMAGA_NAME); ?></th>
			<th scope="col" id="host" class="manage-column column-success" style="">
				<span><?php echo __('Success count',MAILMAGA_NAME); ?></span><span class="sorting-indicator"></span></th>
			<th nowrap="nowrap" scope="col" id="role" class="manage-column column-open" style=""><?php echo __('Opened count (only HTML mail)',MAILMAGA_NAME); ?></th>
			<th scope="col" id="role" class="manage-column column-faild" style=""><?php echo __('Faild count',MAILMAGA_NAME); ?></th>
			<th scope="col" id="role" class="manage-column column-time" style=""><?php echo __('Sent time',MAILMAGA_NAME); ?></th>
			</tr>
		</tfoot>
	
		<tbody id="the-list" data-wp-lists="list:hitory">
		<?php foreach($this->history_list as $k => $h){ ?>
			<tr id="user-<?php echo $h['mail_id']; ?>">
				<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-<?php echo $k; ?>"><?php echo __('Select',MAILMAGA_NAME); ?> <?php echo $h['title']; ?></label>
					<input type="checkbox" name="mail_ids[]" id="history_<?php echo $h['mail_id']; ?>" class="subscriber" value="<?php echo $h['mail_id']; ?>"></th>
				<td class="host column-mail-id"><?php echo $h['mail_id']; ?></td>
				<td class="name column-name"><?php echo $h['title']; ?>
				<div class="row-actions">
				<span class="trash"><a class="submitdelete" title="<?php echo __('Delete'); ?>" href="admin.php?page=mailmaga_x_page_history&action=delete&mail_id=<?php echo $h['mail_id']; ?>"><?php echo __('Delete'); ?></a> | </span>
				<span class="send"><a title="<?php echo __('Preview'); ?>" target="_blank" href="admin-ajax.php?action=mailmaga_x_send_mail&mode=preview&mail_id=<?php echo $h['mail_id']; ?>"><?php echo __('Preview'); ?></a></span>
				<?php if($h['faild_count'] == 0){ ?>
				<span class="send"> | <a href="admin.php?page=mailmaga_x_page_users&action=resend&mail_id=<?php echo $h['mail_id']; ?>" title="<?php echo __('Resend',MAILMAGA_NAME); ?>"><?php echo __('Resend',MAILMAGA_NAME); ?></a></span>
				<?php }?>
				<?php if($h['faild_count'] > 0){ ?>
				<span class="send"> | <a href="admin.php?page=mailmaga_x_page_users&action=retry&mail_id=<?php echo $h['mail_id']; ?>" title="<?php echo __('Retry',MAILMAGA_NAME); ?>"><?php echo __('Retry',MAILMAGA_NAME); ?></a></span>
				<?php }?>
				</div>
				</td>
				<td class="host column-success"><?php echo $h['success_count']; ?></td>
				<td class="host column-opened"><?php echo $h['opened_count']; ?></td>
				<td class="role column-faild"><?php echo $h['faild_count']; ?></td>
				<td class="role column-time"><?php echo $h['created_time']; ?></td>
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