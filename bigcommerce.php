<?php
/*
Plugin Name: Bigcommerce
Plugin URI: http://www.seodenver.com/interspire-bigcommerce-wordpress/
Description: Integrate Bigcommerce products into your WordPress pages and posts.
Author: Katz Web Services & beAutomated
Version: 1.4.1-devel
Author URI: http://www.katzwebservices.com
License: GPLv2

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version, see <http://www.gnu.org/licenses/>.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

// Includes
require_once( 'class.api.php' );

// WP Hooks - General
add_action( 'admin_init', array( 'Bigcommerce', 'admin_init' ) );
add_action( 'admin_menu', array( 'Bigcommerce', 'admin_menu' ) );
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	array( 'Bigcommerce', 'plugin_action_links' )
);
add_action( 'admin_footer',  array( 'Bigcommerce', 'admin_footer' ) );
add_action( 'wp_footer', array( 'Bigcommerce', 'wp_footer' ) );

// WP Hooks - Media Importing
add_action( 'media_buttons_context', array( 'Bigcommerce', 'media_buttons_context' ) );
add_filter( 'media_upload_tabs', array( 'Bigcommerce', 'media_upload_tabs' ), 11 );
add_action( 'media_upload_wpinterspire', array( 'Bigcommerce', 'media_upload_wpinterspire' ) );

// Shortcodes (Support For Legacy Ones Too)
add_shortcode( 'BigCommerce', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'Bigcommerce', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'bigcommerce', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'Interspire', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'interspire', array( 'Bigcommerce', 'shortcode' ) );

// Plugin Class
class Bigcommerce {
	public static $configured = false;
	public static $errors = array();

	// Tied To WP Hook By The Same Name
	function admin_init() {
		global $pagenow;

		// Handles Saving Of Settings
        register_setting(
        	'wpinterspire_options',
        	'wpinterspire',
        	array( 'Bigcommerce', 'sanitize_settings' )
        );

		// Load Support For Localizations
		load_plugin_textdomain(
			'wpinterspire', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		// Only Continue For Self Settings Page
		if(
			$pagenow == 'options-general.php'
			&& isset( $_REQUEST['page'] )
			&& $_REQUEST['page'] != 'wpinterspire'
		) { return; }

		// Run Settings Check
		self::$configured = Bigcommerce_api::CheckSettings();

		// (Re)Build Products Upon Request
		if (
			isset( $_REQUEST['wpinterspirerebuild'] )
			&& $_REQUEST['wpinterspirerebuild'] == 'all'
		) { Bigcommerce_api::BuildProductsSelect( true ); }
    }


	/***************
	 Media Importing
	 ***************/

	// Tied To WP Hook By The Same Name - Adds Product Images Tab To Media Popup
	function media_upload_tabs( $tabs ) {
		return array_merge(
			$tabs, array( 'wpinterspire' => __( 'Bigcommerce', 'wpinterspire' ) )
		);
	}

	// Tied To WP Hook By The Same Name - Adds Menu Item For Processing Media
	function media_upload_wpinterspire() {
		return wp_iframe( array( 'Bigcommerce', 'media_process' ) );
	}

	// Tied To WP Hook By The Same Name - Ads Icon To WYSIWYG Posts/Pages Editor
	function media_buttons_context( $context ) {
		if( ! self::$configured ) { return $context; }
		return $context . '
			<a href="#TB_inline?width=640&inlineId=interspire_select_product" class="thickbox"
				title="' . __( 'Add Bigcommerce Product Link', 'wpinterspire' ) . '">
			<img src="' . plugins_url( 'favicon.png', __FILE__ ) . '" width="16" height="16"
				alt="' . __( "Add a Product", 'wpinterspire' ) . '" /></a>
		';
	}

	// Tied To WP Hook By The Same Name - Admin Area Footer
	function admin_footer() {
		$options = self::get_options();
		require( 'mce-popup.html.php' );
	}

	// Presents Product Image Choices
	function media_process() {
    	$options = self::get_options();

		// Get Products From Cache
		$Products = get_option( 'wpinterspire_products' );
		$Products = new SimpleXMLElement( $Products, LIBXML_NOCDATA );

		// Present Other Tabs
		media_upload_header();

		// Handle No Products
		if( is_wp_error( $Products ) || ! $Products ) { 
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
				'total' => ceil( sizeof( $Products->product ) / $perpage ),
				'current' => $page,
			)
		);

		// Loop Products
		$images = array();
		for( $i = 0; $i < sizeof( $Products->product ); $i++ ) {
			$product = $Products->product[$i];

			// Limit To Per Page Quantity
			if( $i < $start || $i > $end ) { continue; }

			// Skip Products Without Images
			if( ! isset( $product->images[0]->link ) ) { continue; }
			$path = Bigcommerce_api::GetImage( $product->images[0]->link );
			$images[] = array(
				'productid'	=> $product->id,
				'name' => $product->name,
				'path' => $path,
			);
		}

		// Output
		require( 'media.html.php' );
	}


	/***************
	 WP Settings API
	 ***************/

	// Gets Stored Settings, Failover To Defaults
	function get_options() {
		return ( object ) get_option(
			'wpinterspire', array(
				'username' => '',
				'xmltoken' => '',
				'storepath' => '',
				'showlink' => '',
			)
		);
	}

    // Sanitizes Setting Value Submissions
    function sanitize_settings( $input ) {
		foreach( $input as $key => $val ) {

		   	// Add SSL Protocol and Trailing Slash To Store URL
		   	if( $key == 'storepath' ) {
				$val = str_replace( 'http:', 'https:', $val );
				$val = ( substr( $val, ( strlen( $val ) - 1 ), 1 ) != '/' )
					? "{$val}/" : $val;
				$input[$key] = $val;
			}
		}
    	return $input;
	}

	// Tied To WP Hook By The Same Name - Adds Settings Link
	function plugin_action_links( $links ) {
		$links['settings'] = '<a href="options-general.php?page=wpinterspire">'
			. __( 'Settings', 'wpinterspire' ) . '</a>';
		return $links;
	}

	// Tied To WP Hook By The Same Name - Adds Admin Submenu Link
    function admin_menu() {
        add_options_page( 'Bigcommerce', 'Bigcommerce', 'administrator', 'wpinterspire', array( 'Bigcommerce', 'admin_page' ) );
    }

    // Tied To Admin Submenu Link
	function admin_page() {
    	$options = self::get_options();
		$vendors = array(
			'http://beautomated.bigcommerce.com/',
			'http://katzwebservices.bigcommerce.com/',
		);
		require( 'admin-page.html.php' );
    }


	/************
	 Plugin Logic
	 ************/

	// Displays The Configuration Check
	function show_configuration_check() {

		// Configured
		if( self::$configured ) {
			$content = __( 'Your Bigcommerce API settings are configured properly.', 'wpinterspire' )
				. (
					( ! get_option( 'wpinterspire_productselect' ) )
					? __( ' However, your product list has not yet been built.', 'wpinterspire' )
					: __( ' When editing posts, look for the ', 'wpinterspire' )
						. '<img src="' . plugins_url( 'favicon.png', __FILE__ )
						. '" width="16" height="16" alt="' . __( 'Bigcommerce icon', 'wpinterspire') . '" />'
						. __( ' icon. Click it to add a product to your post or page.', 'wpinterspire' )
				);

		// Unconfigured
		} else {
			$content =  __( 'Your Bigcommerce API settings are <strong>not configured properly</strong>.', 'wpinterspire' ) ;
			if( self::$errors ) { $content .= '<br /><blockquote>' . implode( '<br />', self::$errors ) . '</blockquote>'; }
		}

		// Output
		echo self::make_notice_box( $content, ( ( self::$configured ) ? false : true ) );
	}

	// Give Thanks Footer Link
	function wp_footer() {
		$options = self::get_options();
		if( ! empty( $options->showlink ) && $options->showlink == 'yes' ) {
			echo '
				<p style="text-align:center;">
					This site uses the
					<a href="http://wordpress.org/extend/plugins/interspire-bigcommerce/">
					Bigcommerce WordPress Plugin</a>
				</p>
			';
		}
	}

	// Handle Shortcodes
	function shortcode( $atts, $content ) {
		$options = self::get_options();
		extract(
			shortcode_atts(
				array(
					'link' => '',
					'rel' => '',
					'target' => '',
					'nofollow' => '',
					'category' => '',
				), $atts
			)
		);

		// Handle Category Lookup
		if( $category ) {

			// Get Categories
			$categories = Bigcommerce_api::GetCategories();
			if( $categories ) {
				foreach( $categories as $cat ) {
	
					// Found Category Match
					if( $cat->name == $category ) {
						return self::DisplayProductsInCategory( (int) $cat->id );
					}
				}

			// No Category Match
			} else {
				$output = __(
					sprintf( "Unable to find a category match for: %s</p>", $category ),
					'wpinterspire'
				);
			}

			// Output
			return $output;
		}

		// Handle Link
		if( $rel ) { $rel = " rel='{$rel}'"; }
		if( $target ) { $target = " target='{$target}'"; };
		if( $nofollow ) { $nofollow = " nofollow='nofollow'"; };
		$extra = "{$rel}{$target}{$nofollow}";
		return "<a href='{$options->storepath}{$link}/'{$extra}>{$content}</a>";
	}

	// Outputs Products In a Category
	private function DisplayProductsInCategory( $catid ) {
		$output = '';

		// Find Products
		$products = Bigcommerce_api::GetProducts( false );
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
							$image = Bigcommerce_api::GetImage( $product->images[0]->link );
						}

						// Output The Row			
						$output .= self::DisplayProduct(
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
											? number_format( (int) $product->retail_price )
											: number_format( (int) $product->price )
										)
								),
								'condition' => (
									( (string) $product->is_condition_shown == 'false' )
										? 'Not specified'
										: (string) $product->condition
								),
								'availability' => (string) $product->availability,
								'link' => sanitize_title( (string) $product->name ),
								'image' => $image,
								'description' => (string) $product->description,
								'warranty' => (
									( (string) $product->warranty )
										? (string) $product->warranty
										: 'Not specified'
								),
								'rating_total' => (int) $product->rating_total,
								'rating_count' => (int) $product->rating_count,								
							)
						);
					}
				}
			}

		// No Product Matches
		} else {
			$output = __(
				sprintf( "Unable to find any products within: %s</p>", $category ),
				'wpinterspire'
			);
		}

		// Output
		return $output;
	}

	// Products Listings Row
	private function DisplayProduct( $data ) {
		$options = self::get_options();
		return "
			<div class='bigcommerce-row'>
				<h2 class='title {$data->is_featured}'>{$data->name}</h2>
				<div style='padding:10px 20px;'>
					<table style='border:0;'>
						<tbody>
							<tr>
								<td rowspan='9' style='border:0;'>
									<a href='{$data->image}' title='Click to enlarge'>
										<img src='{$data->image}'
											style='max-width:200px;max-height:200px;margin:10px;padding:10px;' />
									</a>
								</td>
							</tr>
							<tr>
								<th>Price</th>
								<td>{$data->price}</td>
							</tr>
							<tr>
								<th>Availibility</th>
								<td>{$data->availability}</td>
							</tr>
							<tr>
								<th>Condition</th>
								<td>{$data->condition}</td>
							</tr>
							<tr>
								<th>SKU</th>
								<td>{$data->sku}</td>
							</tr>
							<tr>
								<th>Warranty</th>
								<td>{$data->warranty}</td>
							</tr>
							<tr>
								<th>Rating</th>
								<td>
									{$data->rating_total}
									(from {$data->rating_count} ratings)
								</td>
							</tr>
							<tr>
								<th></th>
								<td>
									<a href='{$options->storepath}{$data->link}/'
										title='View the main store page'>
										More Information / Buy Now
									</a>
								</td>
							</tr>
						</tbody>
					</table>
					<div style='overflow:auto;max-height:100px;padding:5px 10px;'>
						{$data->description}
					</div>
				</div>
			</div>
		";
	}


	/*****************
	 Generic Utilities
	 *****************/

	// Generic Notice Box Maker
    function make_notice_box( $content, $error=false ) {
        $output = '';
        if( ! $error ) {
        	$output .= '<div id="message" class="updated">';
        } else {
            $output .= '<div id="messgae" class="error">';
        }
        $output .= '<p>' . $content . '</p></div>';
        return $output;
    }

	// Sanitizes URL
	function MakeURLSafe( $val ) {
		$val = str_replace( '-', '%2d', $val );
		$val = str_replace( '+', '%2b', $val );
		$val = str_replace( '+', '%2b', $val );
		$val = str_replace( '/', '{47}', $val );
		$val = urlencode( $val );
		$val = str_replace( '+', '-', $val );
		return $val;
	}
}

?>