<?php
/*
Plugin Name: Interspire & BigCommerce
Plugin URI: http://www.seodenver.com/interspire-bigcommerce-wordpress/
Description: Integrate Interspire and BigCommerce products into your WordPress content
Author: Katz Web Services, Inc.
Version: 1.0.2
Author URI: http://www.katzwebservices.com
*/

add_action('init', array('WP_Interspire','init'),1);

class WP_Interspire {
	
	public $configured = false;
	
	function php5() {
		echo self::make_notice_box('Your server does not support PHP5, which is required to run the Interspire &amp; BigCommerce plugin. Please contact your host and have them upgrade your server configuration.', 'error');
	}
	
	function init() {
		if(is_admin()) {
			$WPI = new WP_Interspire();

			if(in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php', 'wpinterspire'))) {
				$plugin_dir = basename(dirname(__FILE__)).'languages';
				load_plugin_textdomain( 'wpinterspire', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
				
				if($WPI->configured) {	
					wp_enqueue_style('media');
					add_action('admin_footer',  array(&$WPI, 'int_add_mce_popup'));
					add_action('media_buttons_context', array(&$WPI, 'add_interspire_button'));
					global $interspire_icon;
					$interspire_icon = $WPI->icon;
				}	
			}
			
			if(isset($_REQUEST['wpinterspirerebuild'])) {
				if($_REQUEST['wpinterspirerebuild'] == 'products' || $_REQUEST['wpinterspirerebuild'] == 'all') {
					$WPI->BuildProducts();
				}
				if($_REQUEST['wpinterspirerebuild'] == 'productsselect' || $_REQUEST['wpinterspirerebuild'] == 'all') {
					$WPI->BuildProductsSelect();
				}
			}
		}
	}
	
	function WP_Interspire() {
		add_action('admin_menu', array(&$this, 'admin'));
	    add_filter( 'plugin_action_links', array(&$this, 'settings_link'), 10, 2 );
        add_action('admin_init', array(&$this, 'settings_init') );
    	$this->options = get_option('wpinterspire', array());
        
        // Set each setting...
        foreach($this->options as $key=> $value) {
        	$this->{$key} = $value;
        }
        $this->icon = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) ."/interspire-button.png";
        
		if(isset($this->username) && isset($this->xmltoken) && !empty($this->username) && !empty($this->xmltoken)) {
			 // Lets do this check only once
	        $this->settings_checked = $this->CheckSettings();
	        
	        if($this->settings_checked === true && !is_array($this->settings_checked)) {
	        	$this->configured = true;
			}
	    }
        
        // and put this in a global too, so widgets can check it
        global $wpinterspire_settings_checked;
        $wpinterspire_settings_checked = $this->settings_checked;
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
                                'desc' => 'Your Store\'s XML Path (http://www.example.com/xml.php)'
                            );
                            
                        $rows[] = array(
                                'id' => 'wpinterspire_xmltoken',
                                'label' => __('XML Token', 'wpinterspire'),
                                'desc' => 'Your Store\'s XML Token',
                                'content' => "<input type='text' name='wpinterspire[xmltoken]' id='wpinterspire_xmltoken' value='".esc_attr($this->xmltoken)."' size='40' style='width:95%!important;' />"
                        );
                        
                        $list = get_option('wpinterspire');
                        if($list['productsselect']) {
                        	$rebuildText = "Your product list has been built. <strong>Has it changed?</strong>";
                        	$rebuildLink = 'Re-build your products list';
                        } else {
                        	$rebuildText = "Your product list has not yet been built. ";
                        	$rebuildLink = 'Build your products list';
                        }
                       
                       $rows[] = array(
                                'id' => 'wpinterspirerebuild',
                                'label' => __('Products', 'wpinterspire'),
                                'desc' => '',
                                'content' => "<p>$rebuildText <a href='?page=wpinterspire&amp;wpinterspirerebuild=all' class='button'>$rebuildLink</a><br /><small>Note: this may take a long time, depending on the size of your products list.</small></p>"
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
	BigCommerce is the #1 rated hosted e-commerce platform. If you want to have an e-commerce store without having to manage the server, security, and payments, BigCommerce is for you. <a href="http://www.bigcommerce.com/145-0-3-6.html" target="_blank">Visit BigCommerce.com to start your own online store today!</a>. You can also check out all the <a href="http://www.bigcommerce.com/livestores/">neat stores that use BigCommerce</a>.</p>
	<p><strong>What is Interspire Shopping Cart?</strong><br />
Interspire Shopping Cart is an all-in-one e-commerce and shopping cart software platform that includes absolutely everything you need to sell online and attract more customers using the power, reach and affordability of the Internet. <a href="http://www.interspire.com/240-2-3-8.html" target="_blank">Check out Interspire Shopping Cart today!</a></p>
EOD;
	return $html;
    }
    
