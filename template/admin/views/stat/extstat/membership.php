<?php defined('BILLINGMASTER') or die;?>
<table class="table" style="margin: 0.5em 0;font-size:12px;">
    <thead>
        <tr>
            <th class="text-left">Период</th>
            <th class="text-right">Всего продаж</th>
            <th class="text-right">На сумму</th>
            <th class="text-right">Первых продаж</th>
            <th class="text-right">Повторных продаж</th>
            <th class="text-right">С активной<br>подпиской</th>
        </tr>
    </thead>

    <tbody>
        <?php $stats = [];
        for ($i = $current_month; $i >= $current_month - 12; $i--) {
            $year = $i >= 1 ? date('Y') : date('Y') - 1;
            $month = $i < 1 ? $i + 12 : $i;

            $end_month = $month + 1 <= 12 ? $month + 1 : 1;
            $end_year = $i > 0 ? date('Y') : date('Y') - 1;
            $end_year = $month < 12 ? $end_year : $end_year + 1;

            $start_date = strtotime("1-$month-$year");
            $end_date = strtotime("1-$end_month-$end_year");

            $stat = SummaryStat::getMemberStatistics(null, $end_date); // общие данные статистики до конца месяца
            $stat2 = SummaryStat::getMemberStatistics($start_date, $end_date); // данные статистики в период месяца

            $stats[] = [
                'stat' => $stat,
                'stat2' => $stat2,
                'month' => $month,
                'year' => $year,
                'end_month' => $end_month,
                'end_year' => $end_year,
            ];
        }

        for($i = 0; $i <= 12; $i++):
            $stat = $stats[$i]['stat'];
            $stat2 = $stats[$i]['stat2'];
            $month = $stats[$i]['month'];
            $year = $stats[$i]['year'];?>

            <tr>
                <td class="text-left"><!--Период-->
                    <nobr><?=$i == 0 ? 'Текущий месяц' : "{$months[$month]} $year";?></nobr>
                </td>

                <td class="text-right"><!--Всего продаж-->
                    <nobr><span class="green-text">+<?=$stat2['sales'];?></span></nobr><br>
                    <nobr><small><?=$stat['sales'];?></strong></small></nobr>
                </td>

                <td class="text-right"><!--На сумму-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Первых продаж-->
                    <nobr><span class="green-text">+<?=$stat2['first_sales'].' ('.number_format($stat2['first_sales_sum'], 0, '.','.').' '.$setting['currency'].')';?></span></nobr><br>
                    <nobr><small><?=$stat['first_sales'].' ('.number_format($stat['first_sales_sum'], 0, '.','.').' '.$setting['currency'].')';?></small></nobr>
                </td>

                <td class="text-right"><!--Повторных продаж-->
                    <nobr><span class="green-text">+<?=$stat2['repeat_sales'].' ('.number_format($stat2['repeat_sales_sum'], 0, '.','.').' '.$setting['currency'].')';?></span></nobr><br>
                    <nobr><small><?=$stat['repeat_sales'].' ('.number_format($stat['repeat_sales_sum'], 0, '.','.').' '.$setting['currency'].')';?></small></nobr>
                </td>

                <td class="text-right"><!--С активной подпиской-->
                    <span class="green-text">+<?=$stat2['users_with_active_subs'];?></span><br>
                    <nobr><small><?=$stat['users_with_active_subs'];?></small></nobr>
                </td>
            </tr>
        <?php endfor;?>
    </tbody>
</table>