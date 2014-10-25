<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <h1><?php echo $heading_title; ?></h1>
    <div class="checkout">
        <div id="checkout">
            <div class="checkout-heading"><?php echo $text_checkout_option; ?></div>
            <div class="checkout-content"></div>
        </div>
        <div id="shipping-address">
            <div class="checkout-heading"><?php echo $text_checkout_shipping_address; ?></div>
            <div class="checkout-content"></div>
        </div>
        <div id="shipping-method">
            <div class="checkout-heading"><?php echo $text_checkout_shipping_method; ?></div>
            <div class="checkout-content"></div>
        </div>
        <div id="confirm">
            <div class="checkout-heading"><?php echo $text_checkout_confirm; ?></div>
            <div class="checkout-content"></div>
        </div>
    </div>
    <?php echo $content_bottom; ?></div>
<script type="text/javascript"><!--
$('#checkout .checkout-content input[name=\'account\']').live('change', function() {
    if ($(this).attr('value') == 'register') {
        $('#payment-address .checkout-heading span').html('<?php echo $text_checkout_account; ?>');
    } else {
        $('#payment-address .checkout-heading span').html('<?php echo $text_checkout_payment_address; ?>');
    }
});

$('.checkout-heading a').live('click', function() {
    $('.checkout-content').slideUp('slow');

    $(this).parent().parent().find('.checkout-content').slideDown('slow');
    $(this).remove();
});

$(document).ready(function() {
    $.ajax({
        url: 'index.php?route=checkout/<?php echo $logged ? "options" : "login" ?>',
        dataType: 'json',
        success: function(json) {
            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['output']) {
                $('#checkout .checkout-content').html(json['output']);
                $('#checkout .checkout-content').slideDown('slow');
            }
        }
    });
});

// Checkout options continue button handler
$('#button-options').live('click', function() {
    if ($('input[name="order_to_add"]:checked').val() != 0)
        $.ajax({
            url: 'index.php?route=checkout/add_to_order&order_id=' + $('input[name="order_to_add"]:checked').val(),
            dataType: 'json',
            beforeSend: function() {
                $('#button-options').attr('disabled', true);
                $('#button-options').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
            },
            complete: function() {
                $('#button-options').attr('disabled', false);
                $('.wait').remove();
            },
            success: function(json)
            {
                if (json['output'])
                {
                    $('#confirm .checkout-content').html(json['output']);
                    $('#confirm .checkout-content').slideDown('slow');
                    $('#checkout .checkout-content').slideUp('slow');
                    $('#checkout .checkout-heading').append('<a><?= $text_modify ?></a>');
                }
                else
                {
                    alert(json);
                }
            },
            error: function(xhr, ajaxOptions, thrownError)
            {
                alert(thrownError);
            }
        });
    else
        $.ajax({
            url: 'index.php?route=checkout/address/shipping',
            dataType: 'json',
            success: function(json) {
                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['output']) {
                    $('#checkout .checkout-content').slideUp('slow');
                    $('#checkout .checkout-heading').append('<a><?= $text_modify ?></a>');

                    $('#shipping-address .checkout-content').html(json['output']);
                    $('#shipping-address .checkout-content').slideDown('slow');


                }
            }
        });
});

// Checkout
$('#button-account').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/' + $('input[name=\'account\']:checked').attr('value'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-account').attr('disabled', true);
            $('#button-account').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#button-account').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.warning').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['output']) {
                $('#payment-address .checkout-content').html(json['output']);

                $('#checkout .checkout-content').slideUp('slow');

                $('#payment-address .checkout-content').slideDown('slow');

                $('#checkout .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
            }
        }
    });
});

// Login
$('#button-login').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/login',
        type: 'post',
        data: $('#checkout #login :input'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-login').attr('disabled', true);
            $('#button-login').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#button-login').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.warning').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['total']) {
                $('#cart_total_data').html(json['total_data']);
            }

            if (json['logged']) {
                $('#welcome').html(json['logged']);
            }

            if (json['error']) {
                $('#checkout .checkout-content').prepend('<div class="error" style="display: none;">' + json['error']['warning'] + '</div>');

                $('.warning').fadeIn('slow');
            } else {
                $.ajax({
                    url: 'index.php?route=checkout/address/payment',
                    dataType: 'json',
                    success: function(json) {
                        if (json['redirect']) {
                            location = json['redirect'];
                        }

                        if (json['output']) {
                            $('#payment-address .checkout-content').html(json['output']);

                            $('#checkout .checkout-content').slideUp('slow');

                            $('#payment-address .checkout-content').slideDown('slow');

                            $('#payment-address .checkout-heading span').html('<?php echo $text_checkout_payment_address; ?>');
                        }
                    }
                });
            }
        }
    });
});

