=== Interspire & BigCommerce ===
Tags: ecommerce, interspire, bigcommerce, e-commerce, shop, cart, paypal, authorize, authorize.net, stock control, ecommerce, zencart, volition, shopsite, oscommerce, zen cart, prestashop, merchant
Requires at least: 2.8
Tested up to: 3.0
Stable tag: trunk
Contributors: katzwebdesign
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Interspire%20BigCommerce%20for%20WordPress&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8

Integrate the great Interspire Shopping Cart & BigCommerce hosted ecommerce shopping carts into WordPress.

== Description ==

<h3>Easily find and link to your Interspire or BigCommerce products from within WordPress</h3>

<h4>This plugin adds a button to the post/page editor that makes it easy to link to your products.</h4>

You want to spend your time writing the best content, not hunting for the link for the product you're blogging about. This plugin is simple to set up, and powerful. If you use WordPress and Interspire or BigCommerce, it's a must have.

<strong>What is BigCommerce?</strong><br />
BigCommerce is the #1 rated hosted e-commerce platform. If you want to have an e-commerce store without having to manage the server, security, and payments, BigCommerce is for you. <a href="http://www.bigcommerce.com/145-0-3-6.html" target="_blank">Visit BigCommerce.com to start your own online store today!</a>. You can also check out all the <a href="http://www.bigcommerce.com/livestores/">neat stores that use BigCommerce</a>.

<strong>What is Interspire Shopping Cart?</strong><br />
Interspire Shopping Cart is an all-in-one e-commerce and shopping cart software platform that includes absolutely everything you need to sell online and attract more customers using the power, reach and affordability of the Internet. <a href="http://www.interspire.com/240-2-3-8.html" target="_blank">Check out Interspire Shopping Cart today!</a>

<h4>Interspire & BigCommerce for WordPress Features:</h4>
* Insert a link into your content for any product in your store
	* Select custom link text
	* Choose to open the link in the same window or a new window
	* Choose to "nofollow" the link, or other <code>rel</code> attribute

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

== Frequently Asked Questions == 

= Requirements = 
* __Requires PHP5__ for list management functionality.

If your web host does not support PHP5, please contact your host and see if they can upgrade your PHP version and activate `curl`; generally this can be done, and at no cost.

= What is the plugin license? =

* This plugin is released under a GPL license.

= Do I need a BigCommerce account or Interspire Shopping Cart to use this plugin? =
Yes, this plugin requires either a BigCommerce account or an Interspire Shopping Cart license. You can sign up for <a href="http://www.bigcommerce.com/145-3.html" rel="nofollow">a free 15 day trial of BigCommerce</a> or <a href="http://www.interspire.com/240-2-3-1.html">test drive Interspire Shopping Cart</a> in an online demo.

== Changelog ==

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

= 1.0.2 =
* Very important if you upgraded to 1.0.1 without having configured your settings!
* Otherwise, it improves settings validation

= 1.0.1 = 
* Fixes possible `Uncaught exception 'Exception' with message 'String could not be parsed as XML` error
* Added error notice if PHP5 is not supported.

= 1.0 = 
* Blastoff!