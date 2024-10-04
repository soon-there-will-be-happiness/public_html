<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Список акций</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/products/">Продукты</a></li>
        <li>Акции</li>
    </ul>
    
    <span id="notification_block"></span>

    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li class="nav_gorizontal__parent-wrap">
                <a class="button-red-rounding" href="/admin/sales/add/">Создать акцию</a>
            </li>
            <li><a class="settings-link" href="/admin/sales/page/"><i class="icon-settings"></i></a></li>
        </ul>
    </div>

    <div class="filter admin_form">
        <form action="/admin/sales/" method="GET">
            <div class="filter-row filter-flex-end">
                <div class="filter-1-3">
                    <input type="text" name="name" value="<?=$filter['name'] ? $filter['name'] : '';?>" placeholder="Название">
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="type">
                            <option value="">Тип акции</option>
                            <option value="1"<?php if($filter['type'] == '1') echo ' selected="selected"';?>>Красная цена</option>
                            <option value="2,9"<?php if($filter['type'] == '2,9') echo ' selected="selected"';?>>Промокод</option>
                            <option value="5"<?php if($filter['type'] == '5') echo ' selected="selected"';?>>Скидка в корзине</option>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="category">
                            <option value="">Все категории</option>
                            <option value="1"<?php if($filter['category'] == '1') echo ' selected="selected"';?>>Ручной</option>
                            <option value="2,9"<?php if($filter['category'] && $filter['category'] == '2,9') echo ' selected="selected"';?>>Авто</option>
                        </select>
                    </div>
                </div>

                <div class="filter-bottom">
                    <div>
                        <div class="order-filter-result">
                            <?php if($filter && $filter['is_filter']):?>
                                <div><p>Отфильтровано: <?=$total_items;?> объекта</p></div>
                            <?php endif;?>
                        </div>
                    </div>

                    <div class="button-group">
                        <?php if($filter['is_filter']):?>
                            <a class="red-link" href="/admin/sales?reset">Сбросить</a>
                        <?php endif;?>

                        <input class="button-blue-rounding" type="submit" name="filter" value="Найти">
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?php endif;?>
    
    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-left">Название</th>
                        <th class="text-left">Промокод</th>
                        <th class="text-left">Закончится</th>
                        <th class="text-left">Категория</th>
                        <th class="td-last"></th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php if($sales):
                        foreach($sales as $sale):?>
                            <tr<?php if($sale['status'] == 0) echo ' class="off"';?>>
                                <td class="text-left">
                                    <div class="custom-field-name status-info-wrap">
                                        <div class="custom-field-status status-info">
                                            <i class="status-<?=$sale['status'] ? 'on' :'off';?>"></i>
                                        </div>

                                        <a href="/admin/sale/edit/<?=$sale['id'];?>">
                                            <?=$sale['name'];?>
                                        </a>
                                    </div>
                                </td>

                                <td class="text-left">
                                    <?php if($sale['type'] == 5):?>
                                        Скидка в корзине
                                    <?php else:?>
                                        <?=$sale['type'] != 1 ? ("{$sale['promo_code']} {$sale['discount']}".($sale['discount_type'] == 'percent' ? '%' : $setting['currency'])) : 'Красная цена';?>
                                    <?php endif;?>
                                </td>

                                <td class="text-left">
                                    <?=date('d.m.Y', $sale['finish']);?>
                                </td>

                                <td class="text-left">
                                    <?=$sale['client_email'] !== null ? 'Авто' : 'Ручной';?>
                                </td>

                                <td class="td-last">
                                    <a class="link-delete" onclick="return confirm('Вы уверены?')" href="/admin/sales/del/<?=$sale['id'];?>?token=<?=$_SESSION['admin_token'];?>" title="Удалить">
                                        <i class="fas fa-times" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach;
                    endif;?>
                </tbody>
            </table>
        </div>
    </div>
    <?=$pagination->get();?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>