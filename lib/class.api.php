<?php

// Bigcommerce Communications Class
class Bigcommerce_api {

	// Communications
	private function communicate( $path ) {
		$options = Bigcommerce_settings::get_options();
		$storepath = Bigcommerce_parser::storepath( true );
		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode(
					"{$options->username}:{$options->xmltoken}"
				)
			)
		);
		$response = wp_remote_request( $storepath . $path, $args );
		if( is_wp_error( $response ) ) {
			Bigcommerce_settings::$errors[] = $response->get_error_message();
			return false;
		}
		return isset( $response['body'] ) ? $response['body'] : false;
	}

	// Get Product Image
	function GetDetail( $uri ) {

		// Query Bigcommerce API
		$response = self::communicate( $uri );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Output
		return $response;
	}

	// Get Products
	function GetProducts() {

		// Query Bigcommerce API
		$response = self::communicate( 'products' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_products', $response );
		return Bigcommerce_parser::XmlToObject( $response, 'product' );
	}

	// Get Categories
	function GetCategories() {

		// Query Bigcommerce API
		$response = self::communicate( 'categories' );

		// Handle Lack Of Response
		if( ! $response || empty( $response ) ) { return false; }

		// Save To Cache
		update_option( 'wpinterspire_categories', $response );
		return Bigcommerce_parser::XmlToObject( $response, 'category' );
	}
}

?>