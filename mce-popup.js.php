<script type="text/javascript">
	function InterspireInsertProduct(){
		var product_id = jQuery("#interspire_add_product_id").val();
		if(product_id == ""){
			alert("<?php _e("The product you selected does not have a link. Try rebuilding your product list in settings.", "wpinterspire") ?>");
			return;
		} else {
			var link_product = ' link="' + product_id + '"';
		}

		var display_title = jQuery("#interspire_display_title").val();
		var link_target = '';
		var link_nofollow = '';
		<?php 
		// If the path to the store is set, we only need the end of the URL;
		// this is to de-clutter the editor
		if(!empty($this->storepath)) { ?>
		product_id = product_id.replace("<?php echo $this->storepath; ?>", '');
		<?php } ?>
		if(jQuery("#link_target").is(":checked")) { link_target = ' target="blank"'; }
		if(jQuery("#link_nofollow").is(":checked")) { link_nofollow = ' rel="nofollow"'; }
		
		var win = window.dialogArguments || opener || parent || top;
		var shortcodeName = '<?php echo preg_match('/mybigcommerce/ism', $this->xmlpath) ? 'bigcommerce' : 'interspire'; ?>';
		win.send_to_editor("["+shortcodeName+link_product+link_target+link_nofollow+"]"+display_title+"[/"+shortcodeName+"]");
	}
</script>
