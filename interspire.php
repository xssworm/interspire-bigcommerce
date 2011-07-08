<?php
/*
Plugin Name: Interspire & BigCommerce
Plugin URI: http://www.seodenver.com/interspire-bigcommerce-wordpress/
Description: Integrate Interspire and BigCommerce products into your WordPress website
Author: Katz Web Services, Inc.
Version: 1.2.2
Author URI: http://www.katzwebservices.com
*/

add_action('init', array('WP_Interspire','init'),1);

class WP_Interspire {
	
	var $configured = false;
	
	function php5() {
		echo self::make_notice_box('Your server does not support PHP5, which is required to run the Interspire &amp; BigCommerce plugin. Please contact your host and have them upgrade your server configuration.', 'error');
	}
	
	function init() {
		if(is_admin()) {
			global $pagenow;
			$WPI = new WP_Interspire();
			if(in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php')) || (in_array(basename($_SERVER['PHP_SELF']), array('options-general.php')) && isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpinterspire'))  {
				$plugin_dir = basename(dirname(__FILE__)).'languages';
				load_plugin_textdomain( 'wpinterspire', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
				
				if($WPI->configured) {
					wp_enqueue_style('media');
					add_action('admin_footer',  array(&$WPI, 'int_add_mce_popup'));
					add_action('media_buttons_context', array(&$WPI, 'add_interspire_button'));
				}
			}
			
			add_filter('media_upload_tabs', array(&$WPI, 'add_media_upload_tabs'), 11);
			add_action('media_upload_interspire', array(&$WPI,'menu_handle'));
			
		} else {
			add_action('wp_footer', 'kws_givethanks_interspire');
		}
	}
	
	function add_media_upload_tabs($tabs) {
		if(!isset($this->options)) {
			$this->get_options();
		}
		
		if(strpos($this->options['xmlpath'], 'bigcommerce')) {
			$newtab = array('interspire' => __('BigCommerce', 'wpinterspire'));
		} else {
			$newtab = array('interspire' => __('Interspire', 'wpinterspire'));
		}
		return array_merge($tabs, $newtab);
	}
	
	function menu_handle() {
		return wp_iframe( array(&$this, 'media_process') );
	}
	
