<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');?>

<div class="main">
    <div class="top-wrap">
        <h1>Создать условие</h1>
    
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>
  
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/conditions/">Автоматизации </a></li>
        <li>Создать условие</li>
    </ul>

    <span id="notification_block"></span>

    <form action="" method="POST">
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Новая автоматизация</h3>
            <ul class="nav_button">
                <li><input type="submit" name="add" value="Создать" class="button save button-white font-bold"></li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/conditions/">Закрыть</a>
                </li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Действия</li>
            </ul>
    
            <div class="admin_form">
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Сегмент</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Фильтрация по:</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="filter_model" type="radio" value="<?=SegmentFilter::FILTER_TYPE_ORDERS;?>"<?=!isset($_GET['filter_model']) || $_GET['filter_model'] == SegmentFilter::FILTER_TYPE_ORDERS ? 'checked="checked"' : '';?>>
                                        <span>Заказам</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="filter_model" type="radio" value="<?=SegmentFilter::FILTER_TYPE_USERS;?>"<?=isset($_GET['filter_model']) && $_GET['filter_model'] == SegmentFilter::FILTER_TYPE_USERS ? 'checked="checked"' : '';?>>
                                        <span>Пользователям</span>
                                    </label>
                                </span>
                            </div>
                        </div>

                        <input type="hidden" name="required_filer" value="1">

                        <div class="col-1-1">
                            <div class="orders-segment-filter">
                                <?require_once(__DIR__.'/../segment_filter/filter2.php');?>
                            </div>
                        </div>
                    </div>

                    <?if(isset($_GET['segment']) && $_GET['segment']):?>
                        <div class="row-line">
                            <div class="col-1-2">
                                <h4 class="h4-border">Основное</h4>

                                <p class="width-100"><label>Название:</label>
                                    <input type="text" name="name" placeholder="Название" required="required">
                                </p>

                                <div class="width-100"><label>Статус:</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input name="status" type="radio" value="1" checked=""><span>Вкл</span></label>
                                        <label class="custom-radio"><input name="status" type="radio" value="0"><span>Откл</span></label>
                                    </span>
                                </div>

                                <p class="width-100"><label>Описание:</label>
                                    <textarea name="desc" rows="3" cols="40"></textarea>
                                </p>
                            </div>

                            <div class="col-1-2">
                                <h4 class="h4-border">Расписание</h4>

                                <div class="width-100"><label>Как выполнять задание?</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input name="use_cron" type="radio" value="1" data-show_on="add_cond_date_settings" checked><span>По расписанию</span></label>
                                        <label class="custom-radio"><input name="use_cron" type="radio" value="0"><span>Сразу</span></label>
                                    </span>
                                </div>

                                <div class="width-100 hidden" id="add_cond_date_settings">
                                    <div class="width-100"><label>Когда выполнять?</label>
                                        <div class="select-wrap">
                                            <select name="params[execute_date_type]">
                                                <option value="1" data-show_on="add_cond_period">По планировщику</option>
                                                <option value="2" data-show_on="add_cond_week_day_settings">Дождаться дня недели и времени</option>
                                                <option value="3" data-show_on="add_cond_specific_date">В конкретное время (один раз)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="width-100 hidden" id="add_cond_week_day_settings">
                                        <div class="width-100"><label>День недели</label>
                                            <div class="select-wrap">
                                                <select name="params[execute_week_day]">
                                                    <option value="1">Пн</option>
                                                    <option value="2">ВТ</option>
                                                    <option value="3">Ср</option>
                                                    <option value="4">Чт</option>
                                                    <option value="5">Пт</option>
                                                    <option value="6">Сб</option>
                                                    <option value="7">Вс</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="width-100"><label>Время</label>
                                            <input type="text" name="params[execute_time]" placeholder="12:00" value="">
                                        </div>
                                    </div>

                                    <div class="width-100 hidden" id="add_cond_period">
                                        <p class="min-label-wrap" title="Интервал выполнения для планировщика">
                                            <label>Интервал выполнения<span class="min-label">мин.</span></label>
                                            <input type="text" name="params[period]" min="0" value="">
                                        </p>
                                    </div>

                                    <div class="width-100 hidden" id="add_cond_specific_date"><label>Когда выполнить?</label>
                                        <div class="datetimepicker-wrap">
                                            <input class="datetimepicker" type="text" autocomplete="off" name="params[execute_specific_date]" placeholder="Дата и время" value="">
                                        </div>
                                    </div>
                                </div>

                                <div class="width-100" id="add_cond_repeat"><label>Исключать из сегмента элементы по которым произошли действия</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input name="repeat" type="radio" value="0" checked><span>Да</span></label>
                                        <label class="custom-radio"><input name="repeat" type="radio" value="1"><span>Нет</span></label>
                                    </span>
                                </div>

                                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                            </div>
                        </div>
                    <?endif;?>
                </div>

                <div>
                    <?require_once(__DIR__ . '/actions.php');?>
                </div>
            </div>
        </div>
    </form>

    <div id="edit_action" class="uk-modal">
        <div class="uk-modal-dialog" style="padding:0;">
            <div class="userbox modal-userbox-3"></div>
        </div>
    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
jQuery('.datetimepicker').datetimepicker({
format:'d.m.Y H:i',
lang:'ru'
});
</script>
<script src="/template/admin/js/conditions.js"></script>
</body>
</html>