=== Klump WooCommerce Buy Now, Pay Later Plugin ===
Contributors: paywithklump, richienabuk
Donate link: null
Tags: klump pay by instalments, useklump, woocommerce, buy now pay later bnpl, payment gateway
Requires at least: 6.2
Tested up to: 6.6
Stable tag: 1.3.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Klump WooCommerce Buy Now, Pay Later plugin allows merchants to give their customers the option of purchasing an item or service and make payment in four instalments.

= Note =

This plugin is meant to be used by merchants in Nigeria.

= Suggestions / Feature Request =

If you have challenges using the plugin or suggestions or a new feature request, kindly reach out via the [contact form on our website](https://useklump.com/contact) or send us an email at support@useklump.com

== Installation ==

= Requirements =
Ensure the following plugins are already installed on your site:
*   [WooCommerce](https://wordpress.org/plugins/woocommerce/)

= Automatic Installation =
*   Login to WordPress admin dashboard
*   Go to __WordPress Admin__ > __Plugins__ > __Add New__ from the left-hand menu.
*   In the search box type __Klump WooCommerce Payment Gateway__.
*   Click on __Install Now__ on __Klump WooCommerce Payment Gateway__ to install the plugin on your website.
*   Confirm the installation
*   After successful installation, __Activate__ the plugin.
*   Go to __WooCommerce__ > __Settings__ from the left-hand menu
*   Click on the __Payments__ tab
*   Click on the __Pay with Klump__ link from the available payment options
*   Enter your parameters accordingly and click save.

= Manual Installation =
*   [Download](https://downloads.wordpress.org/plugin/klump-wc-payment-gateway.zip) the plugin zip file
*   Login to WordPress admin dashboard
*   Go to __WordPress Admin__ > __Plugins__ > __Add New__ from the left-hand menu.
*   Click on the "Upload" option, then click "Choose File" to select the zip file you downloaded. Click "OK" and "Install Now" to complete the installation.
*   After successful installation, __Activate__ the plugin.
*   Go to __WooCommerce__ > __Settings__ from the left-hand menu
*   Click on the __Payments__ tab
*   Click on the __Pay with Klump__ link from the available payment options
*   Enter your parameters accordingly and click save.

= Klump Configuration Reference =
*   __Enable/Disable__ - Check this checkbox to Enable "Pay with Klump" on your store's checkout page
*   __Title__ - This is what users will see on the checkout page. The default is "Pay in Instalments - Klump" but you can change this to better reflect how you communicate with your customers.
*   __Description__ - This controls the message that appears under the payment fields on the checkout page. You can change the default description to describe in your own words the benefit of Klump to your customers.
*   __Test Mode__ - Check this to enable test mode. Test mode requires test public and secret key and it allows you to see Klump in action before going live to start receiving real payments from your customers.
*   __API Keys__ - Klump requires public and private API keys for both test mode and live mode. You can obtain them from your Klump merchant dashboard.
*   __Webhook URL__ - To complete the order automatically, copy and paste the webhook link here on your Klump merchant dashboard.
*   __Enable Klump Ads__ - This shows your customers the breakdown of the payment stages if they want to use Klump before they start the process. It's nice to have but not required for Klump to work on your site.
*   __Disallow cancel order__ - Removes the cancel order button if enabled
*   __Autocomplete order__ - If enabled automatically resolves order from webhook call after payment. Note: It requires webhook to work for the most part.
*    Click on __Save Changes__ to update the settings.

= Troubleshooting =
If you do not find Klump on WooCommerce payments tab on settings page, please check and ensure the following:

*   __"Enable/Disable"__ checkbox is checked (enabled)
*   __API Keys__ are supplied and are correct

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

*   A Klump merchant account—use an existing account or [create an account here](https://merchant.useklump.com/sign-up)
*   An active [WooCommerce installation](https://docs.woocommerce.com/document/installing-uninstalling-woocommerce/)
*   A valid [SSL Certificate](https://docs.woocommerce.com/document/ssl-and-https/)

== Screenshots ==

1. Klump plugin download or installed not activated
2. Activated Klump plugin
3. WooCommerce Payments tab - Klump settings page
4. Save Klump settings

== Changelog ==

= 1.0.0 - April 20, 2022 =
*   First release

= 1.0.1 - April 25, 2022 =
*   Update installation guide

= 1.0.2 - May 4, 2022  =
*   Add logo & update screenshots

= 1.0.3 - May 4, 2022  =
*   Update screenshots

= 1.0.4 - May 30, 2022  =
*   Fix issue with checkout

= 1.0.5 - June 21, 2022  =
*   Typo fixes

= 1.1.0 - December 1, 2022  =
*   Fix total amount does not match items amount error
*   Compatibility test

= 1.2.0 - January 4, 2024  =
*   Update for WP compatibility

= 1.3.0 - August 2, 2024  =
*   Add block feature
*   Update for WP compatibility

= 1.3.1 - August 5, 2024 =
*   Update compatibility requirements

= 1.3.2 - August 21, 2024 =
*   Fix shipping price error

= 1.3.3 - August 26, 2024 =
*   Prefill user form on checkout

= 1.3.4 - October 14, 2024 =
*   Add HPOS compatibility, plugin meta info

= 1.3.5 - October 24, 2024 =
*   Add coupon discount, bug fixes

== Upgrade Notice ==

= 1.0.1 =
*   Upgrade for installation guide and screenshot

= 1.0.2 =
*   Add logo & update screenshots

= 1.0.3 =
*   Update screenshots

= 1.0.4 =
*   Fix issue with checkout

= 1.0.5 =
*   Typo fixes

= 1.1.0 =
*   Fix total amount does not match items amount error
*   Compatibility test

= 1.2.0 =
*   Update for WP compatibility

= 1.3.0 =
*   Add block features
*   Update for WP compatibility

= 1.3.1 =
*   Update compatibility requirements

= 1.3.2 =
*   Fix shipping price error

= 1.3.3 =
*   Prefill user form on checkout

= 1.3.4 =
*   Add HPOS compatibility, plugin meta info

= 1.3.5 =
* Add coupon discount, bug fixes
