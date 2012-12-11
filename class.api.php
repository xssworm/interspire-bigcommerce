<?php

// Bigcommerce Communications Class
class Bigcommerce_api {


	/***************
	 Private Methods
	 ***************/

	// Communicate Via cURL
	private function curl( $path ) {
		$options = Bigcommerce::get_options();
		$url = "{$options->storepath}api/v2/{$path}";
		$url = str_replace( 'http://', 'https://', $url );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_USERPWD, "{$options->username}:{$options->xmltoken}" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, null );
		curl_setopt( $ch, CURLOPT_POST, false );
		curl_setopt( $ch, CURLOPT_HTTPGET, true );
		$result = curl_exec( $ch );
		curl_close( $ch );
		return $result;
	}

	// Get Product Image
	private function GetDetail( $uri ) {

		// Query Bigcommerce API
		$response = self::curl( $uri );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Output
		return $response;
	}

	// Get Products
	private function GetProducts() {

		// Query Bigcommerce API
		$response = self::curl( 'products' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_products', $response );
		return self::XmlToObject( $response, 'product' );
	}

	// Get Categories
	private function GetCategories() {

		// Query Bigcommerce API
		$response = self::curl( 'categories' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_categories', $response );
		return self::XmlToObject( $response, 'category' );
	}


	/**************
	 Public Methods
	 **************/

	// Converts XML To Object
	public function XmlToObject( $xml, $test ) {

		// Try To Convert
		try {
			$response = new SimpleXMLElement( $xml, LIBXML_NOCDATA );

		// Catch Error In Conversion
		} catch ( Exception $error ) {
			Bigcommerce::$errors[] = $error;
			return false;
		}

		// Handle Bad Response
		if( isset( $response->errors->error[0]->message ) ) {
			Bigcommerce::$errors[] = $response->errors->error[0]->message;
			return false;
		}

		// Handle Good Response
		return isset( $response->$test ) ? $response->$test : false;
	}

	// Checks Saved Settings
	public function CheckSettings() {
    	return ( self::GetCategories() );
	}

	// Gets Live Image URL
	public function GetImage( $link ) {
    	$options = Bigcommerce::get_options();

		// Query Image Info
		$image = self::GetDetail( substr( $link, 1 ) );
		$image = self::XmlToObject( $image, 'image' );
		$path = $image[0]->image_file;
		return $options->storepath . 'product_images/' . $path;
	}

	// Builds Select Box For Products
	public function BuildProductsSelect( $rebuild ) {

		// Not Forcing Rebuild
		if( ! $rebuild ) {
			return get_option( 'wpinterspire_productselect' );
		}

		// Get Products
		if ( ! $items = self::GetProducts() ) { return false; }

		// Generate HTML Selector
		$output = '
			<select id="interspire_add_product_id">
			<option value="" disabled="disabled" selected="selected">Products</option>
		';
		foreach( $items as $item ) {
			if( isset( $item->name ) ) {
				$output .= '<option value="' . sanitize_title( $item->name ) . '">'
					. esc_html( $item->name ) . '</option>';
			}
		}
		$output .= '</select>';

		// Save HTML Selector To Cache
		update_option( 'wpinterspire_productselect', $output );
		return $output;
	}

	// Builds Select Box For Categories
	public function BuildCategoriesSelect( $rebuild ) {

		// Not Forcing Rebuild
		if( ! $rebuild ) {
			return get_option( 'wpinterspire_categoryselect' );
		}

		// Get Products
		if ( ! $items = self::GetCategories() ) { return false; }

		// Generate HTML Selector
		$output = '
			<select id="interspire_add_category_id">
			<option value="" disabled="disabled" selected="selected">Categories</option>
		';
		foreach( $items as $item ) {
			if( isset( $item->name ) ) {
				$output .= '<option>' . esc_html( $item->name ) . '</option>';
			}
		}
		$output .= '</select>';

		// Save HTML Selector To Cache
		update_option( 'wpinterspire_categoryselect', $output );
		return $output;
	}
}

?>