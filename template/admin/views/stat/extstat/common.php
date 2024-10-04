<?php defined('BILLINGMASTER') or die;?>
<table class="table" style="margin: 0.5em 0;font-size:12px;">
    <thead>
        <tr>
            <th class="text-left">Период</th>
            <th class="text-right">Всего<br>счетов</th>
            <th class="text-right">На сумму</th>
            <th class="text-right">Всего<br>продаж</th>
            <th class="text-right">На сумму</th>
            <th class="text-right">Принесли<br>партнеры</th>
            <th class="text-right color-red">Не продано</th>
            <th class="text-right">Средний<br>чек</th>
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

            $stat = SummaryStat::getCommonStatistics(null, $end_date); // общие данные статистики до конца месяца
            $stat2 = SummaryStat::getCommonStatistics($start_date, $end_date); // данные статистики в период месяца

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

                <td class="text-right"><!--Всего счетов-->
                    <nobr><span class="green-text">+<?=$stat2['invoices'];?></span></nobr><br>
                    <nobr><small><?=$stat['invoices'];?></strong></small></nobr>
                </td>

                <td class="text-right"><!--На сумму-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_invoices'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_invoices'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Всего продаж-->
                    <nobr><span class="green-text">+<?=$stat2['sales'];?></span></nobr><br>
                    <nobr><small><?=$stat['sales'];?></small></nobr>
                </td>

                <td class="text-right"><!--На сумму-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Принесли партнеры (new)-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_sales_from_partners'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_sales_from_partners'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Не продано-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_invoices'] - $stat2['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_invoices'] - $stat['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <td class="text-right"><!--Средний чек-->
                    <nobr><span class="green-text">+<?=$stat2['sales'] ? number_format($stat2['sum_sales'] / $stat2['sales'], 0, '.','.') : 0;?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=$stat['sales'] ? number_format($stat['sum_sales'] / $stat['sales'], 0, '.','.') : 0;?> <?=$setting['currency'];?></small></nobr>
                </td>
            </tr>
        <?php endfor;?>
    </tbody>
</table>