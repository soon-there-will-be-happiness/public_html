<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Настройки</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/conditions/"><?=System::Lang('CONDITIONS');?></a></li>
        <li>Настройки</li>
    </ul>

    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?php endif;
    if(isset($_GET['fail'])):?>
        <div class="admin_warning">Не удалось удалить список очередей</div>
    <?php endif;?>

    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <?if($cond_queues):?>
                <h4 class="no-border mb-20">Очередь автоматизаций</h4>

                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Дата создания</th>
                            <th class="td-last">Статус</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($cond_queues as $cond_queue):?>
                            <tr>
                                <td class="text-left"><a href="/admin/conditions/edit/<?=$cond_queue['condition_id'];?>"><?=$cond_queue['condition_id'];?></a></td>
                                <td class="text-left"><?=$cond_queue['create_date'] ? date('d-m-Y H:i:s', $cond_queue['create_date']) : '';?></td>
                                <td class="td-last"><?=$cond_queue['status'];?></td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>

                <p>
                    <a href="/admin/conditions/del-cond-queues" onclick="return confirm('Вы уверены?')">Удалить список очередей</a>
                </p>
            <?php else:?>
                <p>Очередей пока нет</p>
            <?php endif;?>
        </div>
    </div>

    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>