# traaittPlatform Gateway for WooCommerce

## Features

* Payment validation done through `traaittService`.
* Validates payments with `cron`, so does not require users to stay on the order confirmation page for their order to validate.
* Order status updates are done through AJAX instead of Javascript page reloads.
* Customers can pay with multiple transactions and are notified as soon as transactions hit the mempool.
* Configurable block confirmations, from `0` for zero confirm to `60` for high ticket purchases.
* Live price updates every minute; total amount due is locked in after the order is placed for a configurable amount of time (default 60 minutes) so the price does not change after order has been made.
* Hooks into emails, order confirmation page, customer order history page, and admin order details page.
* View all payments received to your wallet with links to the blockchain explorer and associated orders.
* Optionally display all prices on your store in terms of traaittPlatform.
* Shortcodes! Display exchange rates in numerous currencies.

## Requirements

* traaittPlatform wallet to receive payments.
* [BCMath](http://php.net/manual/en/book.bc.php) - A PHP extension used for arbitrary precision maths

## Installing the plugin

* Download the plugin from the [releases page](https://github.com/traaittplatform/traaittplatform-woocommerce-gateway/releases) or clone with `git clone https://github.com/traaittplatform/traaittplatform-woocommerce-gateway.git`
* Unzip or place the `traaittplatform-woocommerce-gateway` folder in the `wp-content/plugins` directory.
* Activate "traaittPlatform Woocommerce Gateway" in your WordPress admin dashboard.
* It is highly recommended that you use native cronjobs instead of WordPress's "Poor Man's Cron" by adding `define('DISABLE_WP_CRON', true);` into your `wp-config.php` file and adding `* * * * * wget -q -O - https://yourstore.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1` to your crontab.

# Set-up traaittPlatform daemon and traaittService

* Root access to your webserver
* Latest [traaittPlatform-currency binaries](https://github.com/traaittplatform/traaittplatform/releases)

After downloading (or compiling) the traaittPlatform binaries on your server, run `traaittPlatformd` and `traaittService`. You can skip running `traaittPlatformd` by using a remote node with `traaittService` by adding `--daemon-address` and the address of a public node.

Note on security: using this option, while the most secure, requires you to run the traaittService program on your server. Best practice for this is to use a view-only wallet since otherwise your server would be running a hot-wallet and a security breach could allow hackers to empty your funds.

## Configuration

* `Enable / Disable` - Turn on or off traaittPlatform gateway. (Default: Disable)
* `Title` - Name of the payment gateway as displayed to the customer. (Default: traaittPlatform Gateway)
* `Discount for using traaittPlatform` - Percentage discount applied to orders for paying with traaittPlatform. Can also be negative to apply a surcharge. (Default: 0)
* `Order valid time` - Number of seconds after order is placed that the transaction must be seen in the mempool. (Default: 3600 [1 hour])
* `Number of confirmations` - Number of confirmations the transaction must recieve before the order is marked as complete. Use `0` for nearly instant confirmation. (Default: 5)
* `traaittPlatform Address` - Your public traaittPlatform address starting with ETRX. (No default)
* `traaittService Host/IP` - IP address where `traaittService` is running. It is highly discouraged to run the wallet anywhere other than the local server! (Default: 127.0.0.1)
* `traaittService Port` - Port `traaittService` is bound to with the `--bind-port` argument. (Default 8070)
* `traaittService Password` - Password `traaittService` was started with using the `--rpc-password` argument. (Default: blank)
* `Show QR Code` - Show payment QR codes. (Default: unchecked)
* `Show Prices in traaittPlatform` - Convert all prices on the frontend to traaittPlatform. Experimental feature, only use if you do not accept any other payment option. (Default: unchecked)
* `Display Decimals` (if show prices in traaittPlatform is enabled) - Number of decimals to round prices to on the frontend. The final order amount will not be rounded. (Default: 2)

## Shortcodes

This plugin makes available two shortcodes that you can use in your theme.

#### Live price shortcode

This will display the price of traaittPlatform in the selected currency. If no currency is provided, the store's default currency will be used.

```
[traaittplatform-price]
[traaittplatform-price currency="BTC"]
[traaittplatform-price currency="USD"]
```
Will display:
```
1 ETRX = 0.00000149 LTC
1 ETRX = 0.00003815 USD
```


#### traaittPlatform accepted here badge

This will display a badge showing that you accept traaittPlatform-currency.

`[traaittplatform-accepted-here]`

![traaittPlatform Accepted Here](/assets/images/traaittplatform-accepted-here.png?raw=true "traaittPlatform Accepted Here")

## Donations

mosu-forge: ETRXuy85x1U8LN37NcMQr4VyyqkxpkmTsBj7iF1zNg2mjjNW4m41RbXPi1tZvvEpcs4WR7SBLj1eSRH3h7pQRRMFFNSQqEoBB7L
