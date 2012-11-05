<div class="tablenav">
	<?php

	// Loop Products
	$i = 0;
	foreach( $Products['items'] as $key => $product ) {

		// Skip Products Without Images
		if( empty( $product['imagefilestd'] ) ) { continue; }

		// Count Products
		$i++;
	}

	// Handle No Products
	if ( ! $i ) {
		echo '<div id="message" class="updated"><p>Your store has no images.</p></div>';
		return false;
	}

	$_GET['paged'] = isset( $_GET['paged'] ) ? intval($_GET['paged']) : 0;
	if( $_GET['paged'] < 1 ) { $_GET['paged'] = 1; }
	$start = ( $_GET['paged'] - 1 ) * 10;
	if( $start < 1 ) { $start = 0; }
	$page_links = paginate_links(
		array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => ceil( $i / 10 ),
			'current' => $_GET['paged']
		)
	);
	if( $page_links ) {
		$page_links_form = "<form id='filter'>";
		$page_links_form .= "<div class='tablenav-pages'>{$page_links}</div>";
		$page_links_form .= "</form>";
	}
	$mediaitems = '<div class="alignleft actions">';
	$default_align = get_option('image_default_align');
	if( empty($default_align) ) { $default_align = 'none'; }
	$postID = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
	$mediaitems .= '<form enctype="multipart/form-data" method="post" action="'
		. admin_url('media-upload.php?type=image&amp;tab=interspire&amp;post_id='
		. $postID) . '" class="media-upload-form validate" id="library-form">'
		. '<div id="media-items">';

	// Loop Products
	$i = 0;
	foreach( $Products['items'] as $key => $product ) {
		if( empty( $product['imagefilestd'] ) ) { continue; }
		$i++;
		if( $i < $start || $i > ( $start + 9 ) ) { continue; }
		extract( (array) $product );
		?>
		<a class='toggle describe-toggle-on' href='#media-item-$productid'>$toggle_on</a>
		<a class='toggle describe-toggle-off' href='#media-item-$productid'>$toggle_off</a>".'
		<div id="media-item-'.$productid.'" class="media-item preloaded">
			<div style="width:40px; float:left;"><img src="'.$options->storepath.$imagefilethumb.'" class="pinkynail toggle" /></div>
			<div class="filename new"><span class="title">'.$prodname.'</span></div>
			<table class="slidetoggle describe '.$class.'">
				<thead class="media-item-info" id="media-head-$post->ID" />
				<tbody>
					<tr>
						<th valign="top" scope="row" class="label" style="width:130px;">
							<span class="alignleft"><label for="src-'.$productid.'">' . __('Image URL') . '</label></span>
							<span class="alignright"><abbr title="required" class="status_img required">*</abbr></span>
						</th>
						<td class="field"><input id="src-'.$productid.'" name="src" value="'.$options->storepath.$imagefilestd.'" type="text" aria-required="true" /></td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="title-'.$productid.'">' . __('Image Title') . '</label></span>
							<span class="alignright"><abbr title="required" class="required">*</abbr></span>
						</th>
						<td class="field"><input id="title-'.$productid.'" name="title" value="'.$prodname.'" type="text" aria-required="true" /></td>
					</tr>
			
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="alt-'.$productid.'">' . __('Alternate Text') . '</label></span>
						</th>
						<td class="field"><input id="alt-'.$productid.'" name="alt" value="'.$prodname.'" type="text" aria-required="true" />
						<p class="help">' . __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;') . '</p></td>
					</tr>
					' . $caption . '
					<tr class="align">
						<th valign="top" scope="row" class="label"><p><label for="align-'.$productid.'">' . __('Alignment') . '</label></p></th>
						<td class="field">
							<input name="align" id="align-none-'.$productid.'" value="none" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'none' ? ' checked="checked"' : '').' />
							<label for="align-none-'.$productid.'" class="align image-align-none-label">' . __('None') . '</label>
							<input name="align" id="align-left-'.$productid.'" value="left" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'left' ? ' checked="checked"' : '').' />
							<label for="align-left-'.$productid.'" class="align image-align-left-label">' . __('Left') . '</label>
							<input name="align" id="align-center-'.$productid.'" value="center" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'center' ? ' checked="checked"' : '').' />
							<label for="align-center-'.$productid.'" class="align image-align-center-label">' . __('Center') . '</label>
							<input name="align" id="align-right-'.$productid.'" value="right" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'right' ? ' checked="checked"' : '').' />
							<label for="align-right-'.$productid.'" class="align image-align-right-label">' . __('Right') . '</label>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" class="label">
							<span class="alignleft"><label for="url-'.$productid.'">' . __('Link Image To:') . '</label></span>
						</th>
						<td class="field"><input id="url-'.$productid.'" name="url" value="" type="text" /><br />
						<input type="hidden" id="productlink-'.$productid.'" value="'.esc_html(self::MakeURL($prodname)).'" />
						<button type="button" id="url-none-'.$productid.'" class="button url-none" value="">' . __('None') . '</button>
						<button type="button" id="url-product-'.$productid.'" class="button url-product" value="">' . __('Link to product') . '</button>
						<button type="button" id="url-src-'.$productid.'" class="button url-src" value="">' . __('Link to image') . '</button>';
						if( ! empty( $imagefilezoom ) ) {
							$mediaitems .= '<button type="button" id="url-large-'.$productid.'" class="button url-large" value="'.$imagefilezoom.'">' . __('Link to large image') . '</button>';
						}
						$mediaitems .= '<p class="help">' . __('Enter a link URL or click above for presets.') . '</p></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="button" class="button" id="go_button-' . $productid . '" style="color:#bbb;" onclick="addExtImage.insert(this)" value="' . esc_attr__('Insert into Post') . '" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php } ?>
	</div>
	</form>
	</div>
	</div>
</div>