// Register
$('#button-register').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/register',
        type: 'post',
        data: $('#payment-address input[type=\'text\'], #payment-address input[type=\'password\'], #payment-address input[type=\'checkbox\']:checked, #payment-address input[type=\'radio\']:checked, #payment-address select'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-register').attr('disabled', true);
            $('#button-register').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#button-register').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.warning').remove();
            $('.error').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                if (json['error']['warning']) {
                    $('#payment-address .checkout-content').prepend('<div class="error" style="display: none;">' + json['error']['warning'] + '</div>');

                    $('.warning').fadeIn('slow');
                }

                if (json['error']['firstname']) {
                    $('#payment-address input[name=\'firstname\'] + br').after('<span class="error">' + json['error']['firstname'] + '</span>');
                }

                if (json['error']['lastname']) {
                    $('#payment-address input[name=\'lastname\'] + br').after('<span class="error">' + json['error']['lastname'] + '</span>');
                }

                if (json['error']['email']) {
                    $('#payment-address input[name=\'email\'] + br').after('<span class="error">' + json['error']['email'] + '</span>');
                }

                if (json['error']['telephone']) {
                    $('#payment-address input[name=\'telephone\'] + br').after('<span class="error">' + json['error']['telephone'] + '</span>');
                }

                if (json['error']['address_1']) {
                    $('#payment-address input[name=\'address_1\'] + br').after('<span class="error">' + json['error']['address_1'] + '</span>');
                }

                if (json['error']['city']) {
                    $('#payment-address input[name=\'city\'] + br').after('<span class="error">' + json['error']['city'] + '</span>');
                }

                if (json['error']['postcode']) {
                    $('#payment-address input[name=\'postcode\'] + br').after('<span class="error">' + json['error']['postcode'] + '</span>');
                }

                if (json['error']['country']) {
                    $('#payment-address select[name=\'country_id\'] + br').after('<span class="error">' + json['error']['country'] + '</span>');
                }

                if (json['error']['zone']) {
                    $('#payment-address select[name=\'zone_id\'] + br').after('<span class="error">' + json['error']['zone'] + '</span>');
                }

                if (json['error']['password']) {
                    $('#payment-address input[name=\'password\'] + br').after('<span class="error">' + json['error']['password'] + '</span>');
                }

                if (json['error']['confirm']) {
                    $('#payment-address input[name=\'confirm\'] + br').after('<span class="error">' + json['error']['confirm'] + '</span>');
                }
            } else {
            <?php if ($shipping_required) { ?>
                    var shipping_address = $('#payment-address input[name=\'shipping_address\']:checked').attr('value');

                    if (shipping_address) {
                        $.ajax({
                            url: 'index.php?route=checkout/shipping',
                            dataType: 'json',
                            success: function(json) {
                                if (json['redirect']) {
                                    location = json['redirect'];
                                }

                                if (json['output']) {
                                    $('#shipping-method .checkout-content').html(json['output']);

                                    $('#payment-address .checkout-content').slideUp('slow');

                                    $('#shipping-method .checkout-content').slideDown('slow');

                                    $('#checkout .checkout-heading a').remove();
                                    $('#payment-address .checkout-heading a').remove();
                                    $('#shipping-address .checkout-heading a').remove();
                                    $('#shipping-method .checkout-heading a').remove();
                                    $('#payment-method .checkout-heading a').remove();

                                    $('#shipping-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                                    $('#payment-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');

                                    $.ajax({
                                        url: 'index.php?route=checkout/address/shipping',
                                        dataType: 'json',
                                        success: function(json) {
                                            if (json['redirect']) {
                                                location = json['redirect'];
                                            }

                                            if (json['output']) {
                                                $('#shipping-address .checkout-content').html(json['output']);
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        $.ajax({
                            url: 'index.php?route=checkout/address/shipping',
                            dataType: 'json',
                            success: function(json) {
                                if (json['redirect']) {
                                    location = json['redirect'];
                                }

                                if (json['output']) {
                                    $('#shipping-address .checkout-content').html(json['output']);

                                    $('#payment-address .checkout-content').slideUp('slow');

                                    $('#shipping-address .checkout-content').slideDown('slow');

                                    $('#checkout .checkout-heading a').remove();
                                    $('#payment-address .checkout-heading a').remove();
                                    $('#shipping-address .checkout-heading a').remove();
                                    $('#shipping-method .checkout-heading a').remove();
                                    $('#payment-method .checkout-heading a').remove();

                                    $('#payment-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                                }
                            }
                        });
                    }
                <?php } else { ?>
                    $.ajax({
                        url: 'index.php?route=checkout/payment',
                        dataType: 'json',
                        success: function(json) {
                            if (json['redirect']) {
                                location = json['redirect'];
                            }

                            if (json['output']) {
                                $('#payment-method .checkout-content').html(json['output']);

                                $('#payment-address .checkout-content').slideUp('slow');

                                $('#payment-method .checkout-content').slideDown('slow');

                                $('#checkout .checkout-heading a').remove();
                                $('#payment-address .checkout-heading a').remove();
                                $('#payment-method .checkout-heading a').remove();

                                $('#payment-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                            }
                        }
                    });
                <?php } ?>

                $.ajax({
                    url: 'index.php?route=checkout/address/payment',
                    dataType: 'json',
                    success: function(json) {
                        if (json['redirect']) {
                            location = json['redirect'];
                        }

                        if (json['output']) {
                            $('#payment-address .checkout-content').html(json['output']);

                            $('#payment-address .checkout-heading span').html('<?php echo $text_checkout_payment_address; ?>');
                        }
                    }
                });
            }
        }
    });
});

// Shipping Address
$('#shipping-address #button-address').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/address/shipping',
        type: 'post',
        data: $('#shipping-address input[type=\'text\'], #shipping-address input[type=\'password\'], #shipping-address input[type=\'checkbox\']:checked, #shipping-address input[type=\'radio\']:checked, #shipping-address select'),
        dataType: 'json',
        beforeSend: function() {
            $('#shipping-address #button-address').attr('disabled', true);
            $('#shipping-address #button-address').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#shipping-address #button-address').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.error').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                if (json['error']['firstname']) {
                    $('#shipping-address input[name=\'firstname\']').after('<span class="error">' + json['error']['firstname'] + '</span>');
                }

                if (json['error']['lastname']) {
                    $('#shipping-address input[name=\'lastname\']').after('<span class="error">' + json['error']['lastname'] + '</span>');
                }

                if (json['error']['email']) {
                    $('#shipping-address input[name=\'email\']').after('<span class="error">' + json['error']['email'] + '</span>');
                }

                if (json['error']['phone']) {
                    $('#shipping-address input[name=\'phone\']').after('<span class="error">' + json['error']['phone'] + '</span>');
                }

                if (json['error']['address_1']) {
                    $('#shipping-address input[name=\'address_1\']').after('<span class="error">' + json['error']['address_1'] + '</span>');
                }

                if (json['error']['city']) {
                    $('#shipping-address input[name=\'city\']').after('<span class="error">' + json['error']['city'] + '</span>');
                }

                if (json['error']['postcode']) {
                    $('#shipping-address input[name=\'postcode\']').after('<span class="error">' + json['error']['postcode'] + '</span>');
                }

                if (json['error']['country']) {
                    $('#shipping-address select[name=\'country_id\']').after('<span class="error">' + json['error']['country'] + '</span>');
                }

                if (json['error']['zone']) {
                    $('#shipping-address select[name=\'zone_id\']').after('<span class="error">' + json['error']['zone'] + '</span>');
                }
            } else {
                $.ajax({
                    url: 'index.php?route=checkout/shipping',
                    dataType: 'json',
                    success: function(json) {
                        if (json['redirect']) {
                            location = json['redirect'];
                        }

                        if (json['output']) {
                            $('#shipping-method .checkout-content').html(json['output']);

                            $('#shipping-address .checkout-content').slideUp('slow');

                            $('#shipping-method .checkout-content').slideDown('slow');

                            $('#shipping-address .checkout-heading a').remove();
                            $('#shipping-method .checkout-heading a').remove();
                            $('#payment-method .checkout-heading a').remove();

                            $('#shipping-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                        }

                        $.ajax({
                            url: 'index.php?route=checkout/address/shipping',
                            dataType: 'json',
                            success: function(json) {
                                if (json['redirect']) {
                                    location = json['redirect'];
                                }

                                if (json['output']) {
                                    $('#shipping-address .checkout-content').html(json['output']);
                                }
                            }
                        });
                    }
                });
            }
        }
    });
});

// Guest
$('#button-guest').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/guest',
        type: 'post',
        data: $('#payment-address input[type=\'text\'], #payment-address input[type=\'checkbox\']:checked, #payment-address select'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-guest').attr('disabled', true);
            $('#button-guest').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#button-guest').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.error').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                if (json['error']['firstname']) {
                    $('#payment-address input[name=\'firstname\'] + br').after('<span class="error">' + json['error']['firstname'] + '</span>');
                }

                if (json['error']['lastname']) {
                    $('#payment-address input[name=\'lastname\'] + br').after('<span class="error">' + json['error']['lastname'] + '</span>');
                }

                if (json['error']['email']) {
                    $('#payment-address input[name=\'email\'] + br').after('<span class="error">' + json['error']['email'] + '</span>');
                }

                if (json['error']['telephone']) {
                    $('#payment-address input[name=\'telephone\'] + br').after('<span class="error">' + json['error']['telephone'] + '</span>');
                }

                if (json['error']['address_1']) {
                    $('#payment-address input[name=\'address_1\'] + br').after('<span class="error">' + json['error']['address_1'] + '</span>');
                }

                if (json['error']['city']) {
                    $('#payment-address input[name=\'city\'] + br').after('<span class="error">' + json['error']['city'] + '</span>');
                }

                if (json['error']['postcode']) {
                    $('#payment-address input[name=\'postcode\'] + br').after('<span class="error">' + json['error']['postcode'] + '</span>');
                }

                if (json['error']['country']) {
                    $('#payment-address select[name=\'country_id\'] + br').after('<span class="error">' + json['error']['country'] + '</span>');
                }

                if (json['error']['zone']) {
                    $('#payment-address select[name=\'zone_id\'] + br').after('<span class="error">' + json['error']['zone'] + '</span>');
                }
            } else {
            <?php if ($shipping_required) { ?>
                    var shipping_address = $('#payment-address input[name=\'shipping_address\']:checked').attr('value');

                    if (shipping_address) {
                        $.ajax({
                            url: 'index.php?route=checkout/shipping',
                            dataType: 'json',
                            success: function(json) {
                                if (json['redirect']) {
                                    location = json['redirect'];
                                }

                                if (json['output']) {
                                    $('#shipping-method .checkout-content').html(json['output']);

                                    $('#payment-address .checkout-content').slideUp('slow');

                                    $('#shipping-method .checkout-content').slideDown('slow');

                                    $('#payment-address .checkout-heading a').remove();
                                    $('#shipping-address .checkout-heading a').remove();
                                    $('#shipping-method .checkout-heading a').remove();
                                    $('#payment-method .checkout-heading a').remove();

                                    $('#payment-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                                    $('#shipping-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                                }

                                $.ajax({
                                    url: 'index.php?route=checkout/guest/shipping',
                                    dataType: 'json',
                                    success: function(json) {
                                        if (json['redirect']) {
                                            location = json['redirect'];
                                        }

                                        if (json['output']) {
                                            $('#shipping-address .checkout-content').html(json['output']);
                                        }
                                    }
                                });
                            }
                        });
                    } else {
                        $.ajax({
                            url: 'index.php?route=checkout/guest/shipping',
                            dataType: 'json',
                            success: function(json) {
                                if (json['redirect']) {
                                    location = json['redirect'];
                                }

                                if (json['output']) {
                                    $('#shipping-address .checkout-content').html(json['output']);

                                    $('#payment-address .checkout-content').slideUp('slow');

                                    $('#shipping-address .checkout-content').slideDown('slow');

                                    $('#payment-address .checkout-heading a').remove();
                                    $('#shipping-address .checkout-heading a').remove();
                                    $('#shipping-method .checkout-heading a').remove();
                                    $('#payment-method .checkout-heading a').remove();

                                    $('#payment-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                                }
                            }
                        });
                    }
                <?php } else { ?>
                    $.ajax({
                        url: 'index.php?route=checkout/payment',
                        dataType: 'json',
                        success: function(json) {
                            if (json['redirect']) {
                                location = json['redirect'];
                            }

                            if (json['output']) {
                                $('#payment-method .checkout-content').html(json['output']);

                                $('#payment-address .checkout-content').slideUp('slow');

                                $('#payment-method .checkout-content').slideDown('slow');

                                $('#payment-address .checkout-heading a').remove();
                                $('#payment-method .checkout-heading a').remove();

                                $('#payment-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                            }
                        }
                    });
                <?php } ?>
            }
        }
    });
});

// Guest Shipping
$('#button-guest-shipping').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/guest/shipping',
        type: 'post',
        data: $('#shipping-address input[type=\'text\'], #shipping-address select'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-guest-shipping').attr('disabled', true);
            $('#button-guest-shipping').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#button-guest-shipping').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('.error').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                if (json['error']['firstname']) {
                    $('#shipping-address input[name=\'firstname\']').after('<span class="error">' + json['error']['firstname'] + '</span>');
                }

                if (json['error']['lastname']) {
                    $('#shipping-address input[name=\'lastname\']').after('<span class="error">' + json['error']['lastname'] + '</span>');
                }

                if (json['error']['address_1']) {
                    $('#shipping-address input[name=\'address_1\']').after('<span class="error">' + json['error']['address_1'] + '</span>');
                }

                if (json['error']['city']) {
                    $('#shipping-address input[name=\'city\']').after('<span class="error">' + json['error']['city'] + '</span>');
                }

                if (json['error']['postcode']) {
                    $('#shipping-address input[name=\'postcode\']').after('<span class="error">' + json['error']['postcode'] + '</span>');
                }

                if (json['error']['country']) {
                    $('#shipping-address select[name=\'country_id\']').after('<span class="error">' + json['error']['country'] + '</span>');
                }

                if (json['error']['zone']) {
                    $('#shipping-address select[name=\'zone_id\']').after('<span class="error">' + json['error']['zone'] + '</span>');
                }
            } else {
                $.ajax({
                    url: 'index.php?route=checkout/shipping',
                    dataType: 'json',
                    success: function(json) {
                        if (json['redirect']) {
                            location = json['redirect'];
                        }

                        if (json['output']) {
                            $('#shipping-method .checkout-content').html(json['output']);

                            $('#shipping-address .checkout-content').slideUp('slow');

                            $('#shipping-method .checkout-content').slideDown('slow');

                            $('#shipping-address .checkout-heading a').remove();
                            $('#shipping-method .checkout-heading a').remove();
                            $('#payment-method .checkout-heading a').remove();

                            $('#shipping-address .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                        }
                    }
                });
            }
        }
    });
});

