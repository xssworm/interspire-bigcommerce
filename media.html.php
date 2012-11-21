<div class="tablenav">
	<?php

	// Handle No Product Image
	if ( ! $images ) {
		echo '<div id="message" class="error"><p>Your store has no images.</p></div>';
		return;
	}

	// Pagination
	$_GET['paged'] = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 0;
	if( $_GET['paged'] < 1 ) { $_GET['paged'] = 1; }
	$start = ( $_GET['paged'] - 1 ) * 10;
	if( $start < 1 ) { $start = 0; }
	$page_links = paginate_links(
		array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'total' => ceil( $i / 10 ),
			'current' => $_GET['paged'],
		)
	);
	if( $page_links ) {
		$page_links_form = "<form id='filter'>";
		$page_links_form .= "<div class='tablenav-pages'>{$page_links}</div>";
		$page_links_form .= "</form>";
	}

	$default_align = get_option( 'image_default_align' );
	if( empty( $default_align ) ) { $default_align = 'none'; }
	$postID = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;

	// Loop Products
	foreach( $images as $productid => $path ) {
		if( $i < $start || $i > ( $start + 9 ) ) { continue; }

		?>
		<a class='toggle describe-toggle-on' href='#media-item-<?php echo $productid; ?>'><?php echo $toggle_on; ?></a>
		<a class='toggle describe-toggle-off' href='#media-item-<?php echo $productid; ?>'><?php echo $toggle_off; ?></a>
		<div id="media-item-<?php echo $productid; ?>" class="media-item preloaded">
			<div style="width:40px; float:left;">
				<img src="<?php echo $path; ?>" class="pinkynail toggle" />
			</div>
			<div class="filename new">
				<span class="title"><?php echo $productid; ?></span>
			</div>
			<table class="slidetoggle describe <?php echo $class; ?>">
				<tbody>
					<tr>
						<th valign="top" scope="row" class="label" style="width:130px;">
							<span class="alignleft">
								<label for="src-<?php echo $productid; ?>">
									<?php _e( 'Image URL', 'wpinterspire' ); ?>:
								</label>
							</span>
							<span class="alignright">
								<abbr title="required" class="status_img required">*</abbr>
							</span>
						</th>
						<td class="field">
							<input id="src-<?php echo $productid; ?>" name="src"
								value="<?php echo $path; ?>" type="text" aria-required="true" />
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft">
								<label for="title-<?php echo $productid; ?>">
									<?php _e( 'Image Title', 'wpinterspire' ); ?>:
								</label>
							</span>
							<span class="alignright">
								<abbr title="required" class="required">*</abbr>
							</span>
						</th>
						<td class="field">
							<input id="title-<?php echo $productid; ?>" name="title"
								value="<?php echo $path; ?>" type="text" aria-required="true" />
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft">
								<label for="alt-<?php echo $productid; ?>">
									<?php _e( 'Alternate Text', 'wpinterspire' ); ?>:
								</label>
							</span>
						</th>
						<td class="field">
							<input id="alt-<?php echo $productid; ?>" name="alt"
								value="<?php echo $path; ?>" type="text" aria-required="true" />
							<p class="help">
								<?php _e( 'Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;', 'wpinterspire' ); ?>
							</p>
						</td>
					</tr>
					<?php echo $caption; ?>
					<tr class="align">
						<th valign="top" scope="row" class="label">
							<p>
								<label for="align-<?php echo $productid; ?>">
									<?php _e( 'Alignment', 'wpinterspire' ); ?>:
								</label>
							</p>
						</th>
						<td class="field">
							<input name="align" id="align-none-<?php echo $productid; ?>" value="none" onclick="addExtImage.align=\'align\'+this.value" type="radio"<?php echo ( $default_align == 'none' ? ' checked="checked"' : '' ); ?> />
							<label for="align-none-<?php echo $productid; ?>" class="align image-align-none-label"><?php _e( 'None', 'wpinterspire' ); ?></label>
							<input name="align" id="align-left-<?php echo $productid; ?>" value="left" onclick="addExtImage.align=\'align\'+this.value" type="radio"<?php echo ( $default_align == 'left' ? ' checked="checked"' : '' ); ?> />
							<label for="align-left-<?php echo $productid; ?>" class="align image-align-left-label"><?php _e( 'Left', 'wpinterspire' ); ?></label>
							<input name="align" id="align-center-<?php echo $productid; ?>" value="center" onclick="addExtImage.align=\'align\'+this.value" type="radio"<?php echo ( $default_align == 'center' ? ' checked="checked"' : '' ); ?> />
							<label for="align-center-<?php echo $productid; ?>" class="align image-align-center-label"><?php _e( 'Center', 'wpinterspire'); ?></label>
							<input name="align" id="align-right-<?php echo $productid; ?>" value="right" onclick="addExtImage.align=\'align\'+this.value" type="radio"<?php echo ( $default_align == 'right' ? ' checked="checked"' : '' ); ?> />
							<label for="align-right-<?php echo $productid; ?>" class="align image-align-right-label"><?php _e( 'Right', 'wpinterspire' ); ?></label>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft">
								<label for="url-<?php echo $productid; ?>">
									<?php _e( 'Link Image To', 'wpinterspire' ); ?>:
								</label>
							</span>
						</th>
						<td class="field">
							<input id="url-<?php echo $productid; ?>" name="url" value="" type="text" /><br />
							<input type="hidden" id="productlink-<?php echo $productid; ?>" value="<?php echo $path; ?>" />
							<button type="button" id="url-none-<?php echo $productid; ?>" class="button url-none" value=""><?php _e( 'None', 'wpinterspire' ); ?></button>
							<button type="button" id="url-product-<?php echo $productid; ?>" class="button url-product" value=""><?php _e( 'Link to product', 'wpinterspire' ); ?></button>
							<button type="button" id="url-src-<?php echo $productid; ?>'" class="button url-src" value=""><?php _e( 'Link to image', 'wpinterspire' ); ?></button>
							<div class="alignleft actions">
							<form enctype="multipart/form-data" method="post"
								action="<?php echo admin_url( 'media-upload.php?type=image&amp;tab=interspire&amp;post_id=' . $postID ); ?>"
								class="media-upload-form validate" id="library-form">
								<div id="media-items">
									<button type="button" id="url-large-<?php echo $productid; ?>" class="button url-large" value="<?php echo $path; ?>"><?php _e( 'Link to large image', 'wpinterspire' ); ?></button>
									<p class="help">
										<?php _e( 'Enter a link URL or click above for presets.', 'wpinterspire' ); ?>
									</p>
								</div>
							</form>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="button" class="button" id="go_button-<?php echo $productid; ?>"
								style="color:#bbb;" onclick="addExtImage.insert(this)"
								value="<?php _e( 'Insert into Post', 'wpinterspire' ); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php } ?>
</div>