<?php

// Bigcommerce Communications Class
class Bigcommerce_api {

	// Communicate Via cURL
	// Private For Security Purposes
	private function curl( $path ) {
		$options = Bigcommerce_settings::get_options();
		$storepath = Bigcommerce_parser::storepath( true );

		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode("{$options->username}:{$options->xmltoken}")
			)
		);

		$response = wp_remote_request($storepath.$path, $args );

		if(!is_wp_error($response)) {
			$result = $response['body'];
			return $result;
		}
		return false;
	}

	// Get Product Image
	function GetDetail( $uri ) {

		// Query Bigcommerce API
		$response = self::curl( $uri );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Output
		return $response;
	}

	// Get Products
	function GetProducts() {

		// Query Bigcommerce API
		$response = self::curl( 'products' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_products', $response );
		return Bigcommerce_parser::XmlToObject( $response, 'product' );
	}

	// Get Categories
	function GetCategories() {

		// Query Bigcommerce API
		$response = self::curl( 'categories' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_categories', $response );
		return Bigcommerce_parser::XmlToObject( $response, 'category' );
	}
}

?>