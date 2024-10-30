<?php
declare(strict_types=1);

class KLP_WC_Payment_Gateway extends WC_Payment_Gateway
{

    // Show klump ads on checkout page
    public $show_klp_ads;

    // Toggle cancel order button on checkout page
    public $remove_cancel_order_button;

    // Toggle autocomplete order mode
    public $is_autocomplete_order_enabled;

    // test mode
    public $test_mode;

    // Credentials
    protected $public_key;
    protected $secret_key;

    public function __construct()
    {
        $this->id   = 'klump'; // payment gateway ID
        $this->icon = plugins_url('assets/images/klump.png', KLP_WC_PLUGIN_FILE); // payment gateway icon

        $this->has_fields         = false; // for custom credit card form
        $this->title              = 'Pay with Klump'; // vertical tab title
        $this->method_title       = 'Pay with Klump'; // payment method name
        $this->method_description = 'Use Klump to buy today, and Pay in Instalments over several months! Businesses accept payments and increase conversion rate and revenue. The Easy Life! Try it now.'; // payment method description

        $this->supports = ['products'];

        // load backend options fields
        $this->init_form_fields();

        // load the settings.
        $this->init_settings();
        $this->title                         = $this->get_option('title');
        $this->description                   = $this->get_option('description');
        $this->enabled                       = $this->get_option('enabled');
        $this->show_klp_ads                  = 'yes' === $this->get_option('show_klp_ads');
        $this->remove_cancel_order_button    = 'yes' === $this->get_option('remove_cancel_order_button');
        $this->is_autocomplete_order_enabled = 'yes' === $this->get_option('is_autocomplete_order_enabled');

        $this->test_mode = 'yes' === $this->get_option('test_mode');

        $this->secret_key = $this->test_mode ? $this->get_option('test_secret_key') : $this->get_option('secret_key');
        $this->public_key = $this->test_mode ? $this->get_option('test_public_key') : $this->get_option('public_key');

        add_action('admin_notices', [$this, 'admin_notices']);

        add_action('woocommerce_receipt_' . $this->id, [$this, 'receipt_page']);
        // verify payment route
        add_action('woocommerce_api_klp_wc_payment_gateway', [$this, 'klp_verify_payment']);

        // webhook route
        add_action('woocommerce_api_klp_wc_payment_webhook', [$this, 'klp_webhook']);

        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        }

