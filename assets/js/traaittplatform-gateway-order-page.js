/*
 * Copyright (c) 2018, Ryo Currency Project
*/
function traaittplatform_showNotification(message, type='success') {
    var toast = jQuery('<div class="' + type + '"><span>' + message + '</span></div>');
    jQuery('#traaittplatform_toast').append(toast);
    toast.animate({ "right": "12px" }, "fast");
    setInterval(function() {
        toast.animate({ "right": "-400px" }, "fast", function() {
            toast.remove();
        });
    }, 2500)
}
function traaittplatform_showQR(show=true) {
    jQuery('#traaittplatform_qr_code_container').toggle(show);
}
function traaittplatform_fetchDetails() {
    var data = {
        '_': jQuery.now(),
        'order_id': traaittplatform_details.order_id
    };
    jQuery.get(traaittplatform_ajax_url, data, function(response) {
        if (typeof response.error !== 'undefined') {
            console.log(response.error);
        } else {
            traaittplatform_details = response;
            traaittplatform_updateDetails();
        }
    });
}

function traaittplatform_updateDetails() {

    var details = traaittplatform_details;

    jQuery('#traaittplatform_payment_messages').children().hide();
    switch(details.status) {
        case 'unpaid':
            jQuery('.traaittplatform_payment_unpaid').show();
            jQuery('.traaittplatform_payment_expire_time').html(details.order_expires);
            break;
        case 'partial':
            jQuery('.traaittplatform_payment_partial').show();
            jQuery('.traaittplatform_payment_expire_time').html(details.order_expires);
            break;
        case 'paid':
            jQuery('.traaittplatform_payment_paid').show();
            jQuery('.traaittplatform_confirm_time').html(details.time_to_confirm);
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'confirmed':
            jQuery('.traaittplatform_payment_confirmed').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired':
            jQuery('.traaittplatform_payment_expired').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
        case 'expired_partial':
            jQuery('.traaittplatform_payment_expired_partial').show();
            jQuery('.button-row button').prop("disabled",true);
            break;
    }

    jQuery('#traaittplatform_exchange_rate').html('1 ETRX = '+details.rate_formatted+' '+details.currency);
    jQuery('#traaittplatform_total_amount').html(details.amount_total_formatted);
    jQuery('#traaittplatform_total_paid').html(details.amount_paid_formatted);
    jQuery('#traaittplatform_total_due').html(details.amount_due_formatted);

    jQuery('#traaittplatform_integrated_address').html(details.integrated_address);

    if(traaittplatform_show_qr) {
        var qr = jQuery('#traaittplatform_qr_code').html('');
        new QRCode(qr.get(0), details.qrcode_uri);
    }

    if(details.txs.length) {
        jQuery('#traaittplatform_tx_table').show();
        jQuery('#traaittplatform_tx_none').hide();
        jQuery('#traaittplatform_tx_table tbody').html('');
        for(var i=0; i < details.txs.length; i++) {
            var tx = details.txs[i];
            var height = tx.height == 0 ? 'N/A' : tx.height;
	    var explorer_url = traaittplatform_explorer_url+'/transaction.html?hash='+tx.txid;
            var row = ''+
                '<tr>'+
                '<td style="word-break: break-all">'+
                '<a href="'+explorer_url+'" target="_blank">'+tx.txid+'</a>'+
                '</td>'+
                '<td>'+height+'</td>'+
                '<td>'+tx.amount_formatted+' ETRX</td>'+
                '</tr>';

            jQuery('#traaittplatform_tx_table tbody').append(row);
        }
    } else {
        jQuery('#traaittplatform_tx_table').hide();
        jQuery('#traaittplatform_tx_none').show();
    }

    // Show state change notifications
    var new_txs = details.txs;
    var old_txs = traaittplatform_order_state.txs;
    if(new_txs.length != old_txs.length) {
        for(var i = 0; i < new_txs.length; i++) {
            var is_new_tx = true;
            for(var j = 0; j < old_txs.length; j++) {
                if(new_txs[i].txid == old_txs[j].txid && new_txs[i].amount == old_txs[j].amount) {
                    is_new_tx = false;
                    break;
                }
            }
            if(is_new_tx) {
                traaittplatform_showNotification('Transaction received for '+new_txs[i].amount_formatted+' ETRX');
            }
        }
    }

    if(details.status != traaittplatform_order_state.status) {
        switch(details.status) {
            case 'paid':
                traaittplatform_showNotification('Your order has been paid in full');
                break;
            case 'confirmed':
                traaittplatform_showNotification('Your order has been confirmed');
                break;
            case 'expired':
            case 'expired_partial':
                traaittplatform_showNotification('Your order has expired', 'error');
                break;
        }
    }

    traaittplatform_order_state = {
        status: traaittplatform_details.status,
        txs: traaittplatform_details.txs
    };

}
jQuery(document).ready(function($) {
    if (typeof traaittplatform_details !== 'undefined') {
        traaittplatform_order_state = {
            status: traaittplatform_details.status,
            txs: traaittplatform_details.txs
        };
        setInterval(traaittplatform_fetchDetails, 30000);
        traaittplatform_updateDetails();
        new ClipboardJS('.clipboard').on('success', function(e) {
            e.clearSelection();
            if(e.trigger.disabled) return;
            switch(e.trigger.getAttribute('data-clipboard-target')) {
                case '#traaittplatform_integrated_address':
                    traaittplatform_showNotification('Copied destination address!');
                    break;
                case '#traaittplatform_total_due':
                    traaittplatform_showNotification('Copied total amount due!');
                    break;
            }
            e.clearSelection();
        });
    }
});