    function show_configuration_check($link = true) {
    	global $interspire_icon;
    	$options = $this->options;
    	
        if(!function_exists('curl_init')) { // Added 1.2.2
            $content = __('Your server does not support <code>curl_init</code>. Please call your host and ask them to enable this functionality, which is required for this awesome plugin.', 'wpinterspire');
            echo $this->make_notice_box($content, 'error');
        } else {
            if($this->configured) {
                $content = __('Your '); if($link) { $content .= '<a href="' . admin_url( 'options-general.php?page=wpinterspire' ) . '">'; } $content .=  __('Interspire API settings', 'wpinterspire'); if($link) { $content .= '</a>'; } $content .= __(' are configured properly.');
                if(!isset($options['productsselect'])) {
                	$content .= __(', however your product list has not yet been built. <strong><a href="?page=wpinterspire&amp;wpinterspirerebuild=all">Build it now</a></strong>.');
                } else {
                 	$content .= __('When editing posts, look for the <img src="'.$this->icon.'" width="14" height="14" alt="Add a Product" /> icon; click it to add a product to your post or page.');
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
		
		$xml = '<requesttype>authentication</requesttype>
		<requestmethod>xmlapitest</requestmethod>
		<details></details>';

		$xml = $this->GenerateRequest($xml);
		
		$response = $this->PostToRemoteFileAndGetResponse($xml);		

		if($response) {
			if($response->status == 'FAILED') { 
				return array('errormessage' => $response->errormessage);
			} else {
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
	
	private function BuildProducts() {
		$options = $this->options;
		
		if((isset($_REQUEST['wpinterspirerebuild']) && $_REQUEST['wpinterspirerebuild'] == 'products' || $_REQUEST['wpinterspirerebuild'] == 'all') || !isset($options['productsselect'])) {
			$products = $this->GetProducts(false, true); // Force rebuild
			$products = $this->simplexml2array($products);
			unset($products['status'], $products['version']);
			
			asort($products['data']['results']);
			
			$i = 0;
			foreach($products['data']['results']['item'] as $product){
		    	$p = $this->GetProduct($product['productid']);
				$p = $this->simplexml2array($p);

		    	$products['data']['results']['item'][$i]['prodlink'] = $p['data']['prodlink'];
		    	
		    	// We want to unset some data in the array to make the data size smaller.
		    	unset($products['data']['results']['item'][$i]['prodinvtrack'], $products['data']['results']['item'][$i]['prodlowinv'], $products['data']['results']['item'][$i]['prodistaxable'], $products['data']['results']['item'][$i]['imagedateadded'], $products['data']['results']['item'][$i]['imageid'],$products['data']['results']['item'][$i]['prodvendorfeatured'], $products['data']['results']['item'][$i]['prodvariationid']);
		    	$i++;
		    }
		    
			$options['products'] = maybe_serialize($products);
			
			update_option('wpinterspire', $options);
		}
	}
	
	private function simplexml2array($xml) {
	   if (get_class($xml) == 'SimpleXMLElement') {
	       $attributes = $xml->attributes();
	       foreach($attributes as $k=>$v) {
	           if ($v) $a[$k] = (string) $v;
	       }
	       $x = $xml;
	       $xml = get_object_vars($xml);
	   }
	   if (is_array($xml)) {
	       if (count($xml) == 0) return (string) $x; // for CDATA
	       foreach($xml as $key=>$value) {
	           $r[$key] = $this->simplexml2array($value);
	       }
	       if (isset($a)) $r['@'] = $a;    // Attributes
	       return $r;
	   }
	   return (string) $xml;
	}
	
	private function BuildProductsSelect() {
		$options = $this->options;
		
		if((isset($_REQUEST['wpinterspirerebuild']) && $_REQUEST['wpinterspirerebuild'] == 'select' || $_REQUEST['wpinterspirerebuild'] == 'all') || !isset($options['productsselect'])) {
			$products = maybe_unserialize($options['products']);
			
			$output = '<select id="add_product_id"  style="width:90%;">';
		    foreach($products['data']['results']['item'] as $product){
		        $output .= '<option value="'.htmlentities($product['prodlink']).'">'.esc_html($product['prodname']).'</option>';
		    }
	        $output .= '</select>';
	        
	        $options['productsselect'] = $output;
	                                	
			update_option('wpinterspire', $options);
		}
	}
	
	public function GetProducts($options = array(), $force_rebuild = false) {
		if($force_rebuild) {
			$xml = '<requesttype>products</requesttype>
			<requestmethod>GetProducts</requestmethod>
			<details>
				<start>0</start>
			</details>';
			
			$xml = $this->GenerateRequest($xml);
			
			$response = $this->PostToRemoteFileAndGetResponse($xml);
		} else {
			$options = $this->options;
			$response = maybe_unserialize($options['products']);
		}
		return $response;
	}
	
	public function GetProduct($id = NULL) {		
		$xml = "<requesttype>products</requesttype>
		<requestmethod>GetProduct</requestmethod>
		<details>
			<productId>$id</productId>
		</details>";
		
		$xml = $this->GenerateRequest($xml);
		
		$response = $this->PostToRemoteFileAndGetResponse($xml);
		
		return $response;
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

			if (is_ssl()) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
    	global $interspire_icon;
        $out = '<a href="#TB_inline?inlineId=select_product" class="thickbox" title="' . __("Add Interspire Product(s)", 'wpinterspire') . '"><img src="'.$interspire_icon.'" width="14" height="14" alt="' . __("Add a Product", 'wpinterspire') . '" /></a>';
        return $context . $out;
    }
    
    function int_add_mce_popup(){
        ?>
        <script>
            function InsertProduct(){
                var product_id = jQuery("#add_product_id").val();
                if(product_id == ""){
                    alert("<?php _e("The product you selected does not have a link. Try rebuilding your product list in settings.", "wpinterspire") ?>");
                    return;
                }

                var display_title = jQuery("#display_title").val();
                
                var link_target = '';
                var link_nofollow = '';
                if(jQuery("#link_target").is(":checked")) { link_target = ' target=blank'; }
                if(jQuery("#link_nofollow").is(":checked")) { link_nofollow = ' rel=nofollow'; }

                var win = window.dialogArguments || opener || parent || top;
                win.send_to_editor("[interspire link=" + product_id + ""+link_target+link_nofollow+"]"+display_title+"[/interspire]");
            }
        </script>

        <div id="select_product" style="display:none; width:90%;">
                <div id="media-upload">
                	<div class="media-upload-form type-form">
                	<h3 class="media-title"><?php _e("Insert a Product", "wpinterspire"); ?></h3>
                    </div>
                    <?php 
                    $options = $this->options;
                   	if(empty($options['productsselect'])) { 
                   		echo '<p>Your settings are correct, however your product list has not been generated. (<em>This may take a while if you have lots of products.</em>)</p>
                   		<p><a href="' . admin_url( 'options-general.php?page=wpinterspire&wpinterspirerebuild=all' ) . '" class="button">Generate your list now</a></p>';
                   	} else { ?>
                            
                    <div id="media-items" style="width:auto; overflow:hidden;">
					<div class="media-item media-blank">
						<h4 class="media-sub-title"><?php _e("Select a product below to add it to your post or page.", "wpinterspire"); ?></h4>
						<table class="describe"><tbody>
							<tr>
								<th valign="top" scope="row" class="label" style="width:130px;">
									<span class="alignleft"><label for="display_title"><?php _e("Link Text", "wpinterspire"); ?></label></span>
								</th>
								<td class="field"><input type="text" id="display_title" size="100" style="width:90%;" />
							</tr>
							
							<tr>
								<th valign="top" scope="row" class="label">
									<span class="alignleft"><label for="add_product_id">Select the Product</label></span>
								</th>
								<td class="field">
								
                            <?php
                    			echo $options['productsselect'];        
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
									<input type="button" class="button-primary" value="Insert Product" onclick="InsertProduct();"/>&nbsp;&nbsp;&nbsp;
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
} 


// Outside the class so we don't have to load the whole object
function wpinterspire_shortcode($atts, $content) {
   	extract(shortcode_atts(array(
		'link' => '',
		'rel' => '',
		'target' => '',
	), $atts));
	
	if(isset($rel) && $rel !='') {$nofollow=' rel="'.$rel.'"';}
	if($target) { $target = ' target="'.$target.'"'; };
	return '<a href="'.$link.'"'.$nofollow.$target.$nofollow.'>' . $content . '</a>';		   	
};

add_shortcode('Interspire', 'wpinterspire_shortcode');
add_shortcode('interspire', 'wpinterspire_shortcode');

?>