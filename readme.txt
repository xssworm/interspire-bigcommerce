=== Interspire & BigCommerce ===
Tags: ecommerce, interspire, bigcommerce, e-commerce, shop, cart, paypal, authorize, authorize.net, stock control, ecommerce, zencart, volition, shopsite, oscommerce, zen cart, prestashop, merchant
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: trunk
Contributors: katzwebdesign
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Interspire%20BigCommerce%20for%20WordPress&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Integrate the great Interspire Shopping Cart & BigCommerce hosted ecommerce shopping carts into WordPress.

== Description ==

<h3>Easily find and link to your Interspire or <a href="http://katzwebservicesinc.bigcommerce.com" target="_blank" rel="nofollow">BigCommerce</a> products from within WordPress</h3>

<strong>This plugin adds a button to the post/page editor that makes it easy to link to your products.</strong>

You want to spend your time writing the best content, not hunting for the link for the product you're blogging about. This plugin is simple to set up, and powerful. If you use WordPress and Interspire or BigCommerce, it's a must have.

<h3>Easily Embed & Link Your Store's Images</h3>
Now you can easily insert product images using the WordPress `Add an Image` button. Adding product images has never been easier! (see the Screenshots section for an example).

<h4>Interspire & BigCommerce for WordPress Features:</h4>
* Insert a link into your content for any product in your store
	* Select custom link text
	* Choose to open the link in the same window or a new window
	* Choose to "nofollow" the link, or other <code>rel</code> attribute
* Insert product images
	* Browse all of your store's products with images
	* Choose to link to the product page, the product image, or the full-sized product image
	* Add `alt`, `title`, alignment and captions -- just like with normal WordPress images!

<strong>What is BigCommerce?</strong><br />
BigCommerce is the #1 rated hosted e-commerce platform. If you want to have an e-commerce store without having to manage the server, security, and payments, BigCommerce is for you. <a href="http://katzwebservicesinc.bigcommerce.com" target="_blank" rel="nofollow">Visit BigCommerce.com to start your own online store today!</a>. You can also check out all the <a href="http://www.bigcommerce.com/livestores/" rel="nofollow">neat stores that use BigCommerce</a>.

<strong>What is Interspire Shopping Cart?</strong><br />
Interspire Shopping Cart is an all-in-one e-commerce and shopping cart software platform that includes absolutely everything you need to sell online and attract more customers using the power, reach and affordability of the Internet. <a href="http://www.interspire.com/240-2-3-8.html" target="_blank" rel="nofollow">Check out Interspire Shopping Cart today!</a>

== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Go to the plugin settings page (under Settings > Interspire / BigCommerce)
1. Enter in your store's username (not your email address) and the API information. (Instructions for finding your API Key are on the settings page on the right.
1. Click Save Changes.
1. If the settings are correct, it will say so.
1. When editing posts, look for the Interspire icon next to the media buttons; click it to select a product and add a link to your product inside your post or page.

== Screenshots ==

1. How the Add Products modal screen appears in the WordPress editor.
2. A button is added to the editor (next to the Media buttons). The product links appear as the `[interspire]` shortcode.
3. This shows the shortcode from above on the live site.
4. Once configured, easily add an image from your Interspire or BigCommerce shopping cart and link it to the product page. Adding product images has never been easier!

== Frequently Asked Questions == 

= Requirements = 
* __Requires PHP5__ for list management functionality.

If your web host does not support PHP5, please contact your host and see if they can upgrade your PHP version and activate `curl`; generally this can be done, and at no cost.

= What is the plugin license? =

* This plugin is released under a GPL license.

= Do I need a BigCommerce account or Interspire Shopping Cart to use this plugin? =
Yes, this plugin requires either a BigCommerce account or an Interspire Shopping Cart license. You can sign up for <a href="http://katzwebservicesinc.bigcommerce.com" target="_blank" rel="nofollow">a free 15 day trial of BigCommerce</a> or <a href="http://www.interspire.com/240-2-3-1.html" rel="nofollow">test drive Interspire Shopping Cart</a> in an online demo.

== Changelog ==

= 1.3 = 
* Removed `curl`-only data retrieval; now uses WordPress' built-in `wp_remote_post()` functionality
* Fixed some PHP notices on fresh installation and in the product image browser
* Fixed <a href="http://www.seodenver.com/interspire-bigcommerce-wordpress/#comment-307176267">issue reported</a> by <a href="http://www.brandywinejewelrysupply.com/">Brandywine Jewelry</a>  where "Screen Options" in WordPress' Add/Edit Post screen are permanently hidden. Thanks for reporting!

= 1.2.2 = 
* Fixed `Call to undefined function MakeURLSafe()` error

