<script type="text/javascript">
	function InterspireInsertProduct() {
		var product_id = jQuery( '#interspire_add_product_id' ).val();
		if( product_id == '' ) {
			alert("<?php _e('The product you selected does not have a link. Try rebuilding your product list in settings.', 'wpinterspire'); ?>");
			return;
		} else {
			var link_product = ' link="' + product_id + '"';
		}
		var display_title = jQuery( '#interspire_display_title' ).val();
		var link_target = '';
		var link_nofollow = '';
		<?php 
		if( ! empty( $options->storepath ) ) { ?>
		product_id = product_id.replace( '<?php echo $options->storepath; ?>', '');
		<?php } ?>
		if( jQuery( '#link_target' ).is( ':checked' ) ) { link_target = ' target="blank"'; }
		if( jQuery( '#link_nofollow' ).is( ':checked' ) ) { link_nofollow = ' rel="nofollow"'; }
		var win = window.dialogArguments || opener || parent || top;
		win.send_to_editor('[bigcommerce' + link_product + link_target + link_nofollow + ']' + display_title + '[/bigcommerce]');
	}
</script>
