<?php

// Bigcommerce Communications Class
class Bigcommerce_api {

	// Communicate Via cURL
	// Private For Security Purposes
	private function curl( $path ) {
		$options = Bigcommerce_settings::get_options();
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