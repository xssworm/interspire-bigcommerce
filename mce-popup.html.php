<div id="interspire_select_product" style="display:none;">
	<div id="media-upload">
		<h3>
			<?php _e( 'Insert a Product', 'wpinterspire' ); ?>
		</h3>
		<?php

		Bigcommerce_api::BuildProductsSelect( false );
		if( ! get_option('wpinterspire_productselect') ) {
			echo '
				<p>Your settings are correct, however your product list has not been generated.</p>
				<p>
					<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '" class="button">
					Bigcommerce Settings</a>
				</p>
			';
		} else { 

		?>
		<h4>
			<?php _e( 'Select a product below to add it to your post or page.', 'wpinterspire' ); ?>
		</h4>
		<table class="describe">
			<tbody>
				<tr>
					<th valign="top" scope="row" class="label" style="width:130px;">
						<span class="alignleft">
							<label for="interspire_display_title">
								<?php _e( 'Link Text', 'wpinterspire' ); ?>:
							</label>
						</span>
					</th>
					<td class="field">
						<input type="text" id="interspire_display_title" size="100" />
					</td>
				</tr>
				<tr>
					<th valign="top" scope="row" class="label">
						<span class="alignleft">
							<label for="interspire_add_product_id">
								<?php _e( 'Select the Product', 'wpinterspire' ); ?>:
							</label>
						</span>
					</th>
					<td class="field">
						<?php echo get_option( 'wpinterspire_productselect' ); ?>
					</td>
				</tr>
				<tr>
					<th valign="top" scope="row" class="label">
						<span class="alignleft">
							<label for="url">
								<?php _e( 'Additional options', 'wpinterspire' ); ?>:
							</label>
						</span>
					</th>
					<td class="field">
						<input type="checkbox" id="link_nofollow" />
						<label for="link_nofollow">
							<?php _e( 'Nofollow the link', 'wpinterspire' ); ?>
						</label><br />

						<input type="checkbox" id="link_target" />
						<label for="link_target">
							<?php _e( 'Open link in a new window', 'wpinterspire' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="button" class="button-primary" value="Insert Product" onclick="InterspireInsertProduct();"/>
						&nbsp;&nbsp;&nbsp;
						<a class="button" style="color:#bbb;" href="#" title="Cancel" onclick="tb_remove(); return false;">
							<?php _e( 'Cancel', 'wpinterspire'); ?>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } ?>
	</div>
</div>