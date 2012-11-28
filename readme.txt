=== Bigcommerce ===
Contributors: katzwebdesign, beautomated, seanconklin, randywsandberg
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Bigcommerce%20for%20WordPress&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: ecommerce, interspire, bigcommerce, e-commerce, shop, cart, paypal, authorize, authorize.net, stock control, ecommerce, zencart, volition, shopsite, oscommerce, zen cart, prestashop, merchant, big commerce
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 1.4
License: GPLv2

Integrate Bigcommerce hosted eCommerce shopping cart product images and links into WordPress.

== Description ==

You want to spend your time writing the best content, not hunting for the link or image for the product you're blogging about. This Plugin is powerful and simple to set up. It's a must have if you use WordPress and Bigcommerce.

<h3>Easily find and link to your Bigcommerce products from within WordPress</h3>

This Plugin adds a button to the post/page editor that makes it easy to link to your products.

*	Set custom link text.
*	Choose to open the link in the same window or a new window.
*	Choose to "nofollow" the link.

<h3>Easily embed your store's images</h3>

You can easily insert product images using the WordPress Add an Image button.

*	Browse all of your store's products with images.
*	Use the WordPress image editor tool to choose to link to the product page, the product image, or the full-sized product image.
*	Use the WordPress image editor tool to add alt, title, alignment and captions.

<h3>What is Bigcommerce?</h3>

Bigcommerce is the #1 rated hosted e-commerce platform. If you want to have an eCommerce store without having to manage the server, security, and payments, Bigcommerce is for you. Check out all the [neat stores that use Bigcommerce](http://www.bigcommerce.com/showcase/ "View over 25,000 successful stores").

== Installation ==

Update Instructions

1. Click to have the Plugin updated.
1. Click Bigcommerce on the administration sidebar menu. Click the Settings tab. Check to ensure that your settings are properly configured. Click to rebuild your products list.

New Automatic Installation

1. Log in to your blog and go to the Plugins page.
1. Click Add New button.
1. Search for Benchmark Email Lite.
1. Click Install Now link.
1. (sometimes required) Enter your FTP or FTPS username and password, as provided by your web host.
1. Click Activate Plugin link.
1. If you are creating a new Bigcommerce account, please click the `Visit Bigcommerce.com to start your own online store today!` link at the top of the Settings page to do so.
1. Obtain your Bigcommerce API Key by following the instructions adjacent to that field.
1. Back on your site, click Bigcommerce on the administration sidebar menu. Click the Settings tab. Check to ensure that your settings are properly configured. Click to rebuild your products list.
1. On any page or post, click on the Bigcommerce icon to insert product links, or click on the media library icon and the Bigcommerce tab to insert product images.

New Manual Installation

1. Download the Plugin and un-zip it.
1. Upload the `interspire-bigcommerce` folder to your `wp-content/plugins/` directory.
1. Activate the Plugin through the Plugins menu in WordPress.
1. If you are creating a new Bigcommerce account, please click the `Visit Bigcommerce.com to start your own online store today!` link at the top of the Settings page to do so.
1. Obtain your Bigcommerce API Key by following the instructions adjacent to that field.
1. Back on your site, click Bigcommerce on the administration sidebar menu. Click the Settings tab. Check to ensure that your settings are properly configured. Click to rebuild your products list.
1. On any page or post, click on the Bigcommerce icon to insert product links, or click on the media library icon and the Bigcommerce tab to insert product images.

== Screenshots ==

1. This is the Add Product popup, after clicking the Bigcommerce icon from the pages/posts editor.
2. This is the shortcode that gets created by the Add Product popup.
3. This is an example page or post showing a link to a Bigcommerce product page.
4. This is the Add Media popup Bigcommerce tab, to insert product images into the pages/posts editor.
5. This is the inserted product image within the pages/posts editor, which can be further adjusted using the WordPress image editor tool.
6. This is an example page or post showing a product image and link to a Bigcommerce product page.

== Frequently Asked Questions == 

= Requirements = 

* Requires PHP version 5. If your web host does not support PHP5, please contact your host and see if they can upgrade your PHP version.
* Activate `curl` if your web host doesn't already have it running. Generally this can be done at no cost.

= When should I rebuild my products list? =

* Rebuild your products list whenever you upgrade the Plugin, or whenever you add new products or change existing product names, links, or images within your store.

= What's coming up in the future? =

