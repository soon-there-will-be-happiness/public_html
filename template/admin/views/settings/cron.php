<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>
<style>
.col-1-1 {margin-bottom:60px}
input {background:#cfdde3!important}
</style>
<div class="main">
    <div class="top-wrap">
        <h1>Задания крон</h1>
        <div class="logout">
            <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Примеры заданий планировщика CRON</li>
    </ul>

    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?php endif;?>
    
    <div class="admin_form admin_form--margin-top">
        <div class="row-line">
            <h1>Это примеры заданий для CRON их нужно прописать на хостинге</h1>

            <div class="col-1-1">
                <h4>Письма напоминания о неоплаченных заказах / <span class="small" style="color: #0772A0;">раз в 10 мин</span></h4>
                <?php $order_cron =  System::getCronLog('order_cron');
                if (!empty($order_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $order_cron['last_run']);?>
                        <?php if($order_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/task/order_cron.php">
            </div>

            <div class="col-1-1">
                <h4>Автоматизации / <span class="small" style="color: #0772A0;">раз в 20 минут</span></h4>
                <?php $cond_cron =  System::getCronLog('cond_cron');
                if (!empty($cond_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $cond_cron['last_run']);?>
                        <?php if($cond_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                <?php endif;?>
                   <input type="text" value="php <?php echo ROOT ?>/task/cond_cron.php">
            </div>


            <div class="col-1-1">
                <h4>Работа рассрочки / <span class="small" style="color: #0772A0;">раз в 15 минут</span></h4>
                <?php $installment_cron =  System::getCronLog('installment_cron');
                
                if (!empty($installment_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $installment_cron['last_run']);?>
                        <?php if($installment_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/task/installment_cron.php">
            </div>

            <?php $courses = System::CheckExtensension('courses', 1);
            if($courses):?>
                <div class="col-1-1">
                    <h4>Автопроверка заданий / <span class="small" style="color: #0772A0;">раз в 10 минут</span></h4>
                    <?php $course_cron =  System::getCronLog('course_cron');
                    
                    if (!empty($course_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $course_cron['last_run']);?>
                        <?php if($course_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                    <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/task/course_cron.php">
                </div>
            <?php endif;?>

            <?php $training = System::CheckExtensension('training', 1);
            if($training):?>
                <div class="col-1-1">
                    <h4>Автопроверка заданий (Тренинги 2.0) / <span class="small" style="color: #0772A0;">раз в 10 минут</span></h4>
                    <?php $training_cron =  System::getCronLog('training_cron');
                    
                    
                    if (!empty($training_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $training_cron['last_run']);?>
                        <?php if($training_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                    <?php endif;?>
                    
                    <input type="text" value="php <?php echo ROOT ?>/task/training_cron.php">
                </div>
            <?php endif;?>
            
            <?php $flows = System::CheckExtensension('learning_flows', 1);
            if($flows):?>
                <div class="col-1-1">
                    <h4>Потоки / <span class="small" style="color: #0772A0;">раз в 30 минут</span></h4>
                    <?php $flow_cron =  System::getCronLog('flow_cron');
                    
                    if (!empty($flow_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $flow_cron['last_run']);?>
                        <?php if($flow_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                    <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/task/flow_cron.php">
                </div>
            <?php endif;?>

            <?php $responder = System::CheckExtensension('responder', 1);
            if($responder):?>
                <div class="col-1-1">
                    <h4>Отправка писем рассылки / <span class="small" style="color: #0772A0;">раз в 1 минуту</span></h4>
                    <?php $email_cron =  System::getCronLog('email_cron');
                    
                    if (!empty($email_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $email_cron['last_run']);?>
                        <?php if($email_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                    <?php endif;?>
                    
                    <input type="text" value="php <?php echo ROOT . '/task/email_cron.php'?>">
                </div>
            <?php endif;?>

            <?php $membership = System::CheckExtensension('membership', 1);
            if($membership):?>
                <div class="col-1-1">
                    <h4>Проверка истечения платного доступа / <span class="small" style="color: #0772A0;">раз в 10 минут</span></h4>
                    <?php $member_cron =  System::getCronLog('member_cron');
                    
                    if (!empty($member_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $member_cron['last_run']);?>
                        <?php if($member_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                    <?php endif;?>
                    
                    <input type="text" value="php <?php echo ROOT ?>/task/member_cron.php">
                    
                </div>
            <?php endif;?>

            <?php $forum_status = System::CheckExtensension('forum2', 1);
            if($forum_status):?>
                <div class="col-1-1">
                    <h4>Отправка уведомлений для форума / <span class="small" style="color: #0772A0;">раз в сутки</span></h4>
                    <?php $forum_cron =  System::getCronLog('forum_cron');
                    if (!empty($forum_cron)):?>
                        <p>Последний запуск был: <?=date("d-m-Y H:i:s", $forum_cron['last_run']);
                            if($forum_cron['jobs_error'] == 1):?>
                                <div style="color:red"> Есть ошибки!</div>
                            <?php endif;?>
                        </p>
                    <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/task/forum_cron.php">
                </div>
            <?php endif;?>

            <div class="col-1-1">
                <h4>Системные события / <span class="small" style="color: #0772A0;">раз в сутки</span></h4>
                <?php $system_cron =  System::getCronLog('system_cron');
                if (!empty($system_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $system_cron['last_run']);?>
                        <?php if($system_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div>
                        <?php endif;?>
                    </p>
                <?php endif;?>
                <input type="text" value="php <?php echo ROOT;?>/task/system_cron.php">
            </div>

            <?php $autobackup_status = System::CheckExtensension('autobackup', 1);
            if($autobackup_status):?>
                <div class="col-1-1">
                    <h4>Автобекапы / <span class="small" style="color: #0772A0;">раз в 60 минут</span></h4>
                    <?php $autobackup_cron =  System::getCronLog('autobackup');
                    if (!empty($autobackup_cron)):?>
                        <p>Последний запуск был: <?=date("d-m-Y H:i:s", $autobackup_cron['last_run']);
                        if($autobackup_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div>
                        <?php endif;?>
                        </p>
                    <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/extensions/autobackup/task/autobackup_cron.php">
                </div>
            <?php endif;?>


        </div>
    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>