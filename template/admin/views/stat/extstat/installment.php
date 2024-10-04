<?php defined('BILLINGMASTER') or die;
$stats = [];
for ($i = $current_month; $i >= $current_month - 12; $i--) {
    $year = $i >= 1 ? date('Y') : date('Y') - 1;
    $month = $i < 1 ? $i + 12 : $i;

    $end_month = $month + 1 <= 12 ? $month + 1 : 1;
    $end_year = $i > 0 ? date('Y') : date('Y') - 1;
    $end_year = $month < 12 ? $end_year : $end_year + 1;

    $start_date = strtotime("1-$month-$year");
    $end_date = strtotime("1-$end_month-$end_year");

    $stat = SummaryStat::getInstallmentStatistics(null, $end_date); // общие данные статистики до конца месяца
    $stat2 = SummaryStat::getInstallmentStatistics($start_date, $end_date); // данные статистики в период месяца

    $stats[] = [
        'stat' => $stat,
        'stat2' => $stat2,
        'month' => $month,
        'year' => $year,
        'end_month' => $end_month,
        'end_year' => $end_year,
    ];
}?>

<table class="table" style="margin: 0.5em 0;font-size:12px;">
    <thead>
        <tr>
            <th class="text-left">Период</th>
            <th class="text-right">Всего<br>продаж</th>
            <th class="text-right">На сумму</th>
            <th class="text-right">Новых<br>рассрочек</th>
            <th class="text-right">Создано<br>обязательств</th>
            <th class="text-right">Должны<br>оплатить</th>
            <th class="text-right">Фактически<br>оплачено</th>
            <th class="text-right">Просрочили</th>
        </tr>
    </thead>

    <tbody>
        <?for($i = 0; $i <= 12; $i++):
            $stat = $stats[$i]['stat'];
            $stat2 = $stats[$i]['stat2'];
            $month = $stats[$i]['month'];
            $year = $stats[$i]['year'];?>

            <tr>
                <td class="text-left"><!--Период-->
                    <nobr><?=$i == 0 ? 'Текущий месяц' : "{$months[$month]} $year";?></nobr>
                </td>

                <td class="text-right"><!--Всего продаж-->
                    <nobr><span class="green-text">+<?=$stat2['sales']['count'];?></span></nobr><br>
                    <nobr><small><?=$stat['sales']['count'];?></strong></small></nobr>
                </td>

                <td class="text-right"><!--На сумму-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sales']['sum'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sales']['sum'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Новых рассрочек-->
                    <nobr><span class="green-text">+<?="{$stat2['new_sales']['count']} (".number_format($stat2['new_sales']['sum'], 0, '.','.').' '.$setting['currency'].')';?></span></nobr><br>
                    <nobr><small><?="{$stat['new_sales']['count']} (".number_format($stat['new_sales']['sum'], 0, '.','.').' '.$setting['currency'].')';?></strong></small></nobr>
                </td>

                <td class="text-right"><!--Создано обязательств-->
                    <nobr><span class="green-text">+<?="{$stat2['total_obligations']['count']} (".number_format($stat2['total_obligations']['sum'], 0, '.','.').' '.$setting['currency'].')';?></span></nobr><br>
                    <nobr><small><?="{$stat['total_obligations']['count']} (".number_format($stat['total_obligations']['sum'], 0, '.','.').' '.$setting['currency'].')';?></strong></small></nobr>
                </td>

                <td class="text-right"><!--Должны оплатить-->
                    <nobr><span class="green-text"><?=$stat2['sum_sales_not_paid'] ? '+'.number_format($stat2['sum_sales_not_paid'], 0, '.','.').' '.$setting['currency'].'': '--';?></span></nobr><br>
                    <nobr><small><?=$stat['sum_sales_not_paid'] ? number_format($stat['sum_sales_not_paid'], 0, '.','.').' '.$setting['currency'].'' : '--';?></small></nobr>
                </td>

                <td class="text-right"><!--Фактически оплачено-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_sales_paid'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_sales_paid'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Просрочили-->
                    <nobr><span class="green-text"><?=$stat2['expired'] ? "+{$stat2['expired']['count']} (".number_format($stat2['expired']['sum'], 0, '.','.').' '.$setting['currency'].')' : '--';?></span></nobr><br>
                    <nobr><small><?=$stat['expired'] ? "{$stat['expired']['count']} (".number_format($stat['expired']['sum'], 0, '.','.').' '.$setting['currency'].')' : '--';?></strong></small></nobr>
                </td>
            </tr>
        <?php endfor;?>
    </tbody>
</table>