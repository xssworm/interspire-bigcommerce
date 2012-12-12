<?php

// Bigcommerce Communications Class
class Bigcommerce_api {

	// Communicate Via cURL
	// Private For Security Purposes
	private function curl( $path ) {
		$options = Bigcommerce_settings::get_options();

		// Ensure API URL Is Secure
		$options->storepath = str_replace( 'http://', 'https://', $options->storepath );

		// Convert API v1 URLs To v2
		$options->storepath = str_replace( '/xml.php', '/api/v2/', $options->storepath );

		// Ensure The API URL Has a Trailing Slash
		$options->storepath = (
			substr( $options->storepath, ( strlen( $options->storepath ) - 1 ), 1 ) != '/'
		) ? "{$options->storepath}/" : $options->storepath;

		// Ensure The API URL Contains The API Path
		$options->storepath = ( strstr( $options->storepath, '/api/v2/' ) )
			? $options->storepath
			: "{$options->storepath}api/v2/";

		// Communicate
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, "{$options->storepath}{$path}" );
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