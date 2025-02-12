<?php define('BILLINGMASTER', 1); 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
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

$db = Db::getConnection();

$setting = System::getSetting();
$now = time();
$name_jobs = "week_analize";

$thisDay = strtotime("today")+3600*3-3600*24;
$firstDayOfWeek = $thisDay - 3600*24*6;
var_dump($firstDayOfWeek);
$weekOrders = Order::OrderWeek($firstDayOfWeek,$thisDay);
$lastWeekOrders = Order::OrderWeek();


// 🔹 Файл Excel, который будем создавать
$file_path = "/home/t/thecarsx/dev.kemstatj.rf/public_html/images/multi_sheet.xlsx";
$zip_path = "/home/t/thecarsx/dev.kemstatj.rf/public_html/images/multi_sheet.zip";

// 1️⃣ Создаем новый Excel-документ
$spreadsheet = new Spreadsheet();

// 🔹 Лист 1 (Sheet1)
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Лист 1');
$sheet1->setCellValue('A1', 'Название');
$sheet1->setCellValue('B1', 'Цена');
$sheet1->setCellValue('A2', 'Продукт A');
$sheet1->setCellValue('B2', '100$');
$sheet1->setCellValue('A3', 'Продукт B');
$sheet1->setCellValue('B3', '150$');

// 🔹 Лист 2 (Sheet2)
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->setActiveSheetIndex(1);
$sheet2->setTitle('Лист 2');
$sheet2->setCellValue('A1', 'Код');
$sheet2->setCellValue('B1', 'Категория');
$sheet2->setCellValue('A2', '001');
$sheet2->setCellValue('B2', 'Электроника');
$sheet2->setCellValue('A3', '002');
$sheet2->setCellValue('B3', 'Бытовая техника');

// 🔹 Возвращаемся на первый лист
$spreadsheet->setActiveSheetIndex(0);

// 2️⃣ Сохраняем Excel-файл
$writer = new Xlsx($spreadsheet);
$writer->save($file_path);

// 3️⃣ Проверяем, существует ли файл перед отправкой
if (!file_exists($file_path)) {
    die("Ошибка: Файл не создан!");
}

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