<?php defined('BILLINGMASTER') or die;

$s = 0;
$count = count($list);
if ($count == 1 && !isset($is_prepaymentList)) { ?>
    <style>
        #install .install_item-title {
            margin-left: 0;
        }

        #install .install_item-title:before {
            content: none;
        }
    </style>
<? } ?>

<?php if ($count == 1 && isset($is_prepaymentList)) { ?>
    <style>
        #prepayment .install_item-title {
            margin-left: 0;
        }

        #prepayment .install_item-title:before {
            content: none;
        }
    </style>
<? } ?>
<?php
foreach ($list as $install_item):

    if ($total < $install_item['minimal']) {
        continue;
    }

    $is_prepayment = $install_item['prepayment'] == 1;

    $pays = Installment::getPays($install_item, $total);
    $p = 2;
    $m = 1;
    $increase_pay = $install_item['increase'] > 0 ? round($install_item['increase'] / $install_item['max_periods']) : 0;
?>

    <div class="install_item install_item-mt-50">
        <label class="install_item-radio">

            <input class="install_item-radio-check" type="radio" <?php if($s++ == 0) echo 'checked=""';?> name="installment_id" id="instal_<?=$install_item['id'];?>" value="<?=$install_item['id'];?>">
            <span class="install_item-title"><?=$install_item['title'];?></span>
            <div class="install_item-inner">
                <table class="install_item-table">
                    <tr>
                        <th><?= System::Lang('PAYMENT_NUMBER'); ?></th>
                        <th><?= System::Lang('PAYMENT_DATE'); ?></th>
                        <th class="install_item-table__last"><?= System::Lang('SUMMCLEAN'); ?></th>
                    </tr>

                    <tr>
                        <td><strong><?= System::Lang('FIRST_PAYMENT'); ?></strong></td>
                        <td><strong><?= System::Lang('TODAY'); ?></strong></td>
                        <td class="install_item-table__last"><strong><?= $pays['first_pay']; ?> <?= $this->settings['currency']; ?></strong></td>
                    </tr>

                    <?php while ($install_item['max_periods'] >= $p):
                        $pay_date = Installment::getNextPayDate($install_item, $now, $install_item['date_second_payment'], $m++); ?>
                        <tr>
                            <td><?= $p++ ?> <?= System::Lang('PAYMENT'); ?></td>
                            <td><?= date("d.m.Y", $pay_date); ?></td>
                            <td class="install_item-table__last"><?= $pays['other_pay']; ?> <?= $this->settings['currency']; ?></td>
                        </tr>
                    <?php endwhile; ?>

                </table>
                <?php if ($install_item['increase'] > 0): ?>
                    <p class="install_item__last-block"><?= $is_prepayment ? System::Lang('BREAKDOWN_SURCHARGE') : System::Lang('INSTALLMENT_COAST'); ?> <?= "{$install_item['increase']} {$this->settings['currency']}"; ?></p>
                <?php endif; ?>
                <p class="install_item__last-block">
                    <strong><?= $is_prepayment ? System::Lang('PREPAYMENT_COAST_SUMM') : System::Lang('INSTALLMENT_COAST_SUMM') ?> <?= ($total + $install_item['increase']) . " {$this->settings['currency']}"; ?></strong>
                </p>
            </div>
        </label>
    </div>
<?php endforeach; ?>
<div class="payment-deskr">
    <!--p>При оплате в рассрочку доступ к курсу предоставляется сразу, но уроки открываются постепенно, по мере оплаты. Счета на оставшиеся платежи будут в вашем личном кабинете.
        <br>Оплатить счета можно в любое время, но не позднее даты платежа.</p-->
</div>
<input type="hidden" name="order_date" value="<?= $order_date; ?>">
<div class="payment-submir-wrap">
    <input type="hidden" name="is_prepayment" value="<?= $is_prepayment ?>">
    <button class="btn-green-small" type="submit" name="installment">
        <?= $prepayment_list ? System::Lang('PREPAYMENT_PLAN') : System::Lang('INSTALLMENT_PLAN'); ?>
    </button>
</div>

