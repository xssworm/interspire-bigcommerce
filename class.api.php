<?php

// Bigcommerce Communications Class
class Bigcommerce_api {

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

	// Checks Saved Settings
	public function CheckSettings() {
    	return ( self::GetProducts( true ) );
	}

	// Get Product Image
	public function GetDetail( $uri ) {

		// Query Bigcommerce API
		$response = self::curl( $uri );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Output
		return $response;
	}

	// Gets Live Image URL
	public function GetImage( $link ) {
    	$options = Bigcommerce::get_options();

		// Query Image Info
		$path = substr( $link, 1 );
		$path = self::GetDetail( $path );
		$path = new SimpleXMLElement( $path, LIBXML_NOCDATA );
		$path = $path->image[0]->image_file;
		$path = $options->storepath . 'product_images/' . $path;
		return $path;
	}

	// Get Products
	public function GetProducts( $rebuild ) {

		// Not Forcing Rebuild
		if( ! $rebuild ) {
			$response = maybe_unserialize( get_option( 'wpinterspire_products' ) );
			if( isset( $response->product ) ) { return $response->product; }
		}

		// Query Bigcommerce API
		$response = self::curl( 'products' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_products', $response );

		// Convert XML To Object
		try {
			$response = new SimpleXMLElement( $response, LIBXML_NOCDATA );
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
		return isset( $response->product ) ? $response->product : false;
	}

	// Get Categories
	public function GetCategories() {

		// Query Bigcommerce API
		$response = self::curl( 'categories' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Convert XML To Object
		try {
			$response = new SimpleXMLElement( $response, LIBXML_NOCDATA );
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
		return isset( $response->category ) ? $response->category : false;
	}

	// Builds Select Box Of Products
	public function BuildProductsSelect( $rebuild ) {
		$output = get_option( 'wpinterspire_productselect' );

		// Rebuilding Requested Or Necessary
		if( $rebuild ) {

			// Get Products
			if ( ! $products = self::GetProducts( true ) ) { return false; }

			// Generate HTML Selector
			$output = '
				<select id="interspire_add_product_id">
				<option value="" disabled="disabled" selected="selected">Products</option>
			';
		    foreach( $products as $product ) {
				if( isset( $product->name ) ) {
					$output .= '<option value="' . sanitize_title( $product->name ) . '">'
						. esc_html( $product->name ) . '</option>';
				}
		    }
	        $output .= '</select>';

			// Save HTML Selector To Cache
			update_option( 'wpinterspire_productselect', $output );
		}
		return $output;
	}
}

?>