	function get_options() {
		$this->options = get_option('wpinterspire', array());
			        
        // Set each setting...
        foreach($this->options as $key=> $value) {
        	$this->{$key} = $value;
        }
	}
	
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
        add_options_page('Interspire & BigCommerce', 'Interspire/BigCommerce', 'administrator', 'wpinterspire', array(&$this, 'admin_page'));  
    }
    
	function admin_page() {
        ?>
        <div class="wrap">
        <h2>Interspire &amp; BigCommerce for WordPress</h2>
        <div class="postbox-container" style="width:65%;">
            <div class="metabox-holder">	
                <div class="meta-box-sortables">
                    <form action="options.php" method="post">
                   <?php 
                   		$this->show_configuration_check(false);
                    	wp_nonce_field('update-options'); 
                        settings_fields('wpinterspire_options');
                   
                   		echo "
                   			<div>
                   				<input type='hidden' name='wpinterspire[seourls]' value='no' />
                   				<input type='hidden' name='wpinterspire[configured]' value='{$this->configured}' />
                   			</div>
                   		";
                       
                       	$rows[] = array(
                                'id' => 'wpinterspire_username',
                                'label' => __('Store Username', 'wpinterspire'),
                                'content' => "<input type='text' name='wpinterspire[username]' id='wpinterspire_username' value='".esc_attr($this->username)."' size='40' style='width:95%!important;' />",
                                'desc' => 'The username whose API credentials are below.'
                            );
                            
                        $rows[] = array(
                                'id' => 'wpinterspire_xmlpath',
                                'label' => __('XML Path', 'wpinterspire'),
                                'content' => "<input type='text' name='wpinterspire[xmlpath]' id='wpinterspire_xmlpath' value='".esc_attr($this->xmlpath)."' size='40' style='width:95%!important;' />",
                                'desc' => 'Your Store\'s XML Path (<code>http://www.example.com/xml.php</code>)'
                            );
                            
                        $rows[] = array(
                                'id' => 'wpinterspire_xmltoken',
                                'label' => __('XML Token', 'wpinterspire'),
                                'desc' => 'Your Store\'s XML Token',
                                'content' => "<input type='text' name='wpinterspire[xmltoken]' id='wpinterspire_xmltoken' value='".esc_attr($this->xmltoken)."' size='40' style='width:95%!important;' />"
                        );
                        
                        $rows[] = array(
                                'id' => 'wpinterspire_storepath',
                                'label' => __('Store Path (optional)', 'wpinterspire'),
                                'desc' => 'Your Store\'s URL, including <code>http://</code>. Entering this into your browser should take you to your home page. This is optional, and only to shorten the shortcode when linking to your products.',
                                'content' => "<input type='text' name='wpinterspire[storepath]' id='wpinterspire_storepath' value='".esc_attr($this->storepath)."' size='40' style='width:95%!important;' />"
                        );
                       
                        $checked = ($this->seourls !== 'no') ? ' checked=checked' : '';
                        
                        $rows[] = array(
                                'id' => 'wpinterspire_seourls',
                                'label' => __('SEO URLs (optional)', 'wpinterspire'),
                                'desc' => 'Please show support for this plugin by enabling.',
                                'content' => "<p><label for='wpinterspire_seourls'><input type='checkbox' name='wpinterspire[seourls]' id='wpinterspire_seourls' value='yes' $checked /> The store uses SEO-friendly URL structure <span class='howto'>If your product URLs do not look like <code>/products.php?product=product-name</code>, this should be checked.</span></label></p>"
                        ); 
                        
                        $checked = (!empty($this->showlink)) ? ' checked=checked' : '';
                        
                        $rows[] = array(
                                'id' => 'wpinterspire_showlink',
                                'label' => __('Give Thanks (optional)', 'wpinterspire'),
                                'desc' => 'Please show support for this plugin by enabling.',
                                'content' => "<p><label for='wpinterspire_showlink'><input type='checkbox' name='wpinterspire[showlink]' id='wpinterspire_showlink' $checked /> Help show the love by telling the world you use this plugin. A link will be added to your footer.</label></p>"
                        );
                                                
                        
                        if(!empty($this->productsselect)) {
                        	$rebuildText = "Your product list has been built:</p>".$this->productsselect."<p><strong>Has the list changed?</strong>";
                        	$rebuildLink = 'Re-build your products list';
                        } else {
                        	$rebuildText = "Your product list has not yet been built. ";
                        	$rebuildLink = 'Build your products list';
                        }
                       
                       $rows['unset'] = array(
                                'id' => 'wpinterspirerebuild',
                                'label' => __('Products', 'wpinterspire'),
                                'desc' => '',
                                'content' => "<p>$rebuildText <a href='".wp_nonce_url(admin_url('options-general.php?page=wpinterspire&amp;wpinterspirerebuild=all'), 'rebuild')."' class='button'>$rebuildLink</a><br /><small>Note: this may take a long time, depending on the size of your products list.</small></p>"
                        	);	
						                                
                        $this->postbox('wpinterspiresettings',__('Store Settings', 'wpinterspire'), $this->form_table($rows), false);
                         
                    ?>
                        

                        <input type="hidden" name="page_options" value="<?php foreach($rows as $row) { $output .= $row['id'].','; } echo substr($output, 0, -1);?>" />
                        <input type="hidden" name="action" value="update" />
                        <p class="submit">
                        <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes', 'wpinterspire') ?>" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <div class="postbox-container" style="width:34%;">
            <div class="metabox-holder">	
                <div class="meta-box-sortables">
                <?php $this->postbox('wpinterspirehelp',__('Configuring This Plugin', 'wpinterspire'), $this->configuration(), true);  ?>
                </div>
            </div>
        </div>
        
    </div>
    <?php   
    }
    
    function configuration() { 
	$html = <<<EOD
	<h4>Finding your XML Path and XML Token</h4>
	<p>Find your API settings at <code>yourstore.com/admin/index.php?ToDo=viewUsers</code>; click Edit next to your username;check the "Yes, allow this user to use the XML API" checkbox.</p>
	<hr />
	<h4>This plugin requires Interspire or BigCommerce accounts.</h4>
	<p><strong>What is BigCommerce?</strong><br />
	BigCommerce is the #1 rated hosted e-commerce platform. If you want to have an e-commerce store without having to manage the server, security, and payments, BigCommerce is for you. <a href="http://katzwebservicesinc.bigcommerce.com" target="_blank">Visit BigCommerce.com to start your own online store today!</a>. You can also check out all the <a href="http://www.bigcommerce.com/livestores/">neat stores that use BigCommerce</a>.</p>
	<p><strong>What is Interspire Shopping Cart?</strong><br />
Interspire Shopping Cart is an all-in-one e-commerce and shopping cart software platform that includes absolutely everything you need to sell online and attract more customers using the power, reach and affordability of the Internet. <a href="http://www.interspire.com/240-2-3-8.html" target="_blank">Check out Interspire Shopping Cart today!</a></p>
EOD;
	return $html;
    }
    
    function show_configuration_check($link = true) {
    	$options = $this->options;
    	
        if(!function_exists('curl_init')) { // Added 1.2.2
            $content = __('Your server does not support <code>curl_init</code>. Please call your host and ask them to enable this functionality, which is required for this awesome plugin.', 'wpinterspire');
            echo $this->make_notice_box($content, 'error');
        } else {
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
    }

    function make_notice_box($content, $type="error") {
        $output = '';
        if($type!='error') { $output .= '<div style="background-color: rgb(255, 255, 224);border-color: rgb(230, 219, 85);-webkit-border-bottom-left-radius: 3px 3px;-webkit-border-bottom-right-radius: 3px 3px;-webkit-border-top-left-radius: 3px 3px;-webkit-border-top-right-radius: 3px 3px;border-style: solid;border-width: 1px;margin: 5px 0px 15px;padding: 0px 0.6em;">';
        } else {
            $output .= '<div style="background-color: rgb(255, 235, 232);border-color: rgb(204, 0, 0);-webkit-border-bottom-left-radius: 3px 3px;-webkit-border-bottom-right-radius: 3px 3px;-webkit-border-top-left-radius: 3px 3px;-webkit-border-top-right-radius: 3px 3px;border-style: solid;border-width: 1px;margin: 5px 0px 15px;padding: 0px 0.6em;">';
        }
        $output .= '<p style="line-height: 1; margin: 0.5em 0px; padding: 2px;">'.$content.'</div>';
        return($output);
    }
 
	private function CheckSettings() {

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
		
		if($response) {
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
			if(!is_array($products)) { return; }
			$output = '<select id="interspire_add_product_id"  style="width:90%;">'."\n".'<option value="" disabled="disabled" selected="selected">Select a product&hellip;</option>'."\n";
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
		$Vars = "xml=" .urlencode($Vars);
		
		$Path = $this->xmlpath;
		
		$result = null;

		if(function_exists("curl_exec")) {
			// Use CURL if it's available
			$ch = curl_init($Path);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if (ini_get('open_basedir') == '') {
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			}
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			
			// Had this backwards until 1.0.3
			if (!is_ssl()) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			} else {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			}

			if($Vars != "") {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $Vars);
			}
			$result = curl_exec($ch);
		}
		else {
			// Use fsockopen instead
			$Path = @parse_url($Path);
			if(!isset($Path['host']) || $Path['host'] == '') {
				return null;
			}
			if(!isset($Path['port'])) {
				$Path['port'] = 80;
			}
			if(!isset($Path['path'])) {
				$Path['path'] = '/';
			}
			if(isset($Path['query'])) {
				$Path['path'] .= "?".$Path['query'];
			}

			$fp = @fsockopen($Path['host'], $Path['port'], $errorNo, $error, 10);
			@stream_set_timeout($fp, 10);
			if(!$fp) {
				return null;
			}

			$headers = array();

			// If we have one or more variables, perform a post request
			if($Vars != '') {
				$headers[] = "POST ".$Path['path']." HTTP/1.0";
				$headers[] = "Content-Length: ".strlen($Vars);
			}
			// Otherwise, let's get.
			else {
				$headers[] = "GET ".$Path['path']." HTTP/1.0";
			}
			$headers[] = "Host: ".$Path['host'];
			$headers[] = "Connection: Close";

			if($Vars != '') {
				$headers[] = "\r\n".$Vars; // Extra CRLF to indicate the start of the data transmission
			}

			if(!@fwrite($fp, implode("\r\n", $headers))) {
				return false;
			}
			while(!@feof($fp)) {
				$result .= @fgets($fp, 12800);
			}
			@fclose($fp);

			// Strip off the headers. Content starts at a double CRLF.
			$result = explode("\r\n\r\n", $result, 2);
			$result = $result[1];
		}		
		
		if($asobject) {
			
			// Begin added 1.0.1
			try {
				$response = new SimpleXMLElement($result, LIBXML_NOCDATA);  // @simplexml_load_string($result);
			} catch (Exception $e) {

			}			
			// End 1.0.1
					
			if(!is_object($response)) {
				return false;
			}
			return $response;
		} else {
			return $result;
		}
	}
		           
    public function add_interspire_button($context){
    	$out = '<a href="#TB_inline?width=640&inlineId=interspire_select_product" class="thickbox" title="' . __("Add Interspire Product(s)", 'wpinterspire') . '"><img src="'.$this->icon.'" width="14" height="14" alt="' . __("Add a Product", 'wpinterspire') . '" /></a>';
        return $context . $out;
    }
    
    function int_add_mce_popup(){
        ?>
        <script>
            function InterspireInsertProduct(){
                var product_id = jQuery("#interspire_add_product_id").val();
                if(product_id == ""){
                    alert("<?php _e("The product you selected does not have a link. Try rebuilding your product list in settings.", "wpinterspire") ?>");
                    return;
                } else {
                	var link_product = ' link="' + product_id + '"';
                }

                var display_title = jQuery("#interspire_display_title").val();
                var link_target = '';
                var link_nofollow = '';
                <?php 
                // If the path to the store is set, we only need the end of the URL;
                // this is to de-clutter the editor
                if(!empty($this->storepath)) { ?>
                product_id = product_id.replace("<?php echo $this->storepath; ?>", '');
                <?php } ?>
                if(jQuery("#link_target").is(":checked")) { link_target = ' target="blank"'; }
                if(jQuery("#link_nofollow").is(":checked")) { link_nofollow = ' rel="nofollow"'; }
				
                var win = window.dialogArguments || opener || parent || top;
                var shortcodeName = '<?php echo preg_match('/mybigcommerce/ism', $this->xmlpath) ? 'bigcommerce' : 'interspire'; ?>';
				win.send_to_editor("["+shortcodeName+link_product+link_target+link_nofollow+"]"+display_title+"[/"+shortcodeName+"]");
            }
        </script>

        <div id="interspire_select_product" style="display:none;">
                <div id="media-upload">
                	<div class="media-upload-form type-form">
                	<h3 class="media-title"><?php _e("Insert a Product", "wpinterspire"); ?></h3>
                    </div>
                    <?php 
                    $this->BuildProductsSelect();
                   	if(empty($this->productsselect)) {
                   		echo '<p>Your settings are correct, however your product list has not been generated. (<em>This may take a while if you have lots of products.</em>)</p>
                   		<p><a href="' . admin_url( 'options-general.php?page=wpinterspire&wpinterspirerebuild=all' ) . '" class="button">Generate your list now</a></p>';
                   	} else { 
                   	?>
                   	        
                    <div id="media-items" style="width:auto; overflow:hidden;">
					<div class="media-item media-blank">
						<h4 class="media-sub-title"><?php _e("Select a product below to add it to your post or page.", "wpinterspire"); ?></h4>
						<table class="describe"><tbody>
							<tr>
								<th valign="top" scope="row" class="label" style="width:130px;">
									<span class="alignleft"><label for="interspire_display_title"><?php _e("Link Text", "wpinterspire"); ?></label></span>
								</th>
								<td class="field"><input type="text" id="interspire_display_title" size="100" style="width:90%;" />
							</tr>
							
							<tr>
								<th valign="top" scope="row" class="label">
									<span class="alignleft"><label for="interspire_add_product_id">Select the Product</label></span>
								</th>
								<td class="field">
								
                            <?php
                    			echo $this->productsselect;        
                            ?></td>
							</tr>
					
							<tr>
								<th valign="top" scope="row" class="label">
									<span class="alignleft"><label for="url">Additional options:</label></span>
								</th>
								<td class="field">
								<input type="checkbox" id="link_nofollow" /> <label for="link_nofollow"><?php _e("Nofollow the link", "wpinterspire"); ?></label><br />
		                        <input type="checkbox" id="link_target" /> <label for="link_target"><?php _e("Open link in a new window", "wpinterspire"); ?></label>
								</td>
							</tr>
						
							<tr>
								<td></td>
								<td>
									<input type="button" class="button-primary" value="Insert Product" onclick="InterspireInsertProduct();"/>&nbsp;&nbsp;&nbsp;
				                    <a class="button" style="color:#bbb;" href="#" title="Cancel" onclick="tb_remove(); return false;"><?php _e("Cancel", "wpinterspire"); ?></a>
								</td>
							</tr>
						
						</tbody></table>
					</div>
					</div>
					<?php } ?>
            </div>
        </div>

        <?php
    }
   
   
   
   // THANKS JOOST!
    function form_table($rows) {
        $content = '<table class="form-table" width="100%">';
        foreach ($rows as $row) {
            $content .= '<tr><th valign="top" scope="row" style="width:50%">';
            if (isset($row['id']) && $row['id'] != '')
                $content .= '<label for="'.$row['id'].'" style="font-weight:bold;">'.$row['label'].':</label>';
            else
                $content .= $row['label'];
            if (isset($row['desc']) && $row['desc'] != '')
                $content .= '<br/><small>'.$row['desc'].'</small>';
            $content .= '</th><td valign="top">';
            $content .= $row['content'];
            $content .= '</td></tr>'; 
        }
        $content .= '</table>';
        return $content;
    }

    function postbox($id, $title, $content, $padding=false) {
        ?>
            <div id="<?php echo $id; ?>" class="postbox">
                <div class="handlediv" title="Click to toggle"><br /></div>
                <h3 class="hndle"><span><?php echo $title; ?></span></h3>
                <div class="inside" <?php if($padding) { echo 'style="padding:10px; padding-top:0;"'; } ?>>
                    <?php echo $content; ?>
                </div>
            </div>
        <?php
    }
    
    function media_process() {
		media_upload_header();
		

		$Products = $this->GetProducts(0, false);

		if(is_wp_error($Products) || !$Products) { 
			echo '<div class="tablenav"><form id="filter"><h3>The Interspire &amp; BigCommerce plugin settings have not been properly configured.</h3></form></div>';
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

?>
<script type="text/javascript">
<!--
jQuery(function($){

	var mediaitems = $('.media-items');
	$('a.toggle').live('click', function(e) {
		e.preventDefault();
		$('a.toggle[href='+$(this).attr('href')+']:hidden').toggle();
		$(this).toggle();
		$('table.slidetoggle',$(this).attr('href')).slideToggle();
		
		addExtImage.getImageData($(this).attr('href'));
		
		return false;
	});
	
	$('input[name=src][id*=src]').live('blur', function() {
		var id = $(this).attr('id');
		id = id.replace('src-', '');
		addExtImage.getImageData($(this).parents('div.media-item'));
	});
	
	$('button.url-src').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-src-', '');
		$('#url-'+id).val(jQuery('#src-'+id).val());
		return false;
	});
	
	$('button.url-large').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-large-', '');
		$('#url-'+id).val('<?php echo $this->storepath; ?>'+jQuery(this).val());
		return false;
	});
	
	$('button.url-none').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-none-', '');
		$('#url-'+id).val('');
		return false;
	});
	
	$('button.url-product').live('click', function() {
		var id = $(this).attr('id');
		id = id.replace('url-product-', '');
		<?php		
		if($this->options['seourls'] == 'no') {
			echo 'var link = "'.$this->storepath.'/products.php?product="+$("#productlink-"+id).val();';
		} else {
			echo 'var link = "'.$this->storepath.'/products/"+$("#productlink-"+id).val()+".html";';
		}
		?>
		$('#url-'+id).val(link);
		return false;
	});
});

