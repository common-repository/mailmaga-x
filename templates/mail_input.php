<?php 
	$this->insert_wp_script();
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	initPromptText();
});
</script>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2><?php echo __('New mailmagazine',MAILMAGA_NAME);?> 
	</h2>
	<?php echo $this->message; ?>
	<div id="poststuff">
		<form action="admin-ajax.php" method="post" id="mail-form">
		<input type="hidden" name="mode" value="" /> 
		<input type="hidden" name="mail_id" value="<?php echo $this->mail_id; ?>" />
		<input type="hidden" name="action" value="mailmaga_x_send_mail" /> 
		<?php foreach($this->param['user_ids'] as $k => $u){ ?>
			<input type="hidden" name="user_ids[]" value="<?php echo $u;?>" /> 
		<?php } ?>
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="titlediv">
					<div id="titlewrap">
						<label class="prompt-text" id="title-prompt-text" for="title"><?php echo __('Mail subject',MAILMAGA_NAME) ?></label>
						<input type="text" name="title" size="30" value="<?php echo $this->param['title']; ?>" id="title" class="input-text">
					</div>
				</div><!-- /titlediv -->
				<div id="postdivrich" class="postarea">
					<div id="wp-content-editor-container" class="wp-editor-container">
						<textarea class="wp-editor-area" style="height: 360px; width:100%; resize: none;" cols="40" name="content" id="content"><?php echo $this->param['content']; ?></textarea>
					</div>
				</div>
			</div><!-- /post-body-content -->	
			<div id="postbox-container-1" class="postbox-container">
				<div id="submitdiv" class="postbox ">
					<h3 class="hndle"><span><?php echo __('Send mail',MAILMAGA_NAME) ?></span></h3>
					<div class="inside">
						<div id="minor-publishing-actions" style="border-bottom: 1px solid #dfdfdf; padding-bottom:10px;">
							<div id="preview-action">
							<a class="preview button" href="javascript:preveiwAction();" target="wp-preview"><?php echo __('Preview');?></a>
							</div>
							<div class="clear"></div>
						</div>
						<div class="misc-pub-section">
							<input type="text" id="test-email" name="test_mail" class="newtag form-input-tip" size="18" value="" />
							<input type="button" class="button fr" onclick="startSendTestMail(this)" value="<?php echo __('Test',MAILMAGA_NAME);?>" />
							<span class="spinner" style="display:none;"></span>
						</div>
						<div id="minor-publishing">
							<div id="minor-publishing-actions" style="border-top: 1px solid #ffffff; padding:10px 8px;">
								<div id="send-action">
									<span class="process" style="display:none;"><?php echo __('Processing',MAILMAGA_NAME);?></span>
									<input name="send" type="button" onclick="startSendMail(this)" class="button button-primary button-large" value="<?php echo __('Send',MAILMAGA_NAME);?>" style="float:right;" />
									<input name="background" type="button" onclick="runInbackground()" class="button button-primary button-large" value="<?php echo __('Run in background',MAILMAGA_NAME);?>" style="float:right;padding-left:5px;padding-right:5px;display:none;" />
									<span class="spinner" style="display:none;"></span>
								</div>
							<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<!-- 账户 -->	
				<div id="submitdiv" class="postbox ">
					<h3 class="hndle"><span><?php echo __('Send from',MAILMAGA_NAME) ?></span></h3>
					<div class="inside">
						<div id="minor-publishing-actions" style="padding-bottom:10px;float:none; text-align: left;">
							<label for="send_account"><?php echo __('Email',MAILMAGA_NAME) ?>：</label>
							<span id="send_account"><?php echo $this->mail_sender;?></span>
							&nbsp;&nbsp;<a href="options-general.php" target="_blank" class="edit-post-status"><?php echo __('Edit');?></a>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<!-- 邮件服务器 -->	
				<div id="submitdiv" class="postbox ">
					<h3 class="hndle"><span><?php echo __('Mail server',MAILMAGA_NAME) ?></span></h3>
					<div class="inside">
						<div id="post-formats-select" style="line-height:20px;padding:10px;border-bottom: 1px solid #dfdfdf; ">
						<?php 
						foreach($this->server_list as $k => $s){ 
							$check = '';
							if(empty($this->param['server_ids'])){
								$check = 'checked="checked"';
							}elseif(in_array($s['server_id'],$this->param['server_ids'])){
								$check = 'checked="checked"';
							}
							
							?>
							<input type="checkbox" name="mail_servers[]" id="mail-server-<?php echo $k;?>" class="mail_servers" value="<?php echo $s['server_id'];?>" <?php echo $check; ?> />&nbsp;&nbsp;
								<label for="mail-server-<?php echo $k;?>"><?php echo $s['server_name'];?></label><br>
						<?php } ?>
							
						</div>
						<div id="minor-publishing-actions" style="border-top: 1px solid #ffffff; padding-bottom:10px;">
								<div id="preview-action">
								<a class="preview button" href="admin.php?page=mailmaga_x_page_servers" target="wp-preview"><?php echo __('Edit');?></a>
								</div>
								<div class="clear"></div>
							</div>
					</div>
				</div>
			</div><!-- /postbox-container-1 -->	
		</div><!-- /post-body -->	
		</form>	
	</div>	
</div>