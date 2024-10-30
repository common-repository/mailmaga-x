<?php 
	$this->insert_wp_script();
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		changeType(jQuery('#server-form #type'));
	});
</script>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2><?php 
	if(empty($this->server_id)){
		echo __('New server',MAILMAGA_NAME);
	}else{
		echo $this->form['server_name'];
	}?> 
	</h2>
	<?php echo $this->message; ?>
	<div id="poststuff">
		<form action="" method="post" id="server-form">
		<input type="hidden" name="mode" value="update" />
		<input type="hidden" name="server_id" value="<?php echo $this->server_id;?>" /> 
			<table class="form-table">
			<tbody>
				<tr class="form-field form-required">
					<th scope="row" valign="top"><label for="server_name"><?php echo __('Server name',MAILMAGA_NAME); ?></label></th>
					<td><input name="server_name" id="server_name" type="text" value="<?php echo $this->form['server_name']; ?>" size="20" aria-required="true">
					<p class="description"></p></td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="parent"><?php echo __('Type',MAILMAGA_NAME); ?></label></th>
					<td>
						<select name="type" id="type" class="postform" onchange="changeType(jQuery(this))">
							<?php
								foreach($this->server_types as $type){
									$selected = '';
									if($this->form['type'] == $type){
										$selected = ' selected';
									}
									echo '<option value = "' . $type . '"' . $selected . '>' . $type . '</OPTION>';
								}
							?>
						</select>
						<!-- @TODO TEXT -->
						<!--  <p class="description"><?php echo __('When you selected smtp server,you should input account and password for connecting smtp server.'); ?></p> -->
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="parent"><?php echo __('Interval',MAILMAGA_NAME); ?></label></th>
					<td>
						<select name="sleep" id="sleep" class="postform">
							<?php
								foreach($this->server_intervals as $inter){
									$selected = '';
									if($this->form['sleep'] == $inter){
										$selected = ' selected';
									}
									echo '<option value = "' . $inter . '"' . $selected . '>' . $inter . __('s') .  '</OPTION>';
								}
							?>
						</select>
						<!-- @TODO TEXT -->
						<!--  <p class="description"><?php echo __('Interval description',MAILMAGA_NAME); ?></p> -->
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="host"><?php echo __('Host',MAILMAGA_NAME); ?></label></th>
					<td><input name="host" id="host" type="text" value="<?php echo $this->form['host']; ?>" size="20">
					<!-- @TODO TEXT -->
					<p class="description"></p></td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="auth"><?php echo __('Auth',MAILMAGA_NAME); ?></label></th>
					<td>
					<input id="auth" name="auth" type="checkbox" value="1" <?php if($this->form['auth'] == '1') echo 'checked' ?> style="width:auto;" />
					<label class="label-text" for="auth">&nbsp;<?php echo __('Use SMTP Auth',MAILMAGA_NAME); ?></label>
					<!-- @TODO TEXT -->
					<p class="description"></p></td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="port"><?php echo __('Port',MAILMAGA_NAME); ?></label></th>
					<td><input name="port" id="port" type="text" value="<?php echo $this->form['port']; ?>" size="20">
					<!-- @TODO TEXT -->
					<p class="description"></p></td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="account"><?php echo __('Account',MAILMAGA_NAME); ?></label></th>
					<td><input name="account" id="account" type="text" value="<?php echo $this->form['account']; ?>" size="20">
					<p class="description"></p></td>
				</tr>
				<tr class="form-field">
					<th scope="row" valign="top"><label for="password"><?php echo __('Password',MAILMAGA_NAME); ?></label></th>
					<td><input name="password" id="password" type="password" value="<?php echo $this->form['password']; ?>" size="20">
					<!-- @TODO TEXT -->
					<p class="description"></p></td>
				</tr>
			</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save'); ?>"></p>
		</form>	
	</div>	
</div>