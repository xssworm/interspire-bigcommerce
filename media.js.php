<script type="text/javascript">
jQuery(function($){

	var mediaitems = $('.media-items');
	$('a.toggle').live('click', function(e) {
		e.preventDefault();
		$('a.toggle[href='+$(this).attr('href')+']:hidden').toggle();
		$(this).toggle();
		$('table.slidetoggle',$(this).attr('href')).slideToggle();
		
		addExtImage.getImageData($(this).attr('href'));
		
		return false;
	});
	
	$('input[name=src][id*=src]').live('blur', function() {
		var id = $(this).attr('id');
		id = id.replace('src-', '');
		addExtImage.getImageData($(this).parents('div.media-item'));
	});
	
	$('button.url-src').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-src-', '');
		$('#url-'+id).val(jQuery('#src-'+id).val());
		return false;
	});
	
	$('button.url-large').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-large-', '');
		$('#url-'+id).val('<?php echo $options->storepath; ?>'+jQuery(this).val());
		return false;
	});
	
	$('button.url-none').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-none-', '');
		$('#url-'+id).val('');
		return false;
	});
	
	$('button.url-product').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-product-', '');
		<?php		
		if($options->seourls == 'no') {
			echo 'var link = "'.$options->storepath.'/products.php?product="+$("#productlink-"+id).val();';
		} else {
			echo 'var link = "'.$options->storepath.'/products/"+$("#productlink-"+id).val()+".html";';
		}
		?>
		$('#url-'+id).val(link);
		return false;
	});
});

var addExtImage = {
	
		width : '',
		height : '',
		align : 'alignnone',
	
		insert : function(that) {
			var t = this, html, f = jQuery(that).parents('div.media-item'), cls, title = '', alt = '', caption = '';
			
			if ( '' == jQuery('input[name="src"]', f).val() || '' == t.width )
				return false;
	
			if ( jQuery('input[name="title"]', f).val() ) {
				title = jQuery('input[name="title"]', f).val().replace(/'/g, '&#039;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				title = ' title="'+title+'"';
			}
	
			if ( jQuery('input[name="alt"]', f).val() )
				alt = jQuery('input[name="alt"]', f).val().replace(/'/g, '&#039;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	
	<?php if ( ! apply_filters( 'disable_captions', '' ) ) { ?>
			if ( jQuery('input[name="caption"]', f).val() )
				caption = jQuery('input[name="caption"]', f).val().replace(/'/g, '&#039;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	<?php } ?>
	
			cls = caption ? '' : ' class="'+t.align+'"';
	
			html = '<img alt="'+alt+'" src="'+jQuery('input[name="src"]', f).val()+'"'+title+cls+' width="'+t.width+'" height="'+t.height+'" />';
	
			if ( jQuery('input[name="url"]', f).val() )
				html = '<a href="'+jQuery('input[name="url"]', f).val()+'">'+html+'</a>';
	
			if ( caption )
				html = '[caption id="" align="'+t.align+'" width="'+t.width+'" caption="'+caption+'"]'+html+'[/caption]';
	
			var win = window.dialogArguments || opener || parent || top;
			win.send_to_editor(html);
			return false;
		},
	
		resetImageData : function() {
			var t = addExtImage;
	
			t.width = t.height = '';
			jQuery('input[id*=go_button]', t.formEl).css('color','#bbb');
			if ( jQuery('input[name="src"]', t.formEl).val() == '' )
				jQuery('.status_img', t.formEl).html('*');
			else jQuery('.status_img', t.formEl).html('<img src="<?php echo esc_url( admin_url( 'images/no.png' ) ); ?>" alt="" />');
		},
	
		updateImageData : function() {
			var t = addExtImage;
			t.width = t.preloadImg.width;
			t.height = t.preloadImg.height;
			jQuery('input[id*=go_button]', t.formEl).css('color','#333');
			jQuery('.status_img', t.formEl).html('<img src="<?php echo esc_url( admin_url( 'images/yes.png' ) ); ?>" alt="" />');
		},
	
		getImageData : function(that) {
			var t = addExtImage, src = jQuery('input[name="src"]', that).val();
			
			t.formEl = jQuery(that);
			
			if ( ! src ) {
				t.resetImageData;
				return false;
			}
			jQuery('.status_img', t.formEl).html('<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />');
			t.preloadImg = new Image();
			t.preloadImg.onload = t.updateImageData;
			t.preloadImg.onerror = t.resetImageData;
			t.preloadImg.src = src;
		}
	}
</script>