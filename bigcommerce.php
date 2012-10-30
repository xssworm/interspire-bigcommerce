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

// WP Hooks
add_action( 'init', array( 'Bigcommerce', 'init' ) );
add_action( 'wp_footer', array( 'Bigcommerce', 'wp_footer' ) );
add_action( 'admin_menu', array( 'Bigcommerce', 'admin' ) );
add_action( 'admin_init', array( 'Bigcommerce', 'settings_init' ) );
add_filter( 'plugin_action_links', array( 'Bigcommerce', 'settings_link' ), 10, 2 );

// Shortcodes (support for legacy too)
add_shortcode( 'Interspire', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'interspire', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'BigCommerce', array( 'Bigcommerce', 'shortcode' ) );
add_shortcode( 'bigcommerce', array( 'Bigcommerce', 'shortcode' ) );

// Plugin Class
class Bigcommerce {
	private static $configured = false;
	private static $errors = array();

	// Tied To Init Hook
	function init() {

		// Admin Area Only
		if( is_admin() ) {
			global $pagenow;

			// Only For Page/Post Editor
			if(
				in_array(
					basename( $_SERVER['PHP_SELF'] ),
					array(
						'post.php', 'page.php', 'page-new.php', 'post-new.php'
					)
				) || (
					in_array(
						basename( $_SERVER['PHP_SELF'] ),
						array( 'options-general.php' )
					)
					&& isset( $_REQUEST['page'] )
					&& $_REQUEST['page'] == 'wpinterspire')
			) {
				$plugin_dir = basename( dirname( __FILE__ ) ) . 'languages';
				load_plugin_textdomain( 'wpinterspire', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

				// Only If Configured
				if( self::$configured ) {

					// Add Footer And Buttons
					add_action( 'admin_footer',  array( 'Bigcommerce', 'int_add_mce_popup' ) );
					add_action( 'media_buttons_context', array( 'Bigcommerce', 'add_interspire_button' ) );
				}
			}

			// Add Menu Item And Media Upload Tabs
			add_filter( 'media_upload_tabs', array( 'Bigcommerce', 'add_media_upload_tabs' ), 11 );
			add_action( 'media_upload_interspire', array( 'Bigcommerce', 'menu_handle' ) );
		}
	}

	// Add Tab To Media Uploader
	function add_media_upload_tabs( $tabs ) {
		return array_merge( $tabs, array( 'interspire' => __( 'Bigcommerce', 'wpinterspire' ) ) );
	}

	// Add Menu Item For Processing Media
	function menu_handle() {
		return wp_iframe( array( 'Bigcommerce', 'media_process' ) );
	}

	// Gets Stored Settings, Failover To Defaults
	function get_options() {
		return ( object ) get_option(
			'wpinterspire', array(
				'username' => '',
				'xmlpath' => '',
				'xmltoken' => '',
				'storepath' => '',
				'seourls' => '',
				'showlink' => '',
			)
		);
	}

	// Handles Saving Of Settings
	function settings_init() {
        register_setting( 'wpinterspire_options', 'wpinterspire', array( 'Bigcommerce', 'sanitize_settings' ) );
    }
    function sanitize_settings( $input ) {
        return $input;
    }

	// Adds Settings Link
    function settings_link( $links, $file ) {
        static $the_plugin;
        if( ! $the_plugin ) $the_plugin = plugin_basename(__FILE__);
        if ( $file == $the_plugin ) {
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '">'
            	. __('Settings', 'wpinterspire') . '</a>';
            array_unshift( $links, $settings_link );
        }
        return $links;
    }

	// Adds Admin Submenu Page
    function admin() {
        add_options_page( 'Bigcommerce', 'Bigcommerce', 'administrator', 'wpinterspire', array( 'Bigcommerce', 'admin_page' ) );
    }
	function admin_page() {
    	$options = self::get_options();
		$vendors = array(
			'http://beautomated.bigcommerce.com/',
			'http://katzwebservices.bigcommerce.com/',
		);
		require( 'admin-page.html.php' );
    }

	// Displays The Configuration Check
	function show_configuration_check( ) {
		$options = self::get_options();
		if( self::$configured ) {
			$content = __('Your Bigcommerce API settings are configured properly.', 'wpinterspire' );
			if( ! get_option( 'wpinterspire_productselect' ) ) {
				$content .= __(' However, your product list has not yet been built.', 'wpinterspire' );
				$content .= '<strong><a href="?page=wpinterspire&amp;wpinterspirerebuild=all">'
					. __('Build it now', 'wpinterspire') . '</a></strong>';
			} else {
				$content .= __( ' When editing posts, look for the ', '')
					. '<img src="' . plugins_url( 'interspire-button.png', dirname( __FILE__ ) )
					. '" width="14" height="14" alt="' . __( 'Add a Product', 'wpinterspire') . '" />'
					. __( ' icon. Click it to add a product to your post or page.', 'wpinterspire' );
			}
		} else {
			$content =  __( 'Your Bigcommerce API settings are <strong>not configured properly</strong>.', 'wpinterspire' ) ;
			if( self::$errors ) { $content .= '<br /><blockquote>' . implode( '<br />', self::$errors ) . '</blockquote>'; }
		}
		echo self::make_notice_box( $content, ( ( self::$configured ) ? false : true ) );
	}

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

	// Checks Saved Settings
	private function CheckSettings() {
    	$options = self::get_options();
		if( empty( $options->username ) || empty( $options->xmltoken ) ) {
			self::$configured = false;
			return false;
		}
		if( ! isset( $_REQUEST['updated'] ) && ! isset( $_REQUEST['settings-updated'] ) && ! isset( $_REQUEST['wpinterspirerebuild'] ) ) {
			return self::$configured;
		}

		// Query Bigcommerce API	
		$response = Bigcommerce_api::get_products();

		// Handle Response
		if($response && !empty($response)) {

			// Handle Bad Response
			if($response->status == 'FAILED') {
				self::$errors[] = $response->errormessage;
	        	self::$configured = false;
				return $response->errormessage;
			}

			// Handle Good Response
			else {
	        	self::$configured = $options['configured'] = true;
				update_option( 'wpinterspire', $options );
				return true;
			}
		}
		self::$configured = $options['configured'] = false;
		return false;
	}

	private function MakeURLSafe( $val ) {
		$val = str_replace("-", "%2d", $val);
		$val = str_replace("+", "%2b", $val);
		$val = str_replace("+", "%2b", $val);
		$val = str_replace("/", "{47}", $val); 
		$val = urlencode($val);
		$val = str_replace("+", "-", $val);
		return $val;
	}

	private function MakeURL( $url ) {
		$options = self::get_options();
		if ( $options->seourls != 'no' ) {
			return self::MakeURLSafe( $url );
		} else {
			return sprintf( "products.php?product=%s", self::MakeURLSafe( $url ) );
		}
	}

	// Builds Select Box Of Products
	private function BuildProductsSelect( $rebuild = false, $products = array() ) {
		if(
			self::$configured && (
				isset( $_REQUEST['wpinterspirerebuild'] )
				&& ( $_REQUEST['wpinterspirerebuild'] == 'select' || $_REQUEST['wpinterspirerebuild'] == 'all' )
			) || $rebuild === true
		) {
			if(isset($_REQUEST['wpinterspirerebuild']) && $_REQUEST['wpinterspirerebuild'] != 'select') {
				$products = self::GetProducts();
			}
			if(empty($products)) { $products = get_option('wpinterspire_products'); }
			$products = maybe_unserialize($products);
			if(!is_array($products) || empty($products) || empty($products['items'])) { return; }
			$output = '<select id="interspire_add_product_id">'
				. "\n" . '<option value="" disabled="disabled" selected="selected">Select a product&hellip;</option>' . "\n";
		    foreach($products['items'] as $product) {
				if(!is_object($product['prodname']) &&  !empty($product['prodname'])) {
					$output .= '<option value="' . esc_html( self::MakeURL( $product['prodname'] ) ) . '">'
						. esc_html( $product['prodname'] ) . '</option>'."\n";
				}
		    }
	        $output .= '</select>'."\n";
		    update_option( 'wpinterspire_productselect', $output );
		} else {
			$output = get_option('wpinterspire_productselect');
			if( ! $output ) { self::BuildProductsSelect( true ); }
		}
		return $output;
	}

	// Get Products
	public function GetProducts( $start = 0, $force_rebuild = true, $GetProducts = false, $i=0 ) {
		$options = self::get_options();
		if( $force_rebuild ) {

			// Query Bigcommerce API
			$response = Bigcommerce_api::get_products();
			if(empty($GetProducts)) { $GetProducts = array(); }
			if(empty($response->data->results->item)) { return false; }

			// Loop Responses
			foreach( $response->data->results->item as $item ) {

				// Ensure Valid
				if( !is_object( $item ) || $item->prodvisible == '0' ) { continue; }

				// Clear Unnecessary Properties
				unset($item->currentinv);
				unset($item->prodlowinv);
				unset($item->prodvendorfeatured);
				unset($item->prodfeatured);
				unset($item->imageid);
				unset($item->imagedateadded);
				unset($item->imagedesc);
				unset($item->prodistaxable);
				unset($item->prodinvtrack);
				unset($item->imagefiletinysize);
				unset($item->imagefilethumbsize);
				unset($item->imagefilestdsize);
				unset($item->imagefilezoomsize);

				// Set Properties
				if( ! empty( $options->storepath ) ) {
					$item->imagefiletiny = str_replace((string)$options->storepath, '', (string)$item->imagefiletiny);
					$item->imagefilethumb = str_replace((string)$options->storepath, '', (string)$item->imagefilethumb);
					$item->imagefilezoom = str_replace((string)$options->storepath, '', (string)$item->imagefilezoom);
					$item->imagefilestd = str_replace((string)$options->storepath, '', (string)$item->imagefilestd);
				}
				if(empty($item->productid)) { unset($item->productid); }
				if(empty($item->proddesc)) { unset($item->proddesc); }
				if(empty($item->prodcode)) { unset($item->prodcode); }
				if(empty($item->prodvariationid) && $item->prodvariationid !== '0') { unset( $item->prodvariationid ); }
				if(empty($item->imagedesc)) { unset($item->imagedesc); }
				if(empty($item->imagefiletiny)) { unset($item->imagefiletiny); }
				if(empty($item->imagefilethumb)) { unset($item->imagefilethumb); }
				if(empty($item->imagefilezoom)) { unset($item->imagefilezoom); }
				if(empty($item->imagefilestd)) { unset($item->imagefilestd); }
				$GetProducts['items'][$i] = (array) $item;
				$i++;
			}
		
			if( (int)$response->data->end < (int)$response->data->numResults ) {
				self::GetProducts( $response->data->end, true, $GetProducts, $i );
			} else {
				$GetProducts['status'] = (string)$response->status;
				$GetProducts['version'] = (int)$response->version;
				$GetProducts['numResults'] = (int)$response->data->numResults;
				asort($GetProducts['items']);
				$updated = update_option('wpinterspire_products', $GetProducts);
				return $GetProducts;
			}
		} else {
			return maybe_unserialize( get_option( 'wpinterspire_products' ) );
		}
	}

    public function add_interspire_button($context){
    	$out = '<a href="#TB_inline?width=640&inlineId=interspire_select_product" class="thickbox" title="'
    		. __("Add Interspire Product(s)", 'wpinterspire')
    		. '"><img src="' . plugins_url( 'interspire-button.png', dirname( __FILE__ ) ) . '" width="14" height="14" alt="'
    		. __("Add a Product", 'wpinterspire')
    		. '" /></a>';
        return $context . $out;
    }

    function int_add_mce_popup(){
		$options = self::get_options();
		require( 'mce-popup.js.php' );
		require( 'mce-popup.html.php' );
    }

	function media_process() {
		$options = self::get_options();
		media_upload_header();
		$Products = self::GetProducts( 0, false );
		if( is_wp_error( $Products ) || ! $Products ) { 
			echo '
				<div class="tablenav">
					<form id="filter">
						<h3>The Bigcommerce plugin settings have not been properly configured.</h3>
					</form>
				</div>
			';
			return false;
		}
		$toggle_on  = __( 'Show' );
		$toggle_off = __( 'Hide' );
		$class = empty( $errors ) ? 'startclosed' : 'startopen';
		if( !apply_filters( 'disable_captions', '' ) ) {
			$caption = '
				<tr>
					<th valign="top" scope="row" class="label">
						<span class="alignleft"><label for="caption">' . __('Image Caption') . '</label></span>
					</th>
					<td class="field"><input id="caption" name="caption" value="" type="text" /></td>
				</tr>
			';
		} else { $caption = ''; }
		require( 'media.js.php' );
		require( 'media.html.php' );
	}

	// Give Thanks Footer Link
	function wp_footer() {
		$options = self::get_options();
		if( ! empty( $options['showlink'] ) && $options['showlink'] == 'yes' ) {
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
		if( ! strpos( 'http', $link ) ) {
			if( empty( $options->storepath ) ) {
				preg_match( '/(.+)\/xml\.php/ism', $options->xmlpath, $m );
				if( ! empty( $m ) ) {
					$options->storepath = $m[1];
				}
			}
			if( substr( $link, 0, 1 ) == '/' && substr( $options->storepath, -1, 1 ) == '/' ) {
				$link = substr( $link, 1 );
			};
			if( $options->seourls != 'no' ) {
				$link = $options->storepath . '/' . strtolower( $link ) . '/';
			} else {
				$link = $options->storepath . '/products.php?product=' . $link;
			}
			$link = str_replace( '//', '/', $link);
		}
		if( isset( $rel ) && $rel != '' ) { $nofollow = ' rel="' . $rel . '"'; }
		if( $target ) { $target = ' target="' . $target . '"'; };
		return '<a href="' . $link . '"' . $nofollow . $target . $nofollow . '>' . $content . '</a>';
	}

} /* End Of Plugin Class */

?>