* Product image search by name.
* Caching of product images.
* Feel free to make suggestions on [the Support tab](http://wordpress.org/support/plugin/interspire-bigcommerce "the Support tab").

== Changelog ==

= 1.4 on 2012-11-27 =

* Fixed: New contributor, beAutomated, rewrote most of the Plugin to get everything up to date and working again.
* Updated: Removed the copy of the WP image editing, as that can be triggered following product image insert, utilizing the latest WordPress tools for doing so.
* Added: Support for the latest Bigcommerce API, powered by cURL.

= 1.3 on 2011-09-12 =

* Removed `curl`-only data retrieval; now uses WordPress' built-in `wp_remote_post()` functionality
* Fixed some PHP notices on fresh installation and in the product image browser
* Fixed <a href="http://www.seodenver.com/interspire-bigcommerce-wordpress/#comment-307176267">issue reported</a> by <a href="http://www.brandywinejewelrysupply.com/">Brandywine Jewelry</a>  where "Screen Options" in WordPress' Add/Edit Post screen are permanently hidden. Thanks for reporting!

= 1.2.2 2011-04-06 =

* Fixed `Call to undefined function MakeURLSafe()` error

= 1.2.1 on 2011-04-04 =

* Fixed <a href="http://wordpress.org/support/topic/445905">bug #445905</a> issue where product list would not appear to have been generated.
* Fixed issue where plugin says settings are configured properly, but are not.
* Fixed issue where if a store had no images, the media browser's image tab would show pagination for an empty set of products
* Fixed pagination inside the media browser: the pages are now based on products with images instead of the number of products

= 1.2 on 2011-01-14 =

* Added a tab to WordPress' `Add an Image` media tabs, allowing you to easily embed your product images and link to product pages or larger images.

= 1.1 on 2010-12-14 =

* Note: Existing links will not be backward compatible with previous versions of this plugin!
* Fixed the issues with previous versions - now lists will generate in much less time and will include unlimited numbers of products
* Added configuration option for whether or not to use SEO-friendly URLs
* Bigcommerce users will now see `[bigcommerce]` shortcode instead of `[interspire]`
* Improved settings check: settings are only checked when they have changed

= 1.0.6 on 2010-11-23 = 

* Whoops! This is what 1.0.5 was supposed to be.
* Fixed generation of product list
* Fixed editor button product insertion

= 1.0.4 on 2010-08-27 = 

* Fixed bug where product lists would appear to have not been built, although they already had.
* Removed the dialog box when inserting product. That was for debug purposes.
* Added optional `Store Path` setting. When configured, the plugin doesn't send full URL to the editor, only the product page. Example: instead of <code>[interspire link=http://example.com/products/product.html]Anchor Text[/interspire]</code>, it will now be <code>[interspire link=/products/product.html]Anchor Text[/interspire]</code>.
* Added <code>[bigcommerce]</code> shortcode that works the same way as `[interspire]`; figured it made sense :-)
* Added option to give thanks by adding a link to the plugin page on your footer. (please do!!!)
* Speeded up the plugin a bit by removing a few calls to the database

= 1.0.3 on 2010-08-16 =

* Fixed issue with Bigcommerce API authentication. Sorry for the problems everyone - it should be working now. Had been using Interspire-only method of basic checking of settings.
* Updated Insert Product form to prevent conflict with other plugins (Gravity Forms, for example).

= 1.0.2 on 2010-07-27 =

* Quick fix, vital update:  fixes errors caused by 1.0.1. Sorry folks!
* Improves settings check to make sure configuration is done properly
* If settings have been configured but a products list hasn't been generated, the editor button will show a link to generate the list

= 1.0.1 on 2010-07-26 =

* Fixes possible `Uncaught exception 'Exception' with message 'String could not be parsed as XML` error
* Added error notice if PHP5 is not supported.

= 1.0 on 2010-07-22 =

* Initial launch

== Upgrade Notice ==

= 1.4 =

* This Plugin has been essentially rewritten by beAutomated, in partnership with Katz Web Services.

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
* Bigcommerce users will now see `[bigcommerce]` shortcode instead of `[interspire]`
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

* Fixed issue with Bigcommerce API authentication. Sorry for the problems everyone - it should be working now.
* Updated Insert Product form to prevent conflict with other plugins (Gravity Forms, for example).

= 1.0.2 =

* Very important if you upgraded to 1.0.1 without having configured your settings!
* Otherwise, it improves settings validation

= 1.0.1 = 

* Fixes possible `Uncaught exception 'Exception' with message 'String could not be parsed as XML` error
* Added error notice if PHP5 is not supported.

= 1.0 = 

* Blastoff!
