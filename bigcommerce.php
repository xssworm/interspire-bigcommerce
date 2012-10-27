<?php
/*
Plugin Name: Bigcommerce
Plugin URI: http://www.seodenver.com/interspire-bigcommerce-wordpress/
Description: Integrate Bigcommerce products into your WordPress pages and posts.
Author: Katz Web Services, & beAutomated
Version: 1.4
Author URI: http://www.katzwebservices.com
*/

// WP Hooks
add_action( 'init', array( 'WP_Interspire','init' ), 1 );
add_shortcode( 'Interspire', 'wpinterspire_shortcode' );
add_shortcode( 'interspire', 'wpinterspire_shortcode' );
add_shortcode( 'BigCommerce', 'wpinterspire_shortcode' );
add_shortcode( 'bigcommerce', 'wpinterspire_shortcode' );

// Plugin Class
class WP_Interspire {
	var $configured = false;

	// Notification for PHP5
	function php5() {
		echo self::make_notice_box(
			'Your server does not support PHP5, which is required to run the Bigcommerce plugin. '
			. 'Please contact your host and have them upgrade your server configuration.',
			'error'
		);
	}

	// Tied To Init Hook
	function init() {

		// Admin Area Only
		if( is_admin() ) {
			global $pagenow;
			$WPI = new WP_Interspire();

			// Only For Page/Post Editor
			if(
				in_array(
					basename($_SERVER['PHP_SELF']),
					array(
						'post.php', 'page.php', 'page-new.php', 'post-new.php'
					)
				) || (
					in_array(
						basename($_SERVER['PHP_SELF']),
						array('options-general.php')
					)
					&& isset($_REQUEST['page'])
					&& $_REQUEST['page'] == 'wpinterspire')
			) {
				$plugin_dir = basename( dirname( __FILE__ ) ) . 'languages';
				load_plugin_textdomain( 'wpinterspire', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

				// Only If Configured
				if($WPI->configured) {

					// Add Footer And Buttons
					add_action( 'admin_footer',  array( &$WPI, 'int_add_mce_popup' ) );
					add_action( 'media_buttons_context', array( &$WPI, 'add_interspire_button' ) );
				}
			}

			// Add Menu Item And Media Upload Tabs
			add_filter( 'media_upload_tabs', array( &$WPI, 'add_media_upload_tabs' ), 11 );
			add_action( 'media_upload_interspire', array( &$WPI,'menu_handle' ) );

		// Not Admin Area, Give Thanks Link
		} else {
			add_action( 'wp_footer', 'kws_givethanks_interspire' );
		}
	}

	function add_media_upload_tabs($tabs) {
		if(!isset($this->options)) {
			$this->get_options();
		}
		if(strpos($this->options['xmlpath'], 'bigcommerce')) {
			$newtab = array('interspire' => __('Bigcommerce', 'wpinterspire'));
		} else {
			$newtab = array('interspire' => __('Interspire', 'wpinterspire'));
		}
		return array_merge($tabs, $newtab);
	}
	
	function menu_handle() {
		return wp_iframe( array(&$this, 'media_process') );
	}
	
	function get_options() {
		$this->options = get_option('wpinterspire', array(
			'username' => '',
			'xmlpath' => '',
			'xmltoken' => '',
			'storepath' => '',
			'seourls' => '',
			'showlink' => ''
		));
			        
        // Set each setting...
        foreach($this->options as $key=> $value) {
        	$this->{$key} = $value;
        }
	}

	// Self Load
	function WP_Interspire() {
    	add_action('admin_menu', array(&$this, 'admin'));
	    add_filter('plugin_action_links', array(&$this, 'settings_link'), 10, 2 );
        add_action('admin_init', array(&$this, 'settings_init') );
    	
    	if(in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php')) || (in_array(basename($_SERVER['PHP_SELF']), array('options-general.php')) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpinterspire') || (in_array(basename($_SERVER['PHP_SELF']), array('media-upload.php')) && isset($_REQUEST['tab']) && $_REQUEST['tab'] == 'interspire'))  {   	
	    	$this->get_options();
	        $this->icon = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) ."/interspire-button.png";
	        if(in_array(basename($_SERVER['PHP_SELF']), array('options-general.php')) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpinterspire') {
	        	$this->CheckSettings();
	        }
			$this->BuildProductsSelect();
		}
    }


	function settings_init() {
        register_setting( 'wpinterspire_options', 'wpinterspire', array(&$this, 'sanitize_settings') );
    }
    
    function sanitize_settings($input) {
        return $input;
    }

    function settings_link( $links, $file ) {
        static $this_plugin;
        if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
        if ( $file == $this_plugin ) {
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '">' . __('Settings', 'wpinterspire') . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }

    function admin() {
        add_options_page('Bigcommerce', 'Bigcommerce', 'administrator', 'wpinterspire', array(&$this, 'admin_page'));  
    }

	function admin_page() {
		require( 'admin-page.html.php' );
    }

    function show_configuration_check($link = true) {
    	$options = $this->options;
    	
        if($this->configured) {
            $content = __('Your '); if($link) { $content .= '<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '">'; } $content .=  __('Interspire API settings', 'wpinterspire'); if($link) { $content .= '</a>'; } $content .= __(' are configured properly');
            
            if(empty($this->productsselect)) {
            	$content .= __(', however your product list has not yet been built. <strong><a href="?page=wpinterspire&amp;wpinterspirerebuild=all">Build it now</a></strong>.');
            } else {
             	$content .= __('. When editing posts, look for the <img src="'.$this->icon.'" width="14" height="14" alt="Add a Product" /> icon; click it to add a product to your post or page.');
             }
            echo $this->make_notice_box($content, 'success');
        } else {
            $content = 'Your '; if($link) { $content .= '<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '">'; } $content .=  __('Interspire API settings', 'wpinterspire') ; if($link) { $content .= '</a>'; } $content .= '  are <strong>not configured properly</strong>';
            if(is_array($this->settings_checked)) { $content .= '<br /><blockquote>'.$this->settings_checked['errormessage'].'</blockquote>'; }
            echo $this->make_notice_box($content, 'error');
        };
    }

    function make_notice_box($content, $type="error") {
        $output = '';
        if( $type != 'error' ) {
        	$output .= '<div id="message" class="updated">';
        } else {
            $output .= '<div id="messgae" class="error">';
        }
        $output .= '<p>' . $content . '</p></div>';
        return $output;
    }
 
	private function CheckSettings() {
		#$this->configured = $this->options['configured'] = true;
		#return;
		if(empty($this->username) || empty($this->xmltoken) || empty($this->username)) {
			$this->settings_checked = $this->configured = false;
			return false;
		}
		
		if(!isset($_REQUEST['updated']) && !isset($_REQUEST['settings-updated']) && !isset($_REQUEST['wpinterspirerebuild'])) { return $this->configured; }
			
		// Changed this from the APITest requestmethod, since it was so buggy.
		// We want this to be fast, so we call a negative productID so it doesn't
		// actually get a product.
		$xml = '<requesttype>products</requesttype>
		<requestmethod>GetProduct</requestmethod>
		<details>
			<productId>-1</productId>
		</details>';

		$xml = $this->GenerateRequest($xml);
		
		$response = $this->PostToRemoteFileAndGetResponse($xml);
		
		if($response && !empty($response)) {
			if($response->status == 'FAILED') {
				$this->settings_checked = array('errormessage' => $response->errormessage);
	        	$this->configured = false;
				return $this->settings_checked;
			} else {
	        	$this->configured = $this->options['configured'] = true;
				$this->settings_checked = true;
				update_option('wpinterspire', $this->options);
				return true;
			}
		}
		
		$this->configured = $this->options['configured'] = false;
		return false;
	}
	
	private function GenerateRequest($xml = '') {
		$request = "
		<xmlrequest>
			<username>{$this->username}</username>
			<usertoken>{$this->xmltoken}</usertoken>
			$xml
		</xmlrequest>";
		
		return $request;
	}
	
	private function MakeURLSafe($val)
	{                         
		$val = str_replace("-", "%2d", $val);
		$val = str_replace("+", "%2b", $val);
		$val = str_replace("+", "%2b", $val);
		$val = str_replace("/", "{47}", $val); 
		$val = urlencode($val);
		$val = str_replace("+", "-", $val);
		return $val;
	}

	private function MakeURL($url) {
		if ($this->seourls != 'no') {
			return $this->MakeURLSafe($url);
		} else {
			return sprintf("products.php?product=%s", $this->MakeURLSafe($url));
		}
	}

	
	private function BuildProductsSelect($rebuild = false, $products = array()) {
		// Added $this->configured in 1.0.3
		if($this->configured && (isset($_REQUEST['wpinterspirerebuild']) && ($_REQUEST['wpinterspirerebuild'] == 'select' || $_REQUEST['wpinterspirerebuild'] == 'all')) || $rebuild === true) {
			if(isset($_REQUEST['wpinterspirerebuild']) && $_REQUEST['wpinterspirerebuild'] != 'select') {
				$products = $this->GetProducts();
			}
			
			if(empty($products)) { $products = get_option('wpinterspire_products'); }
			
			$products = maybe_unserialize($products);
			if(!is_array($products) || empty($products) || empty($products['items'])) { return; }
			$output = '<select id="interspire_add_product_id">'."\n".'<option value="" disabled="disabled" selected="selected">Select a product&hellip;</option>'."\n";
		    foreach($products['items'] as $product) {
				if(!is_object($product['prodname']) &&  !empty($product['prodname'])) {
					$output .= '<option value="'.esc_html($this->MakeURL($product['prodname'])).'">'.esc_html($product['prodname']).'</option>'."\n";
				}
		    }
	        $output .= '</select>'."\n";
	        $this->productsselect = $output;
		    update_option('wpinterspire_productselect', $this->productsselect);
		} else {
			$output = get_option('wpinterspire_productselect');
			if(!$output) {
				$this->BuildProductsSelect(true);
			}
		}
		$this->productsselect = $output;
		return $output;
	}
		
	public function GetProducts($start = 0, $force_rebuild = true, $GetProducts = false, $i=0) {
		if($force_rebuild) {
			$xml = '<requesttype>products</requesttype>
			<requestmethod>GetProducts</requestmethod>
			<details>
				<start>'.(int)$start.'</start>
			</details>';
			
			$xml = $this->GenerateRequest($xml);
			
			$response = $this->PostToRemoteFileAndGetResponse($xml);
			
			if(empty($GetProducts)) { $GetProducts = array(); }
			
			if(empty($response->data->results->item)) { return false; }
			
			foreach($response->data->results->item as $item) {
				if(!is_object($item) || $item->prodvisible == '0') { continue; }
				
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

				if(!empty($this->storepath)) {
					$item->imagefiletiny = str_replace((string)$this->storepath, '', (string)$item->imagefiletiny);
					$item->imagefilethumb = str_replace((string)$this->storepath, '', (string)$item->imagefilethumb);
					$item->imagefilezoom = str_replace((string)$this->storepath, '', (string)$item->imagefilezoom);
					$item->imagefilestd = str_replace((string)$this->storepath, '', (string)$item->imagefilestd);
				}
				
				if(empty($item->productid)) { unset($item->productid); }
				if(empty($item->proddesc)) { unset($item->proddesc); }
				if(empty($item->prodcode)) { unset($item->prodcode); }
				if(empty($item->prodvariationid) && $item->prodvariationid !== '0') { unset($item->prodvariationid); }
				if(empty($item->imagedesc)) { unset($item->imagedesc); }
				if(empty($item->imagefiletiny)) { unset($item->imagefiletiny); }
				if(empty($item->imagefilethumb)) { unset($item->imagefilethumb); }
				if(empty($item->imagefilezoom)) { unset($item->imagefilezoom); }
				if(empty($item->imagefilestd)) { unset($item->imagefilestd); }
				
				$GetProducts['items'][$i] = (array)$item;
				$i++;
			}
		
			if((int)$response->data->end < (int)$response->data->numResults) {
				$this->GetProducts($response->data->end, true, $GetProducts, $i);
			} else {
				$GetProducts['status'] = (string)$response->status;
				$GetProducts['version'] = (int)$response->version;
				$GetProducts['numResults'] = (int)$response->data->numResults;
				asort($GetProducts['items']);
				$updated = update_option('wpinterspire_products', $GetProducts);
				return $GetProducts;
			}
		} else {
			return maybe_unserialize(get_option('wpinterspire_products'));
		}
	}

	
	private function PostToRemoteFileAndGetResponse($Vars="", $asobject = true)
	{	
		$Vars = 'xml='.urlencode($Vars);
		
		$Path = $this->xmlpath;
		
		$result = null;

		$args = array(
			'body' => $Vars,
			'sslverify' => is_ssl(),
			'timeout' => 600
		);
		$result = wp_remote_retrieve_body(wp_remote_post( $Path, $args ));

		if($asobject) {
			
			// Begin added 1.0.1
			try {
				$response = new SimpleXMLElement($result, LIBXML_NOCDATA);  // @simplexml_load_string($result);
			} catch (Exception $e) {
				return false;
			}			
			// End 1.0.1
					
			if(!is_object($response)) {
				return false;
			}
			return $response;
		} else {
			return empty($response) ? false : $response;
		}
	}

    public function add_interspire_button($context){
    	$out = '<a href="#TB_inline?width=640&inlineId=interspire_select_product" class="thickbox" title="' . __("Add Interspire Product(s)", 'wpinterspire') . '"><img src="'.$this->icon.'" width="14" height="14" alt="' . __("Add a Product", 'wpinterspire') . '" /></a>';
        return $context . $out;
    }

    function int_add_mce_popup(){
		require( 'mce-popup.js.php' );
		require( 'mce-popup.html.php' );
    }

    function media_process() {
		media_upload_header();
		

		$Products = $this->GetProducts(0, false);

		if(is_wp_error($Products) || !$Products) { 
			echo '<div class="tablenav"><form id="filter"><h3>The Bigcommerce plugin settings have not been properly configured.</h3></form></div>';
			return false;
		}
	
	$toggle_on  = __( 'Show' );
	$toggle_off = __( 'Hide' );
		$class = empty( $errors ) ? 'startclosed' : 'startopen';
	
	if ( !apply_filters( 'disable_captions', '' ) ) {
		$caption = '
			<tr>
				<th valign="top" scope="row" class="label">
					<span class="alignleft"><label for="caption">' . __('Image Caption') . '</label></span>
				</th>
				<td class="field"><input id="caption" name="caption" value="" type="text" /></td>
			</tr>
		';
	} else {
		$caption = '';
	}
	require( 'media.js.php' );
?>
<div class="tablenav">
<?php
	
	$i = 0;
	foreach ($Products['items'] as $key => $product ) {
		if(empty($product['imagefilestd'])) { continue; }
		$i++;
	}
		
	$_GET['paged'] = isset( $_GET['paged'] ) ? intval($_GET['paged']) : 0;
		if ( $_GET['paged'] < 1 )
			$_GET['paged'] = 1;
		$start = ( $_GET['paged'] - 1 ) * 10;
		if ( $start < 1 )
			$start = 0;
			
	$page_links = paginate_links( array(
		'base' => add_query_arg( 'paged', '%#%' ),
		'format' => '',
		'prev_text' => __('&laquo;'),
		'next_text' => __('&raquo;'),
		'total' => ceil($i / 10),
		'current' => $_GET['paged']
	));
		
	if ( $page_links ) {
		$page_links_form = "<form id='filter'>";
		$page_links_form .= "<div class='tablenav-pages'>{$page_links}</div>";
		$page_links_form .= "</form>";
	}

	$mediaitems = '<div class="alignleft actions">';
	
	$default_align = get_option('image_default_align');
	if ( empty($default_align) )
		$default_align = 'none';
		
		$postID = isset($_GET['post']) ? (int)$_GET['post'] : 0;
		$mediaitems .= '<form enctype="multipart/form-data" method="post" action="'.admin_url('media-upload.php?type=image&amp;tab=interspire&amp;post_id='.$postID).'" class="media-upload-form validate" id="library-form">
		<div id="media-items">
		';
		
		$i = 0;
		foreach ($Products['items'] as $key => $product ) {
			if(empty($product['imagefilestd'])) { continue; }
			$i++;
			if($i < $start || $i > ($start + 9)) { continue; }
			extract((array)$product);
			$mediaitems .= "<a class='toggle describe-toggle-on' href='#media-item-$productid'>$toggle_on</a>
	<a class='toggle describe-toggle-off' href='#media-item-$productid'>$toggle_off</a>".'
			<div id="media-item-'.$productid.'" class="media-item preloaded">
				<div style="width:40px; float:left;"><img src="'.$this->storepath.$imagefilethumb.'" class="pinkynail toggle" /></div>
				<div class="filename new">
					<span class="title">'.$prodname.'</span>
				</div>
	<table class="slidetoggle describe '.$class.'">
		<thead class="media-item-info" id="media-head-$post->ID">
		<tbody>
		<tr>
			<th valign="top" scope="row" class="label" style="width:130px;">
				<span class="alignleft"><label for="src-'.$productid.'">' . __('Image URL') . '</label></span>
				<span class="alignright"><abbr title="required" class="status_img required">*</abbr></span>
			</th>
			<td class="field"><input id="src-'.$productid.'" name="src" value="'.$this->storepath.$imagefilestd.'" type="text" aria-required="true" /></td>
		</tr>

		<tr>
			<th valign="top" scope="row" class="label">
				<span class="alignleft"><label for="title-'.$productid.'">' . __('Image Title') . '</label></span>
				<span class="alignright"><abbr title="required" class="required">*</abbr></span>
			</th>
			<td class="field"><input id="title-'.$productid.'" name="title" value="'.$prodname.'" type="text" aria-required="true" /></td>
		</tr>

		<tr>
			<th valign="top" scope="row" class="label">
				<span class="alignleft"><label for="alt-'.$productid.'">' . __('Alternate Text') . '</label></span>
			</th>
			<td class="field"><input id="alt-'.$productid.'" name="alt" value="'.$prodname.'" type="text" aria-required="true" />
			<p class="help">' . __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;') . '</p></td>
		</tr>
		' . $caption . '
		<tr class="align">
			<th valign="top" scope="row" class="label"><p><label for="align-'.$productid.'">' . __('Alignment') . '</label></p></th>
			<td class="field">
				<input name="align" id="align-none-'.$productid.'" value="none" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'none' ? ' checked="checked"' : '').' />
				<label for="align-none-'.$productid.'" class="align image-align-none-label">' . __('None') . '</label>
				<input name="align" id="align-left-'.$productid.'" value="left" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'left' ? ' checked="checked"' : '').' />
				<label for="align-left-'.$productid.'" class="align image-align-left-label">' . __('Left') . '</label>
				<input name="align" id="align-center-'.$productid.'" value="center" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'center' ? ' checked="checked"' : '').' />
				<label for="align-center-'.$productid.'" class="align image-align-center-label">' . __('Center') . '</label>
				<input name="align" id="align-right-'.$productid.'" value="right" onclick="addExtImage.align=\'align\'+this.value" type="radio"' . ($default_align == 'right' ? ' checked="checked"' : '').' />
				<label for="align-right-'.$productid.'" class="align image-align-right-label">' . __('Right') . '</label>
			</td>
		</tr>

		<tr>
			<th valign="top" scope="row" class="label">
				<span class="alignleft"><label for="url-'.$productid.'">' . __('Link Image To:') . '</label></span>
			</th>
			<td class="field"><input id="url-'.$productid.'" name="url" value="" type="text" /><br />
			<input type="hidden" id="productlink-'.$productid.'" value="'.esc_html($this->MakeURL($prodname)).'" />
			<button type="button" id="url-none-'.$productid.'" class="button url-none" value="">' . __('None') . '</button>
			<button type="button" id="url-product-'.$productid.'" class="button url-product" value="">' . __('Link to product') . '</button>
			<button type="button" id="url-src-'.$productid.'" class="button url-src" value="">' . __('Link to image') . '</button>';
			if(!empty($imagefilezoom)) {
			$mediaitems .= '<button type="button" id="url-large-'.$productid.'" class="button url-large" value="'.$imagefilezoom.'">' . __('Link to large image') . '</button>';
			}
			$mediaitems .= '<p class="help">' . __('Enter a link URL or click above for presets.') . '</p></td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="button" class="button" id="go_button-'.$productid.'" style="color:#bbb;" onclick="addExtImage.insert(this)" value="' . esc_attr__('Insert into Post') . '" />
			</td>
		</tr>
	</tbody></table>
			</div>
			
			';
		}
		$mediaitems .= '</div></form></div></div>';
		
		if($i == 0) {
			_e('<div class="updated"><p>Your store has no images.</p></div>');
		} else {
			_e($page_links_form.$mediaitems);
		}
		
	}


} 

