<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

final class KLP_WC_Payment_Gateway_Blocks_Support extends AbstractPaymentMethodType
{

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'klump';

    private $gateway;

    /**
     * Initializes the payment method type.
     */
    public function initialize()
    {
        $this->settings = get_option('woocommerce_klump_settings', []);

        $payment_gateways = WC()->payment_gateways()->payment_gateways();
        $this->gateway    = $payment_gateways['klump'];
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active()
    {
        return $this->gateway->is_active();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles()
    {
        wp_register_script(
            'klp-wc-payment-gateway-blocks-integration',
            plugins_url('/assets/js/blocks/checkout.js', KLP_WC_PLUGIN_FILE),
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities'
            ],
            null,
            true
        );

        return ['klp-wc-payment-gateway-blocks-integration'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        return [
            'title'       => 'Pay in Instalments',
            'description' => $this->gateway->method_description,
            'logo_urls'   => [$this->gateway->icon],
        ];
    }
}
