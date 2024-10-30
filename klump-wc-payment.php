<?php
declare(strict_types=1);

/**
 * Klump WC Buy Now, Pay Later
 *
 * @package           KlumpPayment
 * @author            Klump Developers
 * @copyright         2022 Klump Inc.
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Klump WC Buy Now, Pay Later
 * Plugin URI:        https://useklump.com/
 * Description:       Buy Now, Pay Later (BNPL) plugin for Klump.
 * Version:           1.3.5
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to:   9.1
 * Author:            Klump Developers
 * Author URI:        https://useklump.com/developers
 * Text Domain:       klp-payments
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined('ABSPATH')) {
    exit;
}

define('KLP_WC_PLUGIN_FILE', __FILE__);
define('KLP_WC_SDK_URL', 'https://js.useklump.com/klump.js');
define('KLP_WC_SDK_VERIFICATION_URL', 'https://api.useklump.com/v1/transactions/');

function klp_wc_payment_init()
{
    if ( ! class_exists('WC_Payment_Gateway')) {
        add_action('admin_notices', 'klp_wc_payment_wc_missing_notice');

        return;
    }

    require_once __DIR__ . '/includes/class-klp-wc-payment-gateway.php';

    add_filter('woocommerce_payment_gateways', 'klp_wc_add_payment_gateway', 99);

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'klp_wc_plugin_action_links');
}

add_action('plugins_loaded', 'klp_wc_payment_init', 99);

/**
 * Add settings link to plugin
 *
 * @param Array $links Existing links on the plugin page
 *
 * @return Array
 */
function klp_wc_plugin_action_links(array $links): array
{
    $klp_settings_url = esc_url(get_admin_url(null, 'admin.php?page=wc-settings&tab=checkout&section=klump'));
    array_unshift($links, "<a title='Klump Settings Page' href='$klp_settings_url'>Settings</a>");

    return $links;
}

/**
 * Display a notice if WooCommerce is not installed
 */
function klp_wc_payment_wc_missing_notice()
{
    echo '<div class="error"><p><strong>' . sprintf('Klump requires WooCommerce to be installed and active. Click %s to install WooCommerce.',
            '<a href="' . admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539') . '" class="thickbox open-plugin-details-modal">here</a>') . '</strong></p></div>';
}

/**
 * Add plugin to Woocommerce
 *
 * @param Array $gateways
 *
 * @return Array
 */
function klp_wc_add_payment_gateway(array $gateways): array
{
    $gateways[] = 'KLP_WC_Payment_Gateway';

    return $gateways;
}

/**
 * Registers WooCommerce Blocks integration.
 */
function klp_wc_payment_gateway_woocommerce_block_support()
{
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        require_once __DIR__ . '/includes/class-klp-wc-payment-gateway-blocks-support.php';

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            static function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                $payment_method_registry->register(new KLP_WC_Payment_Gateway_Blocks_Support());
            }
        );
    }
}

add_action('woocommerce_blocks_loaded', 'klp_wc_payment_gateway_woocommerce_block_support');

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );
