<?defined('BILLINGMASTER') or die;?>

<? // Вывод промо кода
require_once (__DIR__ . '/../common/show_promo_code.php');?>

<? // Вывод уведомления CallPassword
if(CallPassword::isShowButton($user)):
    require_once (ROOT.'/extensions/callpassword/views/show_notice.php');
endif;?>

<?
// Вывод уведомления Telegram
Connect::showConnectNotice('telegram', $user['user_id'], true);
?>

<?if(isset($_GET['success'])):?>
    <div class="success_message"><?=System::Lang('USER_SUCCESS_MESS');?></div>
<?endif;

if(isset($_GET['fail'])):?>
    <div class="warning_message"><?=System::Lang('ERROR_HAPPENED');?></div>
<?endif;

if($myplanes):?>
    <div class="pay_orders">
        <h1 class="mb-30"><?=System::Lang('MY_SUBSCRIPTIONS');?></h1>

        <div class="table-responsive">
            <table class="pay-table pay-table-padding">
                <tr>
                    <th class="text-left"><?=System::Lang('TITLE');?></th>
                    <th><?=System::Lang('VALID_UNTIL');?></th>
                    <th><?=System::Lang('ACTION');?></th>
                    <?if(Member::getTotalRecurrentPlanes2User($userId)):?>
                        <th><?=System::Lang('STATUS');?></th>
                    <?endif;?>
                </tr>

                <?$recurrents = false;
                foreach($myplanes as $myplane):
                    if($myplane['subscription_id'] != null) $recurrents = true;
                    if ($myplane['status'] == 1) {
                        $last_update = $myplane['last_update'];
                    }
                    $plane_data = Member::getPlaneByID($myplane['subs_id']);?>
                    <tr>
                        <td class="text-left" title="<?=$plane_data['subs_desc'];?>"><?=$plane_data['name'];?></td>

                        <td><?=date("d.m.Y H:i:s", $myplane['end']);?></td>
                        <?if($myplane['status'] == 1 && $myplane['subscription_id'] != null && $myplane['recurrent_cancelled'] != 1):?>
                            <td>
                                <a onclick="return confirm('Вы уверены что хотите отменить подписку?')" href="<?=$this->settings['script_url'];?>/lk/membership?action=pause&id=<?=$myplane['id'];?>"><?=System::Lang('SUBSCRIPTION_CANCEL');?></a>
                            </td>
                        <?endif;?>

                        <td><?if($myplane['status'] == 1):?>
                                <span class="status-act"><?=System::Lang('ACTIVE2');?></span>
                            <?else:?>
                                <span class="status-remove"><?=System::Lang('NOT_ACTIVE2');?></span>
                            <?endif;?>
                        </td>
                    </tr>
                <?endforeach;?>
            </table>
        </div>
    </div>

    <?if($recurrents):?>
        <div class="my-payments-section">
            <h2 class="mb-30"><?=System::Lang('PAYMENTS');?></h2>
            <div class="my-payments-date">
                <div class="my-payments-date__left">
                    <?if(!empty($last_update)):?>
                        <p><?=System::Lang('LAST_PAYMENTS');?>  <?=date("d.m.Y H:i:s", $last_update);?></p>
                    <?endif; ?>
                    <?=System::Lang('BANK_CARD_SAVING');?>
                </div>
                <div class="my-payments-date__right">
                    <a href="/lk/orders" class="btn-blue-history"><?=System::Lang('PAYMENT_HISTORY');?></a>
                </div>
            </div>
        </div>
    <?endif;?>
<?else:
    echo 'Нет подписок';
endif;

/*<h2>Отмененные подписки</h2>
<div class="pay_orders">
    <div class="table-responsive">
        <table class="pay-table pay-table-padding">
            <tr>
                <th class="text-left">Название плана</th>
                <th>Действует до:</th>
                <th>Действие</th>
                <th>Статус</th>
            </tr>
            <?foreach($myplanes as $myplane):
                $plane_data = Member::getPlaneByID($myplane['subs_id']);?>
            <tr>
                <td class="text-left" title="<?=$plane_data['subs_desc'];?>"><?=$plane_data['name'];?></td>

                <td><?=date("d.m.Y H:i:s", $myplane['end']);?></td>
                <td><?if($myplane['status'] == 1){?>
                    <a  onclick="return confirm('Вы уверены что хотите отменить подписку?')" href="<?=$setting['script_url'];?>/lk/membership?action=pause&id=<?=$myplane['id'];?>">Отменить подписку</a>
                    <?} else {?>

                    <?} ?>
                </td>
                <td><?if($myplane['status'] == 1) echo '<span class="status-act">активен</span>'; else echo '<span class="status-remove">Отключен</span>';?></td>
            </tr>
            <?endforeach; ?>
        </table>
    </div>
</div>*/