$('#button-shipping').live('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/shipping',
        type: 'post',
        data: $('#shipping-method input[type=\'radio\']:checked, #shipping-method textarea'),
        dataType: 'json',
        beforeSend: function() {
            $('#button-shipping').attr('disabled', true);
            $('#button-shipping').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        done: function() {
            $('#button-shipping').attr('disabled', false);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('.wait').remove();
            alert(xhr.responseText);
        },
        success: function(json) {
            $('.warning').remove();

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                if (json['error']['warning']) {
                    $('#shipping-method .checkout-content').prepend('<div class="error" style="display: none;">' + json['error']['warning'] + '</div>');

                    $('.warning').fadeIn('slow');
                    $('.wait').remove();
                }
            } else {
                $.ajax({
                    url: 'index.php?route=checkout/confirm',
                    dataType: 'json',
                    done: function() {
                        $('.wait').remove();
                    },
                    success: function(json) {
                        if (json['redirect']) {
                            location = json['redirect'];
                        }

                        if (json['output']) {
                            $('#confirm .checkout-content').html(json['output']);
                            $('#confirm .checkout-content').slideDown('slow');

                            $('#shipping-method .checkout-content').slideUp('slow');
                            $('#shipping-method .checkout-heading a').remove();
                            $('#shipping-method .checkout-heading').append('<a><?php echo $text_modify; ?></a>');
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alert(xhr.responseText);
                    }
                });
            }
        }
    });
});
//--></script>
<?php echo $footer; ?>