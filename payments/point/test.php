<?php defined('BILLINGMASTER') or die;
/*$com_per=2.7;
$luft = 1;
$payments_tochkas=Payments::getAllPaymentsTochka('unmatched');
foreach ($payments_tochkas as $payments_tochka) {
    $matched_payments=Payments::getJoinedOrdersAndPayments(); // 2990 2990 2990 2990 11960 // 2990
    $amount=floatval($payments_tochka["amount"]); //  11637,08 // 2909,27
    $remaind = 0;
    foreach($matched_payments as $matched_payment){
        if ($amount >= $matched_payment["amount"]*(1-($com_per+$luft/100))) {
            Payments::updatePaymentId($matched_payment['matched_payment_id'],$payments_tochka['id']);
            $amount-=floatval($matched_payment["amount"]);
            $remaind -= $matched_payment["amount"]*(1-($com_per+$luft/100));
        } else {
            continue;
        }
    }
    if ($amount>=$remaind && $amount<=0) {
        Payments::updatePaymentStatus($payments_tochka['id'],'matched');
    } else {
        Log::add(1, 'unmatched pay', [
            'payments_tochka' => $payments_tochka['id'],
            ], 'cyclops_match');
    }


    if($i>0){
        Payments::updatePaymentStatus($payments_tochka['id'],'matched');
        for($j=0;$j<$i;$j++){
            $matched_payment=$matched_payments[$j];
            Payments::updatePaymentId($matched_payment['matched_payment_id'],$payments_tochka['id']);
        }
    }
    else{
        Log::add(1, 'unmatched pay', [
            'payments_tochka' => $payments_tochka['id'],
            ], 'cyclops_match');
    }
}*/

$com_per = 2.7; // Комиссия
$luft = 1; // Погрешность
$payments_tochkas = Payments::getAllPaymentsTochka('unmatched'); // Список платежей из Точки

foreach ($payments_tochkas as $payments_tochka) { // 7765
    $matched_payments = Payments::getJoinedOrdersAndPayments(); // Список продуктов 2990 2990/ 1000 2990 1000
    $total_commission = 0; // Общая комиссия для текущего платежа 258,26

    foreach ($matched_payments as $matched_payment) {
        // Порог для сопоставления с учетом комиссии и погрешности
        $threshold = $matched_payment["amount"] * (1 - ($com_per + $luft) / 100); // 2879,37 // 963
        Log::add(1, 'matched_payment', [
            'matched_payment' => $matched_payment,
            'if' => $payments_tochka["amount"] >= $threshold,
            'tocka'=>  $payments_tochka["amount"],
            'threshold' => $threshold,
        ], 'cyclops_match');
        // Проверяем, можно ли сопоставить текущий платеж
        if ($payments_tochka["amount"] >= $threshold) { // 1043,26 >= 2879,37 // 963
            // Обновляем сопоставление платежа с продуктом
            Payments::updatePaymentId($matched_payment['matched_payment_id'], $payments_tochka['id']);

            // Уменьшаем сумму текущего платежа с учетом комиссии
            $payments_tochka["amount"] -= $matched_payment["amount"] * (1 - ($com_per + $luft) / 100); // 1043,26

            // Добавляем в общую комиссию
            $total_commission += $matched_payment["amount"] * ($com_per + $luft) / 100; // 258,26
        }
    }

    // Платеж полностью сопоставлен
    Payments::updatePaymentStatus($payments_tochka['id'], 'matched');
    // Проверяем результат цикла
    if ($payments_tochka["amount"] >= $total_commission) {
        // Логируем оставшийся остаток
        Log::add(1, 'unmatched pay', [
            'payment_id' => $payments_tochka['id'],
            'remaining_amount' => $payments_tochka["amount"],
            'total_commission' => $total_commission,
        ], 'cyclops_match');
    }
}