// Outside the class so we don't have to load the whole object
function wpinterspire_shortcode($atts, $content) {
   	extract(shortcode_atts(array(
		'link' => '',
		'rel' => '',
		'target' => '',
		'nofollow' => ''
	), $atts));
	
	if(!strpos('http', $link)) { // If the link is shorter because $this->storepath is entered
		$options = get_option('wpinterspire');
		extract($options);
		if(empty($storepath)) {
			preg_match('/(.+)\/xml\.php/ism', $options['xmlpath'], $m);
			if(!empty($m)) {
				$storepath = $m[1];
			}
		}
		if(substr($link, 0, 1) == '/' && substr($storepath, -1, 1) == '/') { // If they start and end with // we strip one.
			$link = substr($link, 1);
		};
		if($seourls != 'no') {
			$link = $storepath.'/products/'.$link.'.html';
		} else {
			$link = $storepath.'/products.php?product='.$link;
		}
	}
	
	if(isset($rel) && $rel !='') {$nofollow=' rel="'.$rel.'"';}
	if($target) { $target = ' target="'.$target.'"'; };
	return '<a href="'.$link.'"'.$nofollow.$target.$nofollow.'>' . $content . '</a>';		   	
};

function kws_givethanks_interspire() {
	$options = get_option('wpinterspire');
	if(!empty($options['showlink'])) {
		
		mt_srand(crc32($_SERVER['REQUEST_URI'])); // Keep links the same on the same page
		
		$urls = array('http://www.seodenver.com/interspire-bigcommerce-wordpress/?ref=foot', 'http://wordpress.org/extend/plugins/interspire-bigcommerce/');
		$url = $urls[mt_rand(0, count($urls)-1)];
		
		if(strpos($options['xmlpath'], 'mybigcommerce')) {
			$links = array(
				'This blog uses the <a href="'.$url.'">Bigcommerce</a> WordPress Plugin',
				'We are using the <a href="'.$url.'">Bigcommerce</a> plugin for WordPress.',
				'Our WordPress blog is integrated with <a href="'.$url.'">Bigcommerce</a>.',
				'WordPress + <a href="'.$url.'">Bigcommerce</a> = awesome.'
			);
		} else {	
			$links = array(
				'This blog uses the <a href="'.$url.'">Interspire</a> WordPress Plugin',
				'We are using the <a href="'.$url.'">Interspire</a> plugin for WordPress.',
				'Our WordPress blog is integrated with <a href="'.$url.'">Interspire</a>.',
				'WordPress + <a href="'.$url.'">Interspire</a> = awesome.'
			);
		}
		$link = '<p style="text-align:center;">'.trim($links[mt_rand(0, count($links)-1)]).'</p>';

		echo apply_filters('interspire_thanks', $link);
		
		mt_srand(); // Make it random again.
	}
}

?>