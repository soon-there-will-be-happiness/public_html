<?php defined('BILLINGMASTER') or die;
$stats = $categories = [];
for ($i = $current_month; $i >= $current_month - 12; $i--) {
    $year = $i >= 1 ? date('Y') : date('Y') - 1;
    $month = $i < 1 ? $i + 12 : $i;

    $end_month = $month + 1 <= 12 ? $month + 1 : 1;
    $end_year = $i > 0 ? date('Y') : date('Y') - 1;
    $end_year = $month < 12 ? $end_year : $end_year + 1;

    $start_date = strtotime("1-$month-$year");
    $end_date = strtotime("1-$end_month-$end_year");

    $stat = SummaryStat::getCategoryStatistics(null, $end_date); // общие данные статистики до конца месяца
    $stat2 = SummaryStat::getCategoryStatistics($start_date, $end_date); // данные статистики в период месяца

    $stats[] = [
        'stat' => $stat,
        'stat2' => $stat2,
        'month' => $month,
        'year' => $year,
        'end_month' => $end_month,
        'end_year' => $end_year,
    ];

    if ($i == $current_month) {
        $last_stat = $stat;
    }
}?>

<table class="table" style="margin: 0.5em 0;font-size:12px;">
    <thead>
        <tr>
            <th class="text-left">Период</th>
            <th class="text-right">Всего продаж</th>
            <th class="text-right">На сумму</th>
            <?php foreach ($last_stat['cat_data'] as $category):?>
                <th class="text-right"><?=$category['cat_name'] ? $category['cat_name'] : 'Прочее';?></th>
            <?php endforeach;?>
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
                    <nobr><span class="green-text">+<?=$stat2['sales'];?></span></nobr><br>
                    <nobr><small><?=$stat['sales'];?></strong></small></nobr>
                </td>

                <td class="text-right"><!--На сумму-->
                    <nobr><span class="green-text">+<?=number_format($stat2['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></span></nobr><br>
                    <nobr><small><?=number_format($stat['sum_sales'], 0, '.','.');?> <?=$setting['currency'];?></small></nobr>
                </td>

                <?foreach ($last_stat['cat_data'] as $cat_id => $category):
                    $data1 = isset($stat2['cat_data'][$cat_id]) ? $stat2['cat_data'][$cat_id] : null;
                    $data2 = isset($stat['cat_data'][$cat_id]) ? $stat['cat_data'][$cat_id] : null;?>
                    <td class="text-right"><!--Категории-->
                        <nobr><span class="green-text"><?=$data1 ? $data1['sales'].' ('.number_format($data1['sum'], 0, '.','.').' '.$setting['currency'].')' : '--'?></span></nobr><br>
                        <nobr><small><?=$data2 ? $data2['sales'].' ('.number_format($data2['sum'], 0, '.','.').' '.$setting['currency'].')' : '--';?></strong></small></nobr>
                    </td>
                <?endforeach;?>
            </tr>
        <?php endfor;?>
    </tbody>
</table>