<?php defined('BILLINGMASTER') or die;
$time_limit = $order_date + ($payment_params['pay_time'] * 86400);

// Сумма прописью.
function str_price($value)
{
    $value = explode('.', number_format($value, 2, '.', ''));

    $f = new NumberFormatter('ru', NumberFormatter::SPELLOUT);
    $str = $f->format($value[0]);

    // Первую букву в верхний регистр.
    $str = mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1, mb_strlen($str));

    // Склонение слова "рубль".
    $num = $value[0] % 100;
    if ($num > 19) {
        $num = $num % 10;
    }
    switch ($num) {
        case 1: $rub = 'рубль'; break;
        case 2:
        case 3:
        case 4: $rub = 'рубля'; break;
        default: $rub = 'рублей';
    }

    return $str . ' ' . $rub . ' ' . $value[1] . ' копеек.';
}

$payment_name = $payment_params['type_org'] == 3 ? "Самозанятый" : "Поставщик";

// Генерация PDF
$html = '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
* {font-family: arial;font-size: 16px;line-height: 16px}
table {margin: 0 0 15px 0; width: 100%; border-collapse: collapse; border-spacing:0}  
table td {padding: 5px; border:1px solid #333}    
table th {padding: 5px;font-weight: bold}
</style>
</head>
<body>
<p style="text-align: right;">Оплату необходимо произвести до '.date("d.m.Y", $time_limit).' г.</p>
<table>
<tbody>
    <tr>
        <td colspan="2" style="border-bottom: none;">'. $payment_params['bank_name'] .'</td>
        <td>БИК</td>
        <td style="border-bottom: none;">'. $payment_params['bik'].'</td>
    </tr>
    <tr>
        <td colspan="2" style="border-top: none; font-size: 12px;">Банк получателя</td>
        <td>Сч. №</td>
        <td style="border-top: none;">'.$payment_params['bank_schet'].'</td>
    </tr>
    <tr>
        <td width="25%">ИНН '.$payment_params['inn'].'</td>
        <td width="30%">КПП '.$payment_params['kpp'].'</td>
        <td width="10%" rowspan="3">Сч. №</td>
        <td width="35%" rowspan="3">'.$payment_params['your_rs'].'</td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom: none;">'.$payment_params['org_name'].'</td>
    </tr>
    <tr>
        <td colspan="2" style="border-top: none; font-size: 12px;">Получатель</td>
    </tr>
</tbody>
</table>

<h1>Счёт на оплату № '.$order_date.' от '. date("d.m.Y", $order_date) .' г.</h1>

<p>'.$payment_name.': <strong>'.$payment_params['org_name'].', ИНН '.$payment_params['inn'].'</strong></p>
<p>Покупатель: <strong>'.$organization.', ИНН '.$inn.'</strong></p>

<table class="list">
    <thead>
        <tr>
            <th width="5%">№</th>
            <th width="45%">Наименование товара, работ, услуг</th>
            <th width="5%">Коли-<br>чество</th>
            <th width="7%">Ед.<br>изм.</th>';
if ($this->settings['nds_enable']>0) {
    $html .= '<th width="7%">НДС %<br></th>
            <th width="15%">Цена, '.$this->settings['currency'].'</th>
            <th width="15%">Сумма, '.$this->settings['currency'].'</th>';
} else {
    $html .= '     
            <th width="18%">Цена, '.$this->settings['currency'].'</th>
            <th width="18%">Сумма, '.$this->settings['currency'].'</th>';
}
$html .= '
        </tr>
    </thead>
    <tbody>';

$total = 0;
$count = 0;
foreach($order_items as $item){
    $count++;
    $html .= '
        <tr>
            <td>'.$count.'</td>
            <td>'.$item['product_name'].'</td>
            <td>1</td>
            <td>шт</td>';
    $html .= '<td>'.$item['price'].'</td>
            <td>'.$item['price'].'</td>
        </tr>';
    $total += $item['price'];
}

$colspan = $this->settings['nds_enable']>0 ? 6 : 5;
$html .= '
    </tbody>
    <tfoot>
        <tr>
            <th colspan="'.$colspan.'">Итого:</th>
            <th>'.$total.' '.$this->settings['currency'].'</th>
        </tr>';
if ($this->settings['nds_enable']>0) {
    $html .= ' 
        <tr>
            <th colspan="'.$colspan.'">В том числе НДС:</th>
            <th>'.$total_nds.' '.$this->settings['currency'].'</th>
        </tr>';
}
$html .= '
        <tr>
            <th colspan="'.$colspan.'">Всего к оплате:</th>
            <th>'.$total.' '.$this->settings['currency'].'</th>
        </tr>
    </tfoot>
</table>

<div class="total">
    <p>Всего наименований '.$count.', на сумму '.$total.' '.$this->settings['currency'].'</p>
    <p><strong>'.str_price($total).'</strong></p>
</div>';

require_once (ROOT . '/vendor/dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$pdf_file = $dompdf->output();
$name_schet = 'schet-'.$order_date.'.pdf';
if (!is_dir("tmp/")) {
    mkdir('tmp');
}

file_put_contents("tmp/$name_schet", $pdf_file);

$title = 'Спасибо!';
$h2 = System::Lang('CUSTOM_SUCCESS_THANK');
$html = "{$payment_params['thanks']}";
$html .= "<p><a href=\"/tmp/$name_schet\" class=\"order_button\" target=\"_blank\">Скачать счёт</a></p>";

require_once __DIR__.'/../success.php';