        // Action hook to load custom JavaScript
        add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);

        // Check if the gateway can be used.
        if ( ! $this->supportCurrency()) {
            $this->enabled = 'no';
        }
    }

    public function is_active()
    {
        return $this->enabled === 'yes';
    }

    /**
     * Gateway settings fields and default values
     * @return void
     */
    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled'                       => [
                'title'       => __('Enable/Disable', 'klp-payments'),
                'label'       => __('Enable Klump Payment', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Enable Klump to allow your customers pay for your products by instalments.',
                    'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => true,
            ],
            'title'                         => [
                'title'       => __('Title', 'klp-payments'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'klp-payments'),
                'default'     => __('Pay in Instalments - Klump BNPL', 'klp-payments'),
                'desc_tip'    => true,
            ],
            'description'                   => [
                'title'       => __('Description', 'klp-payments'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.',
                    'klp-payments'),
                'default'     => __('Enjoy ease of payment by splitting cost and paying in instalments with Klump.',
                    'klp-payments'),
            ],
            'test_mode'                     => [
                'title'       => __('Test mode', 'klp-payments'),
                'label'       => __('Enable Test Mode', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Place the payment gateway in test mode using test API keys.', 'klp-payments'),
                'default'     => 'yes',
                'desc_tip'    => true,
            ],
            'test_public_key'               => [
                'title' => __('Test Public Key', 'klp-payments'),
                'type'  => 'text',
            ],
            'test_secret_key'               => [
                'title' => __('Test Secret Key', 'klp-payments'),
                'type'  => 'password',
            ],
            'public_key'                    => [
                'title' => __('Live Public Key', 'klp-payments'),
                'type'  => 'text',
            ],
            'secret_key'                    => [
                'title' => __('Live Private Key', 'klp-payments'),
                'type'  => 'password',
            ],
            'webhook'                       => [
                'title'       => __('Webhook URL', 'klp-payments'),
                'type'        => 'hidden',
                'description' => __('Please copy and paste this webhook URL on your API Keys & Webhooks tab of your settings page on your dashboard <strong><pre><code>' . WC()->api_request_url('klp_wc_payment_webhook') . '</code></pre></strong> (<a href="https://merchant.useklump.com/settings" target="_blank">Klump Account</a>)',
                    'klp-payments'),
            ],
            'show_klp_ads'                  => [
                'title'       => __('Enable Klump Ads', 'klp-payments'),
                'label'       => __('Show Klump Ads', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Show Klump ads to entice your customers to convert.', 'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => true,
            ],
            'remove_cancel_order_button'    => [
                'title'       => __('Disallow cancel order', 'klp-payments'),
                'label'       => __('Remove cancel order button', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Remove cancel order button', 'klp-payments'),
                'default'     => 'no',
                'desc_tip'    => false,
            ],
            'is_autocomplete_order_enabled' => [
                'title'       => __('Autocomplete order', 'klp-payments'),
                'label'       => __('Enable autocomplete orders', 'klp-payments'),
                'type'        => 'checkbox',
                'description' => __('Enable orders autocomplete', 'klp-payments'),
                'default'     => 'yes',
                'desc_tip'    => false,
            ],
        ];
    }

    /**
     * Admin notices
     * @return void
     */
    public function admin_notices(): void
    {
        if ('no' === $this->enabled) {
            return;
        }

        /**
         * Check if public key is provided
         */
        if ( ! $this->public_key || ! $this->secret_key) {
            $mode = ($this->test_mode) ? 'test' : 'live';
            echo '<div class="error"><p>';
            echo sprintf(
                'Provide your ' . $mode . ' public key and secret key <a href="%s">here</a> to be able to use the Pay with Klump Gateway plugin. If you don\'t have one, kindly sign up at <a href="https://useklump.com" target="_blank">https://useklump.com</a>.',
                admin_url('admin.php?page=wc-settings&tab=checkout&section=klump')
            );
            echo '</p></div>';
        }

    }

    /**
     * Load payment scripts
     * @return void
     */
    public function payment_scripts(): void
    {
        if ( ! is_checkout_pay_page()) {
            return;
        }

        // stop enqueue JS if payment gateway is disabled
        if ('no' === $this->enabled) {
            return;
        }

        // stop enqueue JS if API keys are not set
        if (empty($this->public_key)) {
            return;
        }

        $primary_key = $this->public_key;

        // stop enqueue JS if site without SSL
        if ( ! $this->test_mode && ! is_ssl()) {
            return;
        }

        $order_key = sanitize_text_field(urldecode($_GET['key']));
        $order_id  = absint(get_query_var('order-pay'));

        $order = wc_get_order($order_id);

        $payment_method = method_exists($order,
            'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

        if ($this->id !== $payment_method) {
            return;
        }

        // payment processor JS that allows to get a token
        wp_enqueue_script('klp_payment_js', KLP_WC_SDK_URL, [], null, true);

        wp_enqueue_script('klp_js', plugins_url('assets/js/klp-payment.js', KLP_WC_PLUGIN_FILE), [], null, true);

        $cb_url = WC()->api_request_url('KLP_WC_Payment_Gateway');

        $payment_params = [];

        if (get_query_var('order-pay')) {
            $email         = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : $order->billing_email;
            $amount        = $order->get_total();
            $txnref        = 'KLP_' . $order_id . '_' . time();
            $currency      = method_exists($order, 'get_currency') ? $order->get_currency() : $order->order_currency;
            $the_order_key = method_exists($order, 'get_order_key') ? $order->get_order_key() : $order->order_key;
            $firstname     = $order->get_billing_first_name();
            $lastname      = $order->get_billing_last_name();
            $phone         = $order->get_billing_phone();
            $shipping_fee  = $order->get_shipping_total();

            $order_items = [];

            // Calculate total coupon amount
            $discount = 0;
            foreach ($order->get_items('coupon') as $coupon_item) {
                $discount += $coupon_item->get_discount();
            }

            foreach ($order->get_items() as $key => $item) {
                $product   = wc_get_product($item->get_product_id());
                $image_url = wp_get_attachment_image_url($product->get_image_id(), 'full');

                $quantity   = $item->get_quantity();
                $unit_price = ($item->get_subtotal() + $item->get_subtotal_tax()) / $quantity;

                $order_item = [
                    'item_url'   => $product->get_permalink(),
                    'name'       => $item->get_name(),
                    'unit_price' => $unit_price,
                    'quantity'   => $quantity,
                ];

                if ($image_url) {
                    $order_item['image_url'] = $image_url;
                }

                $order_items[] = $order_item;
            }

            if ($the_order_key === $order_key) {
                $payment_params = compact(
                    'amount',
                    'email',
                    'txnref',
                    'primary_key',
                    'currency',
                    'firstname',
                    'lastname',
                    'cb_url',
                    'order_items',
                    'shipping_fee',
                    'order_id',
                    'phone',
                    'discount'
                );
            }

            $order->update_meta_data( '_klp_payment_txn_ref', $txnref );
            $order->save();
        }

        wp_localize_script('klp_js', 'klp_payment_params', $payment_params);
    }

    /**
     * Shows the payment page.
     *
     * @param $order_id
     */
    public function receipt_page($order_id): void
    {
        $order = wc_get_order($order_id);

        echo '<p>' . __('Thank you for your order, please click the button below to pay with Klump.',
                'klp-payments') . '</p>';

        echo '<div id="klump__checkout"></div>';

        if ($this->show_klp_ads) {
            echo '<div id="klump__ad">';
            echo '<input type="number" value="' . esc_attr($order->get_total()) . '" id="klump__price">';
            echo '<input type="text" value="' . esc_attr($this->public_key) . '" id="klump__merchant__public__key">';
            echo '<input type="text" value="' . esc_attr($order->get_currency()) . '" id="klump__currency">';
            echo '</div>';
        }

        if ( ! $this->remove_cancel_order_button) {
            echo '<a class="button cancel" href="' . esc_url($order->get_cancel_order_url()) . '">Cancel order';
            echo '</a>';
        }
    }

    /**
     * Process payment
     *
     * @param $order_id
     *
     * @return array
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);

        return [
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url(true),
        ];
    }

    /**
     * Verify payment
     * @return void
     */
    public function klp_verify_payment(): void
    {
        $reference = $order_id = null;
        if (isset($_REQUEST['reference'], $_REQUEST['order_id'])) {
            $reference = sanitize_text_field(urldecode($_REQUEST['reference']));
            $order_id  = sanitize_text_field(urldecode($_REQUEST['order_id']));
        }

        @ob_clean();

        if ($reference && $order_id) {
            $order = wc_get_order($order_id);

            if ( ! $order) {
                $failed_notice = 'Unable to process payment, kindly try again.';

                wc_add_notice($failed_notice, 'error');

                do_action('klp_wc_gateway_process_payment_error', $failed_notice, $order);

                wp_redirect(wc_get_page_permalink('cart'));
                exit;
            }

            $verifyUrl = KLP_WC_SDK_VERIFICATION_URL . $reference . '/verify';
            $args      = [
                'headers' => [
                    'klump-secret-key' => $this->secret_key,
                    'Content-Type'     => 'application/json',
                ],
                'timeout' => 60,
            ];

            $request = wp_remote_get($verifyUrl, $args);

            if ( ! is_wp_error($request) && 200 === wp_remote_retrieve_response_code($request)) {
                $klp_response = json_decode(wp_remote_retrieve_body($request), true);

                $klp_merchant_reference = $klp_response['data']['merchant_reference'];
                $klp_amount             = $klp_response['data']['amount'];
                $klp_currency           = $klp_response['data']['currency'];
                $klp_status             = $klp_response['data']['status'];

                $order_details     = explode('_', $klp_merchant_reference);
                $verified_order_id = (int)$order_details[1];

                if (('new' === $klp_status || 'successful' === $klp_status) && $verified_order_id === (int)$order_id) {
                    if (in_array($order->get_status(), ['processing', 'completed', 'on-hold'])) {
                        wp_redirect($this->get_return_url($order));
                        exit;
                    }

                    $order_total      = $order->get_total();
                    $order_currency   = $order->get_currency();
                    $currency_symbol  = get_woocommerce_currency_symbol($order_currency);
                    $amount_paid      = $klp_amount;
                    $payment_currency = strtoupper($klp_currency);
                    $gateway_symbol   = get_woocommerce_currency_symbol($payment_currency);

                    if ($payment_currency !== $order_currency || $amount_paid < $order_total) {
                        if ($amount_paid < $order_total) {
                            $order->update_status('on-hold', '');
                            $order->add_meta_data( '_transaction_id', $klp_merchant_reference, true );

                            $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.',
                                'klp-payments'), '<br />', '<br />', '<br />');
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note($notice, 1);

                            // Add Admin Order Note
                            $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Klump Payment Transaction Reference:</strong> %9$s',
                                'klp-payments'), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid,
                                $currency_symbol, $order_total, '<br />', $klp_merchant_reference);
                            $order->add_order_note($admin_order_note);

                            if (function_exists('wc_reduce_stock_levels')) {
                                wc_reduce_stock_levels($order_id);
                            }

                            wc_add_notice($notice, $notice_type);
                        }

                        if ($payment_currency !== $order_currency) {

                            $order->update_status('on-hold', '');

                            $order->update_meta_data( '_transaction_id', $klp_merchant_reference );

                            $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.',
                                'klp-payments'), '<br />', '<br />', '<br />');
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note($notice, 1);

                            // Add Admin Order Note
                            $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Klump Payment Transaction Reference:</strong> %9$s',
                                'klp-payments'), '<br />', '<br />', '<br />', $order_currency, $currency_symbol,
                                $payment_currency, $gateway_symbol, '<br />', $klp_merchant_reference);
                            $order->add_order_note($admin_order_note);

                            if (function_exists('wc_reduce_stock_levels')) {
                                wc_reduce_stock_levels($order_id);
                            }
                            wc_add_notice($notice, $notice_type);
                        }
                    } else {
                        $order->payment_complete($klp_merchant_reference);
                        $order->add_order_note(sprintf(__('Payment via Klump successful (Transaction Reference: %s)',
                            'klp-payments'), $klp_merchant_reference));

                        if ('successful' === $klp_status) {
                            if ($this->is_autocomplete_order_enabled) {
                                $order->update_status('completed');
                            } else {
                                $order->update_status('processing');
                            }
                        }
                    }

                    WC()->cart->empty_cart();
                } else {
                    $failed_notice = 'Unable to process payment, kindly try again.';

                    $order->update_status('failed', __($failed_notice, 'klp-payments'));

                    wc_add_notice($failed_notice, 'error');

                    do_action('klp_wc_gateway_process_payment_error', $failed_notice, $order);
                }

                $order->save();
            }

            wp_redirect($this->get_return_url($order));
            exit;
        }

        wp_redirect(wc_get_page_permalink('cart'));
        exit;
    }

    /**
     * Verify webhook information
     * @return void
     */
    public function klp_webhook(): void
    {
        if ( ! array_key_exists('HTTP_X_KLUMP_SIGNATURE', $_SERVER) || (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')) {
            exit;
        }

        $klump_event_payload = json_decode(file_get_contents('php://input'), true);

        if ($_SERVER['HTTP_X_KLUMP_SIGNATURE'] !== hash_hmac('sha512', file_get_contents('php://input'), $this->secret_key)) {
            exit;
        }

        if ('successful' === $klump_event_payload['data']['status']) {
            sleep(10);

            $klp_merchant_reference = $klump_event_payload['data']['merchant_reference'];
            $amount_paid            = $klump_event_payload['data']['amount'];
            $payment_currency       = strtoupper($klump_event_payload['data']['currency']);

            $order_details = explode('_', $klp_merchant_reference);
            $order_id      = (int)$order_details[1];
            $order         = wc_get_order($order_id);

            if ( ! $order) {
                exit;
            }

            $klp_txn_ref = $order->get_meta('_klp_payment_txn_ref', true);

            if ($klp_merchant_reference !== $klp_txn_ref) {
                exit;
            }

            http_response_code(200);

            if (in_array($order->get_status(), ['completed', 'on-hold'])) {
                exit;
            }

            $order_total     = $order->get_total();
            $order_currency  = $order->get_currency();
            $currency_symbol = get_woocommerce_currency_symbol($order_currency);
            $gateway_symbol  = get_woocommerce_currency_symbol($payment_currency);

            if ($payment_currency !== $order_currency || $amount_paid < $order_total) {
                if ($amount_paid < $order_total) {
                    $order->update_status('on-hold', '');

                    $order->add_meta_data( '_transaction_id', $klp_merchant_reference, true );

                    $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.',
                        'klp-payments'), '<br />', '<br />', '<br />');
                    $notice_type = 'notice';

                    // Add Customer Order Note
                    $order->add_order_note($notice, 1);

                    // Add Admin Order Note
                    $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Klump Payment Transaction Reference:</strong> %9$s',
                        'klp-payments'), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol,
                        $order_total, '<br />', $klp_merchant_reference);
                    $order->add_order_note($admin_order_note);

                    if (function_exists('wc_reduce_stock_levels')) {
                        wc_reduce_stock_levels($order_id);
                    }

                    wc_add_notice($notice, $notice_type);

                    WC()->cart->empty_cart();
                }

                if ($payment_currency !== $order_currency) {

                    $order->update_status('on-hold', '');

                    $order->update_meta_data( '_transaction_id', $klp_merchant_reference );

                    $notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.',
                        'klp-payments'), '<br />', '<br />', '<br />');
                    $notice_type = 'notice';

                    // Add Customer Order Note
                    $order->add_order_note($notice, 1);

                    // Add Admin Order Note
                    $admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Klump Payment Transaction Reference:</strong> %9$s',
                        'klp-payments'), '<br />', '<br />', '<br />', $order_currency, $currency_symbol,
                        $payment_currency, $gateway_symbol, '<br />', $klp_merchant_reference);
                    $order->add_order_note($admin_order_note);

                    if (function_exists('wc_reduce_stock_levels')) {
                        wc_reduce_stock_levels($order_id);
                    }
                    wc_add_notice($notice, $notice_type);
                    WC()->cart->empty_cart();
                }
            } else {
                $order->add_order_note(sprintf(__('Payment via Klump successful (Transaction Reference: %s)', 'klp-payments'), $klp_merchant_reference));

                if ($this->is_autocomplete_order_enabled) {
                    $order->update_status('completed');
                } else {
                    $order->payment_complete($klp_merchant_reference);
                }
            }
            $order->save();
        }
    }

    /**
     * Check if payment gateway supports currency
     * @return false
     */
    public function supportCurrency(): bool
    {
        if ( ! in_array(get_woocommerce_currency(), apply_filters('klump_wc_supported_currencies', ['NGN']), true)) {
            return false;
        }

        return true;
    }
}
