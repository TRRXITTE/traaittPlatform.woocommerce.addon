<?php foreach($errors as $error): ?>
<div class="error"><p><strong>traaittPlatform Gateway Error</strong>: <?php echo $error; ?></p></div>
<?php endforeach; ?>

<h1>traaittPlatform Gateway Settings</h1>

<div style="border:1px solid #ddd;padding:5px 10px;">
    <?php
         echo 'Wallet height: ' . $balance['height'] . '</br>';
         echo 'Your balance is: ' . $balance['balance'] . '</br>';
         echo 'Unlocked balance: ' . $balance['unlocked_balance'] . '</br>';
         ?>
</div>

<table class="form-table">
    <?php echo $settings_html ?>
</table>

<h4><a href="https://github.com/traaittplatform/traaittplatform-woocommerce-gateway">Learn more about using the traaittPlatform payment gateway</a></h4>

<script>
function traaittplatformUpdateFields() {
    var usetraaittPlatformPrices = jQuery("#woocommerce_traaittplatform_gateway_use_traaittplatform_price").is(":checked");
    if(usetraaittPlatformPrices) {
        jQuery("#woocommerce_traaittplatform_gateway_use_traaittplatform_price_decimals").closest("tr").show();
    } else {
        jQuery("#woocommerce_traaittplatform_gateway_use_traaittplatform_price_decimals").closest("tr").hide();
    }
}
traaittplatformUpdateFields();
jQuery("#woocommerce_traaittplatform_gateway_use_traaittplatform_price").change(traaittplatformUpdateFields);
</script>

<style>
#woocommerce_traaittplatform_gateway_traaittplatform_address,
#woocommerce_traaittplatform_gateway_viewkey {
    width: 100%;
}
</style>
