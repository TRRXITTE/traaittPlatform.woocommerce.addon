<?php
/*
Plugin Name: traaittPlatform Woocommerce Gateway
Plugin URI:
Description: Extends WooCommerce by adding a traaittPlatform Gateway
Version: 3.0.0
Tested up to: 4.9.8
Author: mosu-forge, SerHack
Author URI: https://monerointegrations.com/
*/
// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('TRAAITTPLATFORM_GATEWAY_EXPLORER_URL', 'https://explorer.traaittplatform.lol');
define('TRAAITTPLATFORM_GATEWAY_ATOMIC_UNITS', 2);
define('TRAAITTPLATFORM_GATEWAY_ATOMIC_UNIT_THRESHOLD', 100); // Amount under in atomic units payment is valid
define('TRAAITTPLATFORM_GATEWAY_DIFFICULTY_TARGET', 30);

// Do not edit these constants
define('TRAAITTPLATFORM_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TRAAITTPLATFORM_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TRAAITTPLATFORM_GATEWAY_ATOMIC_UNITS_POW', pow(10, TRAAITTPLATFORM_GATEWAY_ATOMIC_UNITS));
define('TRAAITTPLATFORM_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.TRAAITTPLATFORM_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'traaittplatform_init', 1);
function traaittplatform_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-traaittplatform-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new traaittPlatform_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-traaittplatform-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'traaittplatform_gateway');
    function traaittplatform_gateway($methods) {
        $methods[] = 'traaittPlatform_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'traaittplatform_payment');
    function traaittplatform_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=traaittplatform_gateway_settings').'">'.__('Settings', 'traaittplatform_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'traaittplatform_cron_add_one_minute');
    function traaittplatform_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'traaittplatform_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'traaittplatform_activate_cron');
    function traaittplatform_activate_cron() {
        if(!wp_next_scheduled('traaittplatform_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'traaittplatform_update_event');
        }
    }

    add_action('traaittplatform_update_event', 'traaittplatform_update_event');
    function traaittplatform_update_event() {
        traaittPlatform_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.traaittPlatform_Gateway::get_id(), 'traaittplatform_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'traaittplatform_order_page');
    add_action('woocommerce_email_after_order_table', 'traaittplatform_order_email');

    function traaittplatform_order_confirm_page($order_id) {
        traaittPlatform_Gateway::customer_order_page($order_id);
    }
    function traaittplatform_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            traaittPlatform_Gateway::customer_order_page($order);
    }
    function traaittplatform_order_email($order) {
        traaittPlatform_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_traaittplatform_gateway_payment_details', 'traaittplatform_get_payment_details_ajax');
    function traaittplatform_get_payment_details_ajax() {
        traaittPlatform_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'traaittplatform_add_currency');
    function traaittplatform_add_currency($currencies) {
        $currencies['traaittPlatform'] = __('traaittPlatform', 'traaittplatform_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'traaittplatform_add_currency_symbol', 10, 2);
    function traaittplatform_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'traaittPlatform':
            $currency_symbol = 'ETRX';
            break;
        }
        return $currency_symbol;
    }

    if(traaittPlatform_Gateway::use_traaittplatform_price()) {

        // This filter will replace all prices with amount in traaittPlatform (live rates)
        add_filter('wc_price', 'traaittplatform_live_price_format', 10, 3);
        function traaittplatform_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return traaittPlatform_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'traaittplatform_order_item_price_format', 10, 3);
        function traaittplatform_order_item_price_format($price_html, $item, $order) {
            return traaittPlatform_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'traaittplatform_order_total_price_format', 10, 2);
        function traaittplatform_order_total_price_format($price_html, $order) {
            return traaittPlatform_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'traaittplatform_order_totals_price_format', 10, 3);
        function traaittplatform_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = traaittPlatform_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'traaittplatform_enqueue_scripts');
    function traaittplatform_enqueue_scripts() {
        if(traaittPlatform_Gateway::use_traaittplatform_price())
            wp_dequeue_script('wc-cart-fragments');
        if(traaittPlatform_Gateway::use_qr_code())
            wp_enqueue_script('traaittplatform-qr-code', TRAAITTPLATFORM_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');

        wp_enqueue_script('traaittplatform-clipboard-js', TRAAITTPLATFORM_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('traaittplatform-gateway', TRAAITTPLATFORM_GATEWAY_PLUGIN_URL.'assets/js/traaittplatform-gateway-order-page.js');
        wp_enqueue_style('traaittplatform-gateway', TRAAITTPLATFORM_GATEWAY_PLUGIN_URL.'assets/css/traaittplatform-gateway-order-page.css');
    }

    // [traaittplatform-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function traaittplatform_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = traaittPlatform_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.8f', $rate / 1e8);

        return "<span class=\"traaittplatform-price\">1 ETRX = $rate_formatted $currency</span>";
    }
    add_shortcode('traaittplatform-price', 'traaittplatform_price_func');


    // [traaittplatform-accepted-here]
    function traaittplatform_accepted_func() {
        return '<img src="'.TRAAITTPLATFORM_GATEWAY_PLUGIN_URL.'assets/images/traaittplatform-accepted-here.png" />';
    }
    add_shortcode('traaittplatform-accepted-here', 'traaittplatform_accepted_func');

}

register_deactivation_hook(__FILE__, 'traaittplatform_deactivate');
function traaittplatform_deactivate() {
    $timestamp = wp_next_scheduled('traaittplatform_update_event');
    wp_unschedule_event($timestamp, 'traaittplatform_update_event');
}

register_activation_hook(__FILE__, 'traaittplatform_install');
function traaittplatform_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "traaittplatform_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(64) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "traaittplatform_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(64) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "traaittplatform_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
