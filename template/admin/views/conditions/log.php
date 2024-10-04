<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Журнал событий</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/conditions/"><?=System::Lang('CONDITIONS');?></a></li>
        <li>Журнал событий</li>
    </ul>

    <div class="filter admin_form">
        <form action="/admin/conditions/log" method="GET">
            <div class="filter-row filter-flex-end">
                <div class="filter-1-3">
                    <input type="text" name="email" value="<?= isset($filter['email']) ? $filter['email'] : '';?>" placeholder="E-mail пользователя">
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="condition">
                            <option value="">Условие</option>
                            <?$conditions = Conditions::getConditionsList();
                            if($conditions):
                                foreach ($conditions as $condition):?>
                                    <option value="<?=$condition['id'];?>"<?if(isset($filter['condition']) && $filter['condition'] == $condition['id']) echo ' selected="selected"';?>><?=$condition['name'];?></option>
                                <?endforeach;
                            endif;?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="event_type">
                            <option value="">Тип события</option>
                            <?$events = Conditions::getEvents();
                            foreach ($events as $value => $title):?>
                                <option value="<?=$value;?>"<?if(isset($filter['event_type']) && $filter['event_type'] == $value) echo ' selected="selected"';?>><?=$title;?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="status">
                            <option value="">Статус</option>
                            <?php $statuses = Conditions::getEventStatuses();
                            if ($statuses) {
                                foreach ($statuses as $value => $title):?>
                                    <option value="<?= $value; ?>"<?php if (isset($filter['status']) && $filter['status'] !== null && $filter['status'] == $value) echo ' selected="selected"'; ?>><?= $title; ?></option>
                                <?php endforeach;
                            } ?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="datetimepicker-wrap">
                        <input type="text" class="datetimepicker" name="start"<?if(isset($filter['start'])) echo ' value="'.date('d.m.Y H:i', $filter['start']).'"';?> placeholder="От" autocomplete="off" title="Выберите период начала событий">
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="datetimepicker-wrap">
                        <input type="text" class="datetimepicker" name="finish"<?if(isset($filter['finish'])) echo ' value="'.date('d.m.Y H:i', $filter['finish']).'"';?> placeholder="До" autocomplete="off" title="Выберите период окончания событий">
                    </div>
                </div>

                <div class="filter-bottom">
                    <div>
                        <?if(isset($filter['is_filter'])):?>
                            <span class="mr-20">Всего в выборке: <strong><?=$total;?></strong></span>
                        <?php endif;?>
                    </div>

                    <div class="button-group">
                        <?if(isset($filter['is_filter'])):?>
                            <a class="red-link" href="/admin/conditions/log/">Сбросить</a>
                        <?endif;?>
                        <input class="button-blue-rounding" type="submit" name="filter" value="Найти">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <?if($log_list):?>
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Email</th>
                            <th class="text-left">Тип события</th>
                            <th class="text-left">ID<br>авт-ии</th>
                            <th class="text-left">ID<br>действия</th>
                            <th class="td-left">Дата</th>
                            <th class="td-last">Статус</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($log_list as $log):
                            $condition = Conditions::getConditionAction($log['action_id']);
                            $condition_id = $condition ? $condition['condition_id'] : $log['condition_id']?>
                            <tr>
                                <td class="text-left"><a href="/admin/conditions/log/<?=$log['id'];?>"><?=$log['id'];?></a></td>
                                <td class="text-left"><?=$log['user_email'];?></td>
                                <td class="text-left"><?=Conditions::getEvents($log['action']);?></td>
                                <td class="text-left"><a href="/admin/conditions/edit/<?=$log['condition_id'];?>"><?=$log['condition_id'];?></a></td>
                                <td class="text-left"><a href="/admin/conditions/edit/<?=$condition_id;?>?action_id=<?=$log['action_id'];?>"><?=$log['action_id'];?></a></td>
                                <td class="text-left"><?=date('d.m.Y H:i', $log['date']);?></td>
                                <td class="text-left"><nobr><?=Conditions::getEventStatuses($log['status'], $log['act_status']);?></nobr></td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            <?php else:?>
                <p>Событий пока небыло</p>
            <?php endif;?>
        </div>
    </div>

    <?=$pagination->get();
    require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
  jQuery('.datetimepicker').datetimepicker({
    format:'d.m.Y H:i',
    lang:'ru'
  });
</script>
</body>