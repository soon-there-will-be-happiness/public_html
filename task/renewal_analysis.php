<?php define('BILLINGMASTER', 1); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1 раз в час - норм.

// Настройки системы
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);


require_once (ROOT . '/components/autoload.php');
require_once (ROOT . '/vendor/autoload.php');
System::enableLongWaitForQueries();
//use PhpOffice\PhpSpreadsheet\Spreadsheet;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = Db::getConnection();

$setting = System::getSetting();
$now = time();
$name_jobs = "week_analize";

$thisDay = strtotime("today")+3600*3-3600*24;
$firstDayOfWeek = $thisDay - 3600*24*6;
var_dump($firstDayOfWeek);
$weekOrders = Order::OrderWeek($firstDayOfWeek,$thisDay);
//$lastWeekOrders = Order::OrderWeek();

//var_dump(Member::getMemberListWithFilter(''));
$data=Member::getMemberListWithFilter('');
foreach ($data as &$row) {
    $row['begin'] = date("Y-m-d", $row['begin']);
    $row['end'] = date("Y-m-d", $row['end']);
    $row['create_date'] = date("Y-m-d", $row['create_date']);
    $row['last_update'] = date("Y-m-d", $row['last_update']);
}

// 🔹 Файл Excel, который будем создавать
$file_path = "/home/t/thecarsx/dev.kemstatj.rf/public_html/images/multi_sheet.xlsx";
$zip_path = "/home/t/thecarsx/dev.kemstatj.rf/public_html/images/multi_sheet.zip";

// Создаём объект Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Заполняем заголовки (первая строка)
$headers = array_keys($data[0]);
$col = 1;
foreach ($headers as $header) {
    $sheet->setCellValueByColumnAndRow($col, 1, $header);
    $col++;
}

// Заполняем данные (начиная со второй строки)
$rowNum = 2;
foreach ($data as $row) {
    $col = 1;
    foreach ($row as $value) {
        $sheet->setCellValueByColumnAndRow($col, $rowNum, $value ?? "");
        $col++;
    }
    $rowNum++;
}

// Указываем путь для сохранения файла
$filePath = __DIR__ . '/data.xlsx';

// Создаём объект Writer и сохраняем файл
$writer = new Xlsx($spreadsheet);
$writer->save($filePath);

echo "Файл сохранён в: " . $filePath;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Ошибка: Невозможно создать архив!");
}
$zip->addFile($file_path, basename($file_path));
$zip->close();

$send = Connect::sendMessagesByEmail('karsakovkirilo@gmail.com','',[],'https://dev.xn--80ajojzgb4f.xn--p1ai/images/multi_sheet.zip');//'https://dev.xn--80ajojzgb4f.xn--p1ai/images/О_Реклама_Финаев.xlsm');// /home/t/thecarsx/dev.kemstatj.rf/public_html/images/photo_2023-02-10_11-05-17.jpg');
#$send = Connect::sendMessagesByEmail('karsakovkirilo@gmail.com','No text');


//Пишем в таблицу логов крона
//TODO в дальнейшем надо сделать модели и класс если этот функционал будет расширятся
$db = Db::getConnection();  
$sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run) VALUES(:name_jobs, :last_run)  ON DUPLICATE KEY UPDATE last_run = :last_run";
$result = $db->prepare($sql);
$result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
$result->bindParam(':last_run', $now, PDO::PARAM_INT);
$result->execute();

//TODO В дальнейшем, все записи и отправки почты в 
//кронзадачах обернуть в try catch и в случае не удачи 

//писать ошибки в лог в таблицу cron_logs позже добавим там колонку error