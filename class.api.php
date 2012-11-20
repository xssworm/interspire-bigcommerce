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

	// Get Products
	public function GetProducts( $rebuild ) {

		// Not Forcing Rebuild
		if( ! $rebuild ) {
			return maybe_unserialize( get_option( 'wpinterspire_products' ) );
		}

		// Query Bigcommerce API
		$result = self::curl( 'products' );
		try {
			$response = new SimpleXMLElement( $result, LIBXML_NOCDATA );
		} catch ( Exception $error ) {
			Bigcommerce::$errors[] = $error;
			return false;
		}

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Handle Bad Response
		if( isset( $response->errors->error[0]->message ) ) {
			Bigcommerce::$errors[] = $response->errors->error[0]->message;
			return false;
		}

		// Handle Good Response
		return isset( $response->product ) ? $response->product : false;
	}

	// Checks Saved Settings
	public function CheckSettings() {
    	return ( self::GetProducts( true ) );
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