$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Действие добавления платежа в payments_tochka
    if (isset($_POST['action']) && $_POST['action'] === 'add_payment_tochka') {
        $payment_id = $_POST['payment_id'];
        $amount = $_POST['amount'];
        $payment_date = $_POST['payment_date'];
        $description = $_POST['description'];
        $status = $_POST['status'];

        if (Payments::addPaymentTochka($payment_id, $amount, $payment_date, $description, $status)) {
            $message = 'Платеж успешно добавлен в payments_tochka!';
        } else {
            $message = 'Ошибка добавления платежа!';
        }
    }

    // Действие получения всех платежей из payments_tochka
    if (isset($_POST['action']) && $_POST['action'] === 'get_all_payments_tochka') {
        $payments = Payments::getAllPaymentsTochka($_POST['filter_status'] ?? null);
    }

    // Действие добавления сопоставленного платежа в matched_payments
    if (isset($_POST['action']) && $_POST['action'] === 'add_matched_payment') {
        $payment_id = $_POST['payment_id'];
        $system_record_id = $_POST['system_record_id'];
        $amount = $_POST['amount'];

        if (Payments::addMatchedPayment($payment_id, $system_record_id, $amount)) {
            $message = 'Сопоставленный платеж успешно добавлен!';
        } else {
            $message = 'Ошибка добавления сопоставленного платежа!';
        }
    }

    // Действие получения всех сопоставленных платежей
    if (isset($_POST['action']) && $_POST['action'] === 'get_all_matched_payments') {
        $matchedPayments = Payments::getAllMatchedPayments();
    }

    // Действие обновления статуса в payments_tochka
    if (isset($_POST['action']) && $_POST['action'] === 'update_payment_status') {
        $id = $_POST['payment_id'];
        $status = $_POST['new_status'];

        if (Payments::updatePaymentStatus($id, $status)) {
            $message = 'Статус успешно обновлен!';
        } else {
            $message = 'Ошибка обновления статуса!';
        }
    }

    // Действие удаления платежа из payments_tochka
    if (isset($_POST['action']) && $_POST['action'] === 'delete_payment') {
        $id = $_POST['payment_id'];

        if (Payments::deletePaymentTochka($id)) {
            $message = 'Платеж успешно удален!';
        } else {
            $message = 'Ошибка удаления платежа!';
        }
    }

    // Действие удаления сопоставленного платежа из matched_payments
    if (isset($_POST['action']) && $_POST['action'] === 'delete_matched_payment') {
        $id = $_POST['payment_id'];

        if (Payments::deleteMatchedPayment($id)) {
            $message = 'Сопоставленный платеж успешно удален!';
        } else {
            $message = 'Ошибка удаления сопоставленного платежа!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Платежи и сопоставленные платежи</title>
</head>
<body>
<h1>Управление платежами и сопоставленными платежами</h1>

<?php if ($message): ?>
    <p style="color: green;"><?= $message ?></p>
<?php endif; ?>

<!-- Форма добавления платежа в payments_tochka -->
<h2>Добавить платеж (payments_tochka)</h2>
<form method="POST">
    <input type="hidden" name="action" value="add_payment_tochka">
    <label>Идентификатор платежа (payment_id):</label>
    <input type="text" name="payment_id" required><br>
    <label>Сумма (amount):</label>
    <input type="number" step="0.01" name="amount" required><br>
    <label>Дата платежа (payment_date):</label>
    <input type="datetime-local" name="payment_date" required><br>
    <label>Описание (description):</label>
    <textarea name="description"></textarea><br>
    <label>Статус (status):</label>
    <select name="status">
        <option value="unmatched">unmatched</option>
        <option value="matched">matched</option>
    </select><br>
    <button type="submit">Добавить</button>
</form>

<!-- Форма получения всех платежей из payments_tochka -->
<h2>Получить все платежи (payments_tochka)</h2>
<form method="POST">
    <input type="hidden" name="action" value="get_all_payments_tochka">
    <label>Фильтр по статусу:</label>
    <select name="filter_status">
        <option value="">Все</option>
        <option value="unmatched">unmatched</option>
        <option value="matched">matched</option>
    </select><br>
    <button type="submit">Получить платежи</button>
</form>

<?php if (!empty($payments)): ?>
    <h3>Результаты платежей:</h3>
    <table border="1">
        <thead>
        <tr>
            <th>ID</th>
            <th>Payment ID</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Description</th>
            <th>Status</th>
            <th>Created At</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $payment): ?>
            <tr>
                <td><?= $payment['id'] ?></td>
                <td><?= $payment['payment_id'] ?></td>
                <td><?= $payment['amount'] ?></td>
                <td><?= $payment['payment_date'] ?></td>
                <td><?= $payment['description'] ?></td>
                <td><?= $payment['status'] ?></td>
                <td><?= $payment['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Форма добавления сопоставленного платежа -->
<h2>Добавить сопоставленный платеж (matched_payments)</h2>
<form method="POST">
    <input type="hidden" name="action" value="add_matched_payment">
    <label>Идентификатор платежа (payment_id):</label>
    <input type="text" name="payment_id" required><br>
    <label>Идентификатор системной записи (system_record_id):</label>
    <input type="text" name="system_record_id" required><br>
    <label>Сумма (amount):</label>
    <input type="number" step="0.01" name="amount" required><br>
    <button type="submit">Добавить сопоставленный платеж</button>
</form>

<!-- Форма получения всех сопоставленных платежей -->
<h2>Получить все сопоставленные платежи</h2>
<form method="POST">
    <input type="hidden" name="action" value="get_all_matched_payments">
    <button type="submit">Получить сопоставленные платежи</button>
</form>

<?php if (!empty($matchedPayments)): ?>
    <h3>Результаты сопоставленных платежей:</h3>
    <table border="1">
        <thead>
        <tr>
            <th>ID</th>
            <th>Payment ID</th>
            <th>System Record ID</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($matchedPayments as $payment): ?>
            <tr>
                <td><?= $payment['id'] ?></td>
                <td><?= $payment['payment_id'] ?></td>
                <td><?= $payment['system_record_id'] ?></td>
                <td><?= $payment['amount'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Форма обновления статуса платежа -->
<h2>Обновить статус платежа</h2>
<form method="POST">
    <input type="hidden" name="action" value="update_payment_status">
    <label>ID платежа:</label>
    <input type="number" name="payment_id" required><br>
    <label>Новый статус:</label>
    <select name="new_status">
        <option value="unmatched">unmatched</option>
        <option value="matched">matched</option>
    </select><br>
    <button type="submit">Обновить статус</button>
</form>

<!-- Форма удаления платежа -->
<h2>Удалить платеж</h2>
<form method="POST">
    <input type="hidden" name="action" value="delete_payment">
    <label>ID платежа для удаления:</label>
    <input type="number" name="payment_id" required><br>
    <button type="submit">Удалить платеж</button>
</form>

<!-- Форма удаления сопоставленного платежа -->
<h2>Удалить сопоставленный платеж</h2>
<form method="POST">
    <input type="hidden" name="action" value="delete_matched_payment">
    <label>ID сопоставленного платежа для удаления:</label>
    <input type="number" name="payment_id" required><br>
    <button type="submit">Удалить сопоставленный платеж</button>
</form>

</body>
</html>
