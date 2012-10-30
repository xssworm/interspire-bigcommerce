<div class="wrap">
	<div id="icon-plugins" class="icon32"></div>

	<h2>Bigcommerce for WordPress</h2>

	<?php self::show_configuration_check(); ?>
	
	<hr />

	<?php if( self::$configured ) { ?>

	<p>
		<?php
		echo ! get_option('wpinterspire_productselect')
			? "Your product list has been built:</p>" . get_option('wpinterspire_productselect') . "<p><strong>Has the list changed?</strong>"
			: 'Your product list has not yet been built.';
		?>
		<a href='<?php echo wp_nonce_url( admin_url( 'options-general.php?page=wpinterspire&amp;wpinterspirerebuild=all' ), 'rebuild' ); ?>' class='button'>
		<?php echo ! get_option( 'wpinterspire_productselect' ) ? 'Re-build your products list' : 'Build your products list'; ?></a><br />
		<small>Note: this may take a long time, depending on the size of your products list.</small>
	</p>

	<?php } else { ?>

	<h3>This plugin requires a Bigcommerce account.</h3>
	<h4>What is Bigcommerce?</h4>
	<p>
		Bigcommerce is the #1 rated hosted e-commerce platform.
		If you want to have an e-commerce store without having to manage the server, security, and payments, Bigcommerce is for you.
	</p>
	<p>
		<a href="<?php shuffle( $vendors ); echo $vendors[0]; ?>" target="_blank">Visit Bigcommerce.com to start your own online store today!</a>.
		You can also check out all the <a href="http://www.bigcommerce.com/showcase/" target="_blank">neat stores that use Bigcommerce</a>.
	</p>

	<?php } ?>
	<hr />

	<h3>Store Settings</h3>
	<p>
		Find your API settings at <code>yourstore.com/admin/index.php?ToDo=viewUsers</code>.
		Click Edit next to your username.
		Check the &quot;Yes, allow this user to use the XML API&quot; checkbox.
	</p>
	<form method="post" action="options.php">
		<input type='hidden' name='wpinterspire[seourls]' value='no' />
		<input type='hidden' name='wpinterspire[configured]' value='<?php echo self::$configured; ?>' />
		<?php 
		wp_nonce_field( 'update-options' );
		settings_fields( 'wpinterspire_options' );
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="wpinterspire_username"><?php echo __('Store Username', 'wpinterspire'); ?>:</label></th>
					<td>
						<input type='text' name='wpinterspire[username]' id='wpinterspire_username' value='<?php echo esc_attr( $options->username ); ?>' size='40' /><br />
						<small>The username whose API credentials are below.</small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpinterspire_xmlpath"><?php echo __('API Path', 'wpinterspire'); ?>:</label></th>
					<td>
						<input type='text' name='wpinterspire[xmlpath]' id='wpinterspire_xmlpath' value='<?php echo esc_attr( $options->xmlpath ); ?>' size='40' /><br />
						<small>Your Store's API Path (<code>http://www.example.com/xml.php</code>).</small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpinterspire_xmltoken"><?php echo __('API Token', 'wpinterspire'); ?>:</label></th>
					<td>
						<input type='text' name='wpinterspire[xmltoken]' id='wpinterspire_xmltoken' value='<?php echo esc_attr( $options->xmltoken ); ?>' size='40' /><br />
						<small>Your Store's API Token.</small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpinterspire_storepath"><?php echo __('Store Path (optional)', 'wpinterspire'); ?>:</label></th>
					<td>
						<input type='text' name='wpinterspire[storepath]' id='wpinterspire_storepath' value='<?php echo esc_attr( $options->storepath ); ?>' size='40' /><br />
						<small>
							Your Store's URL (<code>http://mystore.mybigcommerce.com/</code>).
							Entering this into your browser should take you to your home page.
							This is optional, and only to shorten the shortcode when linking to your products.
						</small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpinterspire_seourls"><?php echo __('SEO URLs (optional)', 'wpinterspire'); ?>:</label></th>
					<td>
						<input type='checkbox' name='wpinterspire[seourls]' id='wpinterspire_seourls' value='yes' <?php echo ( ( isset( $options->seourls ) && $options->seourls == 'yes' ) ? 'checked=checked' : '' ); ?> />
						The store uses SEO-friendly URL structure<br />
						<small>If your product URLs look like <code>/products.php?product=product-name</code>, this should be unchecked.</small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="wpinterspire_showlink"><?php echo __('Give Thanks (optional)', 'wpinterspire'); ?>:</label></th>
					<td>
						<input type='checkbox' name='wpinterspire[showlink]' id='wpinterspire_showlink' value='yes' <?php echo ( ( isset( $options->showlink ) && $options->showlink == 'yes' ) ? 'checked=checked' : '' ); ?> />
						Help show the love by telling the world you use this plugin.<br />
						<small>A link will be added to your footer. Please show support for this plugin by enabling.</small>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="page_options" value="<?php foreach($rows as $row) { $output .= $row['id'].','; } echo substr($output, 0, -1);?>" />
		<input type="hidden" name="action" value="update" />
		<p><input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes', 'wpinterspire') ?>" /></p>
	</form>
</div>