= 1.2.1 = 
* Fixed <a href="http://wordpress.org/support/topic/445905">bug #445905</a> issue where product list would not appear to have been generated.
* Fixed issue where plugin says settings are configured properly, but are not.
* Fixed issue where if a store had no images, the media browser's image tab would show pagination for an empty set of products
* Fixed pagination inside the media browser: the pages are now based on products with images instead of the number of products

= 1.2 =
* Added a tab to WordPress' `Add an Image` media tabs, allowing you to easily embed your product images and link to product pages or larger images.

= 1.1 =
__Note: Existing links will not be backward compatible with previous versions of this plugin!__

* Fixed the issues with previous versions - now lists will generate in much less time and will include unlimited numbers of products
* Added configuration option for whether or not to use SEO-friendly URLs
* BigCommerce users will now see `[bigcommerce]` shortcode instead of `[interspire]`
* Improved settings check: settings are only checked when they have changed

= 1.0.6 = 
* Whoops! This is what 1.0.5 was supposed to be.
* Fixed generation of product list
* Fixed editor button product insertion

= 1.0.4 = 
* Fixed bug where product lists would appear to have not been built, although they already had.
* Removed the dialog box when inserting product. That was for debug purposes.
* Added optional `Store Path` setting. When configured, the plugin doesn't send full URL to the editor, only the product page. Example: instead of <code>[interspire link=http://example.com/products/product.html]Anchor Text[/interspire]</code>, it will now be <code>[interspire link=/products/product.html]Anchor Text[/interspire]</code>.
* Added <code>[bigcommerce]</code> shortcode that works the same way as `[interspire]`; figured it made sense :-)
* Added option to give thanks by adding a link to the plugin page on your footer. (please do!!!)
* Speeded up the plugin a bit by removing a few calls to the database

= 1.0.3 = 
* Fixed issue with BigCommerce API authentication. Sorry for the problems everyone - it should be working now. Had been using Interspire-only method of basic checking of settings.
* Updated Insert Product form to prevent conflict with other plugins (Gravity Forms, for example).

= 1.0.2 =
* Quick fix, vital update:  fixes errors caused by 1.0.1. Sorry folks!
* Improves settings check to make sure configuration is done properly
* If settings have been configured but a products list hasn't been generated, the editor button will show a link to generate the list

= 1.0.1 = 
* Fixes possible `Uncaught exception 'Exception' with message 'String could not be parsed as XML` error
* Added error notice if PHP5 is not supported.

= 1.0 =
* Initial launch

== Upgrade Notice ==

= 1.3 = 
* Removed `curl`-only data retrieval; now uses WordPress' built-in `wp_remote_post()` functionality
* Fixed some PHP notices on fresh installation and in the product image browser
* Fixed <a href="http://www.seodenver.com/interspire-bigcommerce-wordpress/#comment-307176267">issue reported</a> by <a href="http://www.brandywinejewelrysupply.com/">Brandywine Jewelry</a>  where "Screen Options" in WordPress' Add/Edit Post screen are permanently hidden. Thanks for reporting!

= 1.2.2 = 
* Fixed `Call to undefined function MakeURLSafe()` error

= 1.2.1 = 
* Fixed <a href="http://wordpress.org/support/topic/445905">bug #445905</a> issue where product list would not appear to have been generated.
* Fixed issue where plugin says settings are configured properly, but are not.
* Fixed issue where if a store had no images, the media browser's image tab would show pagination for an empty set of products
* Fixed pagination inside the media browser: the pages are now based on products with images instead of the number of products

= 1.2 =
* Added a tab to WordPress' `Add an Image` media tabs, allowing you to easily embed your product images and link to product pages or larger images.

= 1.1 =
* Fixed the issues with previous versions - now lists will generate in much less time and will include unlimited numbers of products
* Added configuration option for whether or not to use SEO-friendly URLs
* BigCommerce users will now see `[bigcommerce]` shortcode instead of `[interspire]`
* Improved settings check: settings are only checked when they have changed

= 1.0.6 = 
* Whoops! This is what 1.0.5 was supposed to be.

= 1.0.5 =
* Fixed generation of product list
* Fixed editor button product insertion

= 1.0.4 = 
* Fixed bug where product lists would appear to have not been built, although they already had.
* Speeded up the plugin a bit by removing a few calls to the database

= 1.0.3 =
* Fixed issue with BigCommerce API authentication. Sorry for the problems everyone - it should be working now.
* Updated Insert Product form to prevent conflict with other plugins (Gravity Forms, for example).

= 1.0.2 =
* Very important if you upgraded to 1.0.1 without having configured your settings!
* Otherwise, it improves settings validation

= 1.0.1 = 
* Fixes possible `Uncaught exception 'Exception' with message 'String could not be parsed as XML` error
* Added error notice if PHP5 is not supported.

= 1.0 = 
* Blastoff!