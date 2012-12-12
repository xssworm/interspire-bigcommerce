<?php

// Plugin Media Class
class Bigcommerce_media {

	// Tied To WP Hook By The Same Name - Adds Product Images Tab To Media Popup
	function media_upload_tabs( $tabs ) {
		return array_merge(
			$tabs, array( 'wpinterspire' => __( 'Bigcommerce', 'wpinterspire' ) )
		);
	}

	// Tied To WP Hook By The Same Name - Adds Menu Item For Processing Media
	function media_upload_wpinterspire() {
		return wp_iframe( array( 'Bigcommerce_media', 'media_process' ) );
	}

	// Tied To WP Hook By The Same Name - Ads Icon To WYSIWYG Posts/Pages Editor
	function media_buttons_context( $context ) {
		if( ! Bigcommerce_settings::$configured ) { return $context; }
		return $context . '
			<a href="#TB_inline?width=640&inlineId=interspire_select_product" class="thickbox"
				title="' . __( 'Add Bigcommerce Product Link', 'wpinterspire' ) . '">
			<img src="' . plugins_url( 'favicon.png', dirname( __FILE__ ) ) . '" width="16" height="16"
				alt="' . __( "Add a Product", 'wpinterspire' ) . '" /></a>
		';
	}

	// Tied To WP Hook By The Same Name - Admin Area Footer
	function admin_footer() {
		$options = Bigcommerce_settings::get_options();
		require( dirname( __FILE__ ) . '/../views/mce-popup.html.php' );
	}

	// Presents Product Image Choices
	function media_process() {
    	$options = Bigcommerce_settings::get_options();

		// Get Products From Cache
		$Products = get_option( 'wpinterspire_products' );
		$Products = Bigcommerce_parser::XmlToObject( $Products, 'product' );

		// Present Other Tabs
		media_upload_header();

		// Handle No Products
		if( ! $Products ) {
			echo '
				<div class="tablenav">
					<form id="filter">
						<h3>'
							. __( 'The Plugin settings have not been properly configured.', 'wpinterspire' ) .
						'</h3>
					</form>
				</div>
			';
			return;
		}

		// Pagination Variables
		$perpage = 10;
		$page = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
	   	$start = $perpage * ( $page - 1 );
	   	$end = $start + ( $perpage - 1 );
		$paginate_links = paginate_links(
			array(
				'base' => add_query_arg( 'paged', '%#%' ),
				'format' => '',
				'total' => ceil( sizeof( $Products ) / $perpage ),
				'current' => $page,
			)
		);

		// Loop Products
		$images = array();
		$i = -1;
		foreach( $Products as $product ) {
			$i++;

			// Limit To Per Page Quantity
			if( $i < $start || $i > $end ) { continue; }

			// Skip Products Without Images
			if( ! isset( $product->images[0]->link ) ) { continue; }
			$path = Bigcommerce_parser::GetImage( $product->images[0]->link );
			$images[] = array(
				'productid'	=> $product->id,
				'name' => $product->name,
				'path' => $path,
			);
		}

		// Output
		require( dirname( __FILE__ ) . '/../views/media.html.php' );
	}
}

?>