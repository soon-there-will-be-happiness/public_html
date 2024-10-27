<?defined('BILLINGMASTER') or die;?>
<?require_once (dirname(__FILE__) . '/../../../../../components/cyclopsApi.php');?>
<!-- 1 Основное -->
<div>
    <div class="table-responsive">
        <table class="usertable fz-14">
            <tr>
                <th><?=System::Lang('PERIOD');?></th>
                <th class="text-right"><?=System::Lang('CLIKS');?></th>
                <th class="text-right"><?=System::Lang('INVOISES_ISSUED');?></th>
                <th class="text-right"><?=System::Lang('INVOISES_PAID');?></th>
                <th class="text-right"><?=System::Lang('EARNED');?></th>
            </tr>

            <?$cur_period = new Datetime(date('Ym',time()));
            $count_month_has_date = $count_month_has_date > 12 ? 12 : $count_month_has_date;
            for($i = 0; $i <= $count_month_has_date; $i++):
                $kol1 = $kol2 = $kol3 = $kol4 = '-';
                $cur_period_str = date('Ym', date_timestamp_get($cur_period));
                $a = array_search($cur_period_str, array_column($main_table,'period'));
                if ($a !== false){
                    $kol1 = $main_table[$a]['hits'];
                    $kol2 = $main_table[$a]['invoice'];
                    $kol3 = $main_table[$a]['pay_invoice'];
                    $kol4 = $main_table[$a]['payments'].'&nbsp'.$this->settings['currency'];
                }?>
                <tr>
                    <?if (date('Ym',time()) === $cur_period_str) {
                        $period_prn = 'Текущий период';
                    } else {
                        $month_num = substr($cur_period_str, 4);
                        $month_lang = $months[(int)$month_num];
                        $period_prn = $month_lang . ' ' .substr($cur_period_str, 0, 4);
                    };?>
                    <td><?=$period_prn;?></td>
                    <td class="text-right"><?=$kol1;?></td>
                    <td class="text-right"><?=$kol2;?></td>
                    <td class="text-right"><?=$kol3;?></td>
                    <td class="text-right"><?=$kol4;?></td>
                </tr>

                <?$cur_period = $cur_period->modify("first day of last month");
            endfor;?>
        </table>
    </div>

    <div class="total-money">
        <?$now = time();
        $past_month = date("m" ,strtotime("first day of last month")); // номер прошлого месяца

        // кол-во дней в прошлом месяце
        $days = 30;
        if (in_array($past_month, [1, 3, 5, 7, 10, 10, 12])) {
            $days = 31;
        } elseif($past_month == 2) {
            $days = date("L") == 1 ? 29 : 28;
        }

        $curr_day = date('j')-1;
        $curr_hour = date('G')+1;

        $past_time = $curr_day * 86400 + $curr_hour * 3600; // прошло времени с конца прошлого месяца

        $last_day_pay_month = $now - $past_time;// последний день прошлого месяца
        $first_day_past_month = $now - (($curr_hour - 1) * 3600) - ($days + $curr_day + 1) * 86400;// первый день прошлого месяца

        $last_month_pay = Aff::getLastMonthPay($userId, 'aff', $first_day_past_month, $last_day_pay_month); // заработано за последий месяц?>

        <h4><?=System::Lang('YOUR_ID');?> <?=$userId;?></h4>
        <?php
        $fill_req = Aff::checkAllPartnerReq($userId);
        if ($fill_req): ?>
            <h4>
                <?= System::Lang('YOUR_OFERTA'); ?>
                <a data-uk-lightbox data-lightbox-type="iframe" class="oferta" href="/oferta" target="_blank"> оферта</a>
            </h4>
        <?php endif; ?>
        <h4><?=System::Lang('TOTAL_EARNED');?> <?if($total['SUM(summ)'] > 0) echo $total['SUM(summ)']; else echo 0;?> <?=$this->settings['currency'];?></h4>
        <p title="с <?=date("d.m.Y H:i:s", $first_day_past_month);?> по <?=date("d.m.Y H:i:s", $last_day_pay_month);?>"><?=System::Lang('EANED_IN_LAST_YEAR');?> <?if(!empty($last_month_pay['SUM(summ)'])) echo $last_month_pay['SUM(summ)']; else echo 0;?> <?=$this->settings['currency'];?></p>

        <p><?=System::Lang('TOTAL_PAID');?> <?if($total['SUM(pay)'] > 0) echo $total['SUM(pay)']; else echo 0;?> <?=$this->settings['currency'];?></p>

        <?if(!empty($last_pay)):?>
            <p><?=System::Lang('LAST_PAIMENT');?> <?=date("d.m.Y", $last_pay['date']);?> <?=System::Lang('ON');?> <?=$last_pay['pay']; ?> <?=$this->settings['currency'];?></p>
        <?endif;?>

        <?if(isset($total2)) {
            $all_pay = $total['SUM(summ)'];
        } else {
            $all_pay = $total['SUM(summ)'];
            $total2['SUM(summ)'] = $total['SUM(summ)'];
        }

        $rezerv = $all_pay - $total2['SUM(summ)'];
        $ostatok = $total['SUM(summ)'] > $total['SUM(pay)'] ? $total['SUM(summ)'] - $total['SUM(pay)'] : 0;?>

        <p style="margin:1em 0"><?=System::Lang('TOTAL_OWNED');?> <?="$ostatok {$this->settings['currency']}";?><br />
            <?if(isset($params['params']['return_period']) && $params['params']['return_period'] > 0 && $ostatok > 0):?>
                <span style="border-bottom:1px dashed #999; cursor: help" title="с учётом резервирования выплат на <?=$params['params']['return_period'];?> дней"><?=System::Lang('AVAILABLE_PAIMENT_TODAY');?></span>
                <?=$ostatok - $rezerv;?> <?=$this->settings['currency'];?>
            <?endif;?>
        </p>

        <h4><?=System::Lang('STATISTICS');?></h4>
        <p><?=System::Lang('TOTAL_CLIKS');?> <?=$hits; ?></p>
        <p><?=System::Lang('ORDERS_PAID');?> <?=$total_orders;?></p>
        <h4><?=System::Lang('EFFECTIVENES');?></h4>
        <p><?=System::Lang('CONVERTION');?> <?if($hits > 0) {
                $conv = ($total_orders / $hits) *100; echo  round($conv, 2) . ' %';
            } else echo 'нет данных';?>
        </p>

        <p><?=System::Lang('EACH_CLICK_BRINGS');?> <?if($hits > 0) {
                $click = $total['SUM(summ)'] / $hits; echo round($click, 2). ' ' .$this->settings['currency'];
            } else {
                echo 'нет данных';
            }?>
        </p>
        <p>
            <?php
            $api = CyclopsApi::getInstance();
            $response = $api->transfer_money('2990');
            echo 'TF: ';
            print_r($response);
            echo '<br />listPayments: ';
            $response = $api->listPayments(1,50,['identify' => false]);
            print_r($response);
            echo '<br />getPayment: ';
            $paymentId = $response['result']['payments'][0];
            $response = $api->getPayment($paymentId);
            print_r($response);
            echo '<br />create_beneficiary_fl: ';
            $response = $api->create_beneficiary_fl('333333333390','Иван','Лев','1994-10-24','г. Свердловск','683031','2020-01-01', 'г. Петропавловск-Камчатский, пр-кт. Карла Маркса, д. 21/1, офис 305','Иванович','6509',true);
            print_r($response);
            echo '<br />uploadDocumentBeneficiary: ';
            $beneficiary_id=$response["result"]['beneficiary']['id'];
            sleep(20);
            $response = $api->uploadDocumentBeneficiary($beneficiary_id,'contract_offer',"2020-01-10",'0002', $_SERVER['DOCUMENT_ROOT'] . '/ben.pdf');
            print_r($response);
            echo '<br />getDocument: ';
            $document_id=$response["document_id"];
            $response = $api->getDocument($document_id);
            print_r($response);
            echo '<br />create_virtual_account: ';
            $response = $api->create_virtual_account($beneficiary_id);
            print_r($response);
            echo '<br />identifyPayment: ';
            $virtual_account=$response["result"]['virtual_account'];
            $response = $api->identifyPayment($paymentId,[['virtual_account' => $virtual_account,'amount'=>2990]]);
            print_r($response);
            echo '<br />createDeal: ';
            $response = $api->createDeal(2990,[
                [
                    'virtual_account' => $virtual_account,
                    'amount' => 2990
                ]
            ],[
                    [
                        'number' => 1,
                        'type' => 'commission',
                        'amount' => 1990
                    ],[
                        "number" => 2,
                        "type"=> "payment_contract",
                        "amount"=> 1000,
                        "account"=> "40817810711005623214",
                        "bank_code"=> "041806647",
                        "name"=> "Иван Лев",
                        "inn"=> "333333333390"
                    ]
                ]
            );
            print_r($response);
            echo '<br />uploadDocumentDeal: ';
            $deal_id=$response;//['result']['deal_id'];
            $response = $api->uploadDocumentDeal($beneficiary_id,$deal_id,'contract_offer',"2020-01-20",'0002','ben.pdf');
            print_r($response);
            echo '<br />getDocument: ';
            $document_id = $response["document_id"];
            $response = $api->getDocument($document_id);
            print_r($response);
            echo '<br />executeDeal: ';
            $response = $api->executeDeal($deal_id);
            print_r($response);
            echo '<br />getDeal: ';
            $response = $api->getDeal($deal_id);
            print_r($response);
            echo '<br />deactivate_beneficiary: ';
            $response = $api->deactivate_beneficiary($beneficiary_id);
            print_r($response);
            echo '<br />activate_beneficiary: ';
            $response = $api->activate_beneficiary($beneficiary_id);
            print_r($response);
            echo '<br />end ';
            ?>
        </p>
    </div>
</div>