var addExtImage = {
	
		width : '',
		height : '',
		align : 'alignnone',
	
		insert : function(that) {
			var t = this, html, f = jQuery(that).parents('div.media-item'), cls, title = '', alt = '', caption = '';
			
			if ( '' == jQuery('input[name="src"]', f).val() || '' == t.width )
				return false;
	
			if ( jQuery('input[name="title"]', f).val() ) {
				title = jQuery('input[name="title"]', f).val().replace(/'/g, '&#039;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				title = ' title="'+title+'"';
			}
	
			if ( jQuery('input[name="alt"]', f).val() )
				alt = jQuery('input[name="alt"]', f).val().replace(/'/g, '&#039;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	
	<?php if ( ! apply_filters( 'disable_captions', '' ) ) { ?>
			if ( jQuery('input[name="caption"]', f).val() )
				caption = jQuery('input[name="caption"]', f).val().replace(/'/g, '&#039;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	<?php } ?>
	
			cls = caption ? '' : ' class="'+t.align+'"';
	
			html = '<img alt="'+alt+'" src="'+jQuery('input[name="src"]', f).val()+'"'+title+cls+' width="'+t.width+'" height="'+t.height+'" />';
	
			if ( jQuery('input[name="url"]', f).val() )
				html = '<a href="'+jQuery('input[name="url"]', f).val()+'">'+html+'</a>';
	
			if ( caption )
				html = '[caption id="" align="'+t.align+'" width="'+t.width+'" caption="'+caption+'"]'+html+'[/caption]';
	
			var win = window.dialogArguments || opener || parent || top;
			win.send_to_editor(html);
			return false;
		},
	
		resetImageData : function() {
			var t = addExtImage;
	
			t.width = t.height = '';
			jQuery('input[id*=go_button]', t.formEl).css('color','#bbb');
			if ( jQuery('input[name="src"]', t.formEl).val() == '' )
				jQuery('.status_img', t.formEl).html('*');
			else jQuery('.status_img', t.formEl).html('<img src="<?php echo esc_url( admin_url( 'images/no.png' ) ); ?>" alt="" />');
		},
	
		updateImageData : function() {
			var t = addExtImage;
			t.width = t.preloadImg.width;
			t.height = t.preloadImg.height;
			jQuery('input[id*=go_button]', t.formEl).css('color','#333');
			jQuery('.status_img', t.formEl).html('<img src="<?php echo esc_url( admin_url( 'images/yes.png' ) ); ?>" alt="" />');
		},
	
		getImageData : function(that) {
			var t = addExtImage, src = jQuery('input[name="src"]', that).val();
			
			t.formEl = jQuery(that);
			
			if ( ! src ) {
				t.resetImageData;
				return false;
			}
			jQuery('.status_img', t.formEl).html('<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />');
			t.preloadImg = new Image();
			t.preloadImg.onload = t.updateImageData;
			t.preloadImg.onerror = t.resetImageData;
			t.preloadImg.src = src;
		}
	}
-->
</script>
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

	$mediaitems .= '<div class="alignleft actions">';
	
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

add_shortcode('Interspire', 'wpinterspire_shortcode');
add_shortcode('interspire', 'wpinterspire_shortcode');
add_shortcode('BigCommerce', 'wpinterspire_shortcode');
add_shortcode('bigcommerce', 'wpinterspire_shortcode');


function kws_givethanks_interspire() {
	$options = get_option('wpinterspire');
	if(!empty($options['showlink'])) {
		
		mt_srand(crc32($_SERVER['REQUEST_URI'])); // Keep links the same on the same page
		
		$urls = array('http://www.seodenver.com/interspire-bigcommerce-wordpress/?ref=foot', 'http://wordpress.org/extend/plugins/interspire-bigcommerce/');
		$url = $urls[mt_rand(0, count($urls)-1)];
		
		if(strpos($options['xmlpath'], 'mybigcommerce')) {
			$links = array(
				'This blog uses the <a href="'.$url.'">BigCommerce</a> WordPress Plugin',
				'We are using the <a href="'.$url.'">BigCommerce</a> plugin for WordPress.',
				'Our WordPress blog is integrated with <a href="'.$url.'">BigCommerce</a>.',
				'WordPress + <a href="'.$url.'">BigCommerce</a> = awesome.'
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