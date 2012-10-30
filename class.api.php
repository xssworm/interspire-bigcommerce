<?php

// Bigcommerce Communications Class
class Bigcommerce_api {

	// Query Bigcommerce For All Products
	public function get_products() {
		$xml =
			'<requesttype>products</requesttype>'
			. '<requestmethod>GetProduct</requestmethod>'
			. '<details>'
				. '<productId>-1</productId>'
			. '</details>';
		$xml = self::wrapper( $xml );
		return self::communicate( $xml );
	}

	// Generates XML Request
	private function wrapper( $xml ) {
		$options = Bigcommerce::get_options();
		return
			'<xmlrequest>'
				. "<username>{$options->username}</username>"
				. "<usertoken>{$options->xmltoken}</usertoken>"
				. $xml
			. '</xmlrequest>';
	}

	// Communicate
	private function communicate( $Vars='', $asobject = true ) {
		$options = Bigcommerce::get_options();
		$Vars = 'xml=' . urlencode( $Vars );
		$Path = $options->xmlpath;
		$result = null;
		$args = array(
			'body' => $Vars,
			'sslverify' => is_ssl(),
			'timeout' => 600
		);
		$result = wp_remote_retrieve_body( wp_remote_post( $Path, $args ) );
		if( $asobject ) {
			try {
				$response = new SimpleXMLElement( $result, LIBXML_NOCDATA );
			} catch ( Exception $e ) {
				return false;
			}			
			if( ! is_object( $response ) ) {
				return false;
			}
			return $response;
		} else {
			return empty( $response ) ? false : $response;
		}
	}

}

?>