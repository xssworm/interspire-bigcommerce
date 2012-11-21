<?php
/*
Plugin Name: Bigcommerce
Plugin URI: http://www.seodenver.com/interspire-bigcommerce-wordpress/
Description: Integrate Bigcommerce products into your WordPress pages and posts.
Author: Katz Web Services, & beAutomated
Version: 1.4
Author URI: http://www.katzwebservices.com
*/

// Includes
require_once( 'class.api.php' );

// WP Hooks - General
add_action( 'admin_init', array( 'Bigcommerce', 'admin_init' ) );
add_action( 'admin_menu', array( 'Bigcommerce', 'admin_menu' ) );
add_filter( 'plugin_action_links', array( 'Bigcommerce', 'plugin_action_links' ), 10, 2 );
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

		// Support For Localizations
		load_plugin_textdomain(
			'wpinterspire', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		// Only Continue For Page/Post Editor Or Settings Page
		if(
			! in_array(
				$pagenow, array(
					'options-general.php', 'post.php', 'page.php', 'page-new.php', 'post-new.php'
				)
			) 
		) { return; }
		if(
			$pagenow == 'options-general.php'
			&& isset( $_REQUEST['page'] )
			&& $_REQUEST['page'] != 'wpinterspire'
		) { return; }

		// Run Settings Check
		self::$configured = Bigcommerce_api::CheckSettings();

		// (Re)Build Products If Requested
		if (
			isset( $_REQUEST['wpinterspirerebuild'] )
			&& $_REQUEST['wpinterspirerebuild'] == 'all'
		) { Bigcommerce_api::BuildProductsSelect( true ); }
    }


	/***************
	 Media Importing
	 ***************/

	// Tied To WP Hook By The Same Name - Adds Tab To Media Popup
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
				title="' . __( 'Add Bigcommerce Product(s)', 'wpinterspire' ) . '">
			<img src="' . plugins_url( 'insert.png', __FILE__ ) . '" width="14" height="14"
				alt="' . __( "Add a Product", 'wpinterspire' ) . '" /></a>
		';
	}

	// Tied To WP Hook By The Same Name - Admin Area Footer
	function admin_footer() {
		$options = self::get_options();
		require( 'mce-popup.js.php' );
		require( 'mce-popup.html.php' );
	}

	// Presents Media Insertion Content
	function media_process() {
    	$options = self::get_options();

		// Get Products From Cache
		$Products = get_option( 'wpinterspire_products' );
		$Products = new SimpleXMLElement( $Products, LIBXML_NOCDATA );
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
			return false;
		}

		// Loop Products
		$images = array();
		foreach( $Products->product as $i => $product ) {

			// Skip Products Without Images
			if( ! isset( $product->images[0]->link ) ) { continue; }

			// Query Image Info
			$path = substr( $product->images[0]->link, 1 );
			$path = Bigcommerce_api::GetDetail( $path );
			$path = new SimpleXMLElement( $path, LIBXML_NOCDATA );

			// Skip Products Without Images
			if( ! isset( $product->images[0]->link ) ) { continue; }

			// Save Path
			$path = $path->image[0]->image_file;
			$path = $options->storepath . 'product_images/' . $path;
			$images[] = $path;
		}

		// Output
		$toggle_on  = __( 'Show', 'wpinterspire' );
		$toggle_off = __( 'Hide', 'wpinterspire' );
		$class = 'startclosed';
		$caption = ( ! apply_filters( 'disable_captions', '' ) )
			? '
				<tr>
					<th valign="top" scope="row" class="label">
						<span class="alignleft">
							<label for="caption">' . __( 'Image Caption' ) . '</label>
						</span>
					</th>
					<td class="field">
						<input id="caption" name="caption" value="" type="text" />
					</td>
				</tr>
			' : '';
		require( 'media.js.php' );
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
    function plugin_action_links( $links, $file ) {
        static $the_plugin;
        if( ! $the_plugin ) $the_plugin = plugin_basename(__FILE__);
        if ( $file == $the_plugin ) {
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '">'
            	. __( 'Settings', 'wpinterspire' ) . '</a>';
            array_unshift( $links, $settings_link );
        }
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
		if( self::$configured ) {
			$content = __( 'Your Bigcommerce API settings are configured properly.', 'wpinterspire' )
				. (
					( ! get_option( 'wpinterspire_productselect' ) )
					? __( ' However, your product list has not yet been built.', 'wpinterspire' )
					: __( ' When editing posts, look for the ', 'wpinterspire' )
						. '<img src="' . plugins_url( 'insert.png', __FILE__ )
						. '" width="14" height="14" alt="' . __( 'Add a Product', 'wpinterspire') . '" />'
						. __( ' icon. Click it to add a product to your post or page.', 'wpinterspire' )
				);
		} else {
			$content =  __( 'Your Bigcommerce API settings are <strong>not configured properly</strong>.', 'wpinterspire' ) ;
			if( self::$errors ) { $content .= '<br /><blockquote>' . implode( '<br />', self::$errors ) . '</blockquote>'; }
		}
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
					'nofollow' => ''
				), $atts
			)
		);
		if( $rel ) { $rel = " rel='{$rel}'"; }
		if( $target ) { $target = " target='{$target}'"; };
		if( $nofollow ) { $nofollow = " nofollow='nofollow'"; };
		return "<a href='{$options->storepath}{$link}/'{$rel}{$target}{$nofollow}>{$content}</a>";
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

} /* End Of Plugin Class */

?>