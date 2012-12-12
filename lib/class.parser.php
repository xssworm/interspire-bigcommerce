<?php

// Plugin Parser Class
class Bigcommerce_parser {

	// Converts XML To Object
	public function XmlToObject( $xml, $test ) {

		// Try To Convert
		try {
			$response = new SimpleXMLElement( $xml, LIBXML_NOCDATA );

		// Catch Error In Conversion
		} catch ( Exception $error ) {
			Bigcommerce_settings::$errors[] = $error;
			return false;
		}

		// Handle Bad Response
		if( isset( $response->errors->error[0]->message ) ) {
			Bigcommerce_settings::$errors[] = $response->errors->error[0]->message;
			return false;
		}

		// Handle Good Response
		return isset( $response->$test ) ? $response->$test : false;
	}

	// Gets Live Image URL
	public function GetImage( $link ) {
    	$options = Bigcommerce_settings::get_options();

		// Query Image Info
		$image = Bigcommerce_api::GetDetail( substr( $link, 1 ) );
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
		if ( ! $items = Bigcommerce_api::GetProducts() ) { return false; }

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
		if ( ! $items = Bigcommerce_api::GetCategories() ) { return false; }

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

	// Outputs Products In a Category
	function DisplayProductsInCategory( $catid ) {
		$output = '';

		// Find Products
		$products = get_option( 'wpinterspire_products' );
		$products = self::XmlToObject( $products, 'product' );
		if( $products ) {
			foreach( $products as $product ) {
				foreach( $product->categories as $product_category ) {
					$product_category = intval( $product_category->value );

					// Product Matches Category
					if( $catid == $product_category ) {

						// Ensure Visible
						if( (string) $product->is_visible != 'true' ) { continue; }

						// Check For Image
						$image = '<p>No image available</p>';
						if( isset( $product->images[0]->link ) ) {
							$image = self::GetImage(
								$product->images[0]->link
							);
						}

						// Output The Row			
						$output .= Bigcommerce_display::DisplayProductRow(
							(object) array(
								'is_featured' => (
									( (string) $product->is_featured == 'true' )
										? 'featured' : ''
								),
								'name' => (string) $product->name,
								'sku' => (
									( (string) $product->sku )
										? (string) $product->sku
										: 'Not specified'
								),
								'price' => (
									( (string) $product->is_price_hidden == 'true' )
										? 'Not specified'
										: (
											( (int) $product->retail_price > 0 )
											? '$' . number_format( (int) $product->retail_price, 2 )
											: '$' . number_format( (int) $product->price, 2 )
										)
								),
								'condition' => (
									( (string) $product->is_condition_shown == 'false' )
										? 'Not specified'
										: (string) $product->condition
								),
								'availability' => ucwords( (string) $product->availability ),
								'link' => sanitize_title( (string) $product->name ),
								'image' => $image,
								'warranty' => (
									( (string) $product->warranty )
										? (string) $product->warranty
										: 'Not specified'
								),
								'rating' => (
									( (int) $product->rating_count === 0 )
										? 'No ratings available'
										: (int) $product->rating_total
											. " (from {$data->rating_count} ratings)"
								),
							)
						);
					}
				}
			}
		}

		// Output
		return ( $output )
			? $output
			: __(
				sprintf( "Unable to find any products within: %s</p>", $category ),
				'wpinterspire'
			);
	}
}

?>