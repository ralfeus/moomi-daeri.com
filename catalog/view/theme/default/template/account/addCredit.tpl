<?= $header ?><?= $column_left ?><?= $column_right ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb)
            echo $breadcrumb['separator'] . "<a href=\"" . $breadcrumb['href'] . "\">" . $breadcrumb['text'] . "</a>";
        ?>
    </div>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">" . nl2br(print_r($notification, true)) . "</div>";
    ?>
    <form id="form" action="<?= $action ?>" method="post">
        <table class="form">
            <tr>
                <td><?= $textAmount ?></td>
                <td><input name="amount" value="<?= $amount ?>" /></td>
                <td>
                    <select name="currency">
                        <?php foreach ($currencies as $currency): ?>
                            <option value="<?= $currency['code'] ?>" <?= $currency['selected'] ?>><?= $currency['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?= $textComment ?></td>
                <td colspan="2">
                    <input name="comment" value="<?= $comment ?>" type="text" multiline="true" />
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="buttons"><div class="right">
                        <a class="button" onclick="$('#form').submit();"><span><?= $textSubmit ?></span></a></div>
                    </div>
                </td>
            </tr>
        </table>
    </form>
    <table class="form">
        <tr>
            <td colspan="3">
                <div class="cnt_page">
                    <span style="font-size:22px;">Payment process</span><br />
                    <span style="font-size:36px;"><span style="background-color:#ee82ee;">After the payment enter proper amount in the field above</span></span><br />
                    <span style="font-size:26px;"><span style="background-color:#00ffff;">We have several methods of payment </span></span><br />
                    <span style="font-size:20px;">1.</span><br />
                    <span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentpaypal.jpg"></span></span></span>
                    <table border="1" cellpadding="1" cellspacing="1" style="width: 500px;">
                        <tbody><tr><td>
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                <input name="cmd" type="hidden" value="_xclick">
                                <input name="business" type="hidden" value="8MNTWT26SNDTG">
                                <input name="lc" type="hidden" value="KR">
                                <input name="amount" type="input" value="0">
                                <input name="tax_rate" type="hidden" value="4">
                                <select name="currency_code">
                                    <option default="true" value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="JPY">JPY</option>
                                    <option value="RUB">RUB</option>
                                </select>
                                <input name="button_subtype" type="hidden" value="services">
                                <input name="no_note" type="hidden" value="0">
                                <input name="cn" type="hidden" value="Add special instructions to the seller:">
                                <input name="no_shipping" type="hidden" value="2">
                                <input name="bn" type="hidden" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
                                <input alt="PayPal - The safer, easier way to pay online!" border="0" name="submit" src="/image/data/payment/sendmoney.gif" type="image">
                                <img alt="" border="0" height="1" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" />
                            </form>
                        </td></tr></tbody>
                    </table>
                    <p><span style="font-size:18px;">2. </span></p>
                    <p><span style="font-size:18px;">&nbsp;For this method total amount + 3% </span></p>
                    <table border="1" cellpadding="1" cellspacing="1" style="width: 500px;">
                        <tbody><tr>
                            <td>
                                <table><tbody>
                                    <tr><td>
                                        <input name="on0" type="hidden" value="add payment in euro"><span style="font-size:18px;">add on EURO</span>
                                    </td></tr>
                                    <tr><td>
                                        <input maxlength="200" name="os0" type="text">
                                    </td></tr>
                                </tbody></table>
                                <p>
                                    <a href="https://secure.onpay.ru/pay/MooMiDae_Fashion?pay_mode=free&f=7&currency=EUR&ln=en&url_success=http%3A//moomidae.com/index.php%3Froute%3Daccount/addCredit&url_fail=http%3A//moomidae.com/index.php%3Froute%3Dinformation/information%26information_id%3D5" target="_blank">
                                        <img alt="" src="/image/data/payment/onpay.jpg" style="width: 319px; height: 186px;">
                                    </a>
                                </p>
                            </td>
                            <td>
                                <table><tbody>
                                    <tr><td>
                                        <span style="font-size:20px;">add in USD</span>
                                    </td></tr>
                                    <tr><td>
                                        <input maxlength="200" name="os0" type="text">
                                    </td></tr>
                                </tbody></table>
                                <p>
                                    <a href="https://secure.onpay.ru/pay/MooMiDae_Fashion?pay_mode=free&currency=USD&ln=en&url_success=https%3A//koreafashion.kr/index.php%3Froute%3Daccount/addCredit&url_fail=http%3A//koreafashion.kr/index.php%3Froute%3Dinformation/information%26information_id%3D5" target="_blank">
                                        <img alt="click" src="/image/data/payment/onpay.jpg" style="width: 319px; height: 186px;">
                                    </a>
                                </p>
                            </td>
                        </tr></tbody>
                    </table>
                    <table><tbody>
                        <tr><td>
                            <span style="font-size:20px;">внести в рублях</span>
                        </td></tr>
                        <tr><td>
                            <input maxlength="200" name="os0" type="text">
                        </td></tr>
                    </tbody></table>
                    <p>
                        <a href="https://secure.onpay.ru/pay/MooMiDae_Fashion?pay_mode=free&f=7&currency=RUR&url_success=http%3A//moomi-daeri.com/index.php%3Froute%3Daccount/addCredit&url_fail=http%3A//moomi-daeri.com/index.php%3Froute%3Dinformation/information%26information_id%3D5" target="_blank">
                            <img alt="click" src="/image/data/payment/onpay.jpg" style="width: 319px; height: 186px;">
                        </a>
                    </p>
                    <p><span style="font-size:22px;">If you are paying through Western Union, Money Gramm or Unistream, you will have to pay according to the currency rate of the date of order in dollars.</span></p>
                    <p><span style="font-size:18px;">&nbsp; For Western Union, Money Gramm, Unistream&nbsp; , you have&nbsp; 2 procent discount .</span></p>
                    <p><span style="font-size:18px;">3.</span></p>
                    <p><span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentwestwenunion.jpg"></span></span></span></span></span></p>
                    <p><span style="font-size:26px;">4.</span></p>
                    <p><span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentunistream.jpg" style="width: 598px; height: 260px"></span></span></span></p>
                    <p><span style="font-size:26px;">5.</span></p>
                    <p><span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentmoneygram.jpg"></span></span></span></p>
                    <p><span style="font-size:26px;">6.</span></p>
                    <p><span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentbank1.jpg"></span></span></span></span></span></p>
                    <p><span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentbank2.jpg" style="width: 599px; height: 358px"></span></span></span></span></span></p>
                    <p><span style="font-size:22px;"><span class="short_text" lang="en"><span class="hps"><img alt="" src="http://moomi-daeri.com/image/data/payment/Paymentbank3.jpg" style="width: 599px; height: 310px"></span></span></span></p>
                </div>
            </td>
        </tr>
    </table>
</div>
<?= $footer ?>