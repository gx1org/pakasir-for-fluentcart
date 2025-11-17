<?php
/**
 * Plugin Name: Pakasir for FluentCart
 * Plugin URI: https://pakasir.com
 * Description: Pakasir Payment Gateway (QRIS, Virtual Account, etc) for FluentCart. (compatible with Indonesia banks/e-wallets only)
 * Version: 1.0.0
 * Author: PT Geksa Eksplorasi Satu
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: pakasir-for-fluentcart
 * Requires Plugins: fluent-cart
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PAKASIR_FC_DIR', plugin_dir_path(__FILE__));
define('PAKASIR_FC_URL', plugin_dir_url(__FILE__));

require_once PAKASIR_FC_DIR . 'src/PakasirFcGateway.php';
require_once PAKASIR_FC_DIR . 'src/PakasirFcSetting.php';

/**
 * Register payment method for FluentCart (supports multiple registration hooks for compatibility)
 */
add_action('fluent_cart/register_payment_methods', function () {
    if (!function_exists('fluent_cart_api')) {
        return;
    }

    $gateway = new \PakasirFc\PakasirFcGateway();
    if (function_exists('fluent_cart_api') && method_exists(fluent_cart_api(), 'registerCustomPaymentMethod'))     {
        fluent_cart_api()->registerCustomPaymentMethod('pakasir', $gateway);
        return;
    }

});

/**
 * Add Settings link on plugins page
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=fluent-cart#/settings/payments') . '">' . __('Settings', 'pakasir-for-fluentcart') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

