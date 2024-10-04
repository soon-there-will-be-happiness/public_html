<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('CONDITIONS');?></h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><?=System::Lang('CONDITIONS');?></li>
    </ul>

    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li class="nav_gorizontal__parent-wrap">
                <a class="button-red-rounding" href="/admin/conditions/add/?filter_model=1"><?=System::Lang('ADD_CONDITION');?></a>
            </li>
            <li><a class="button-yellow-rounding" href="/admin/conditions/log/">Журнал событий</a></li>
            <li><a title="Общие настройки" class="settings-link" href="/admin/conditions/settings/"><i class="icon-settings-bold"></i></a></li>
        </ul>
    </div>
    
    <?if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?endif;?>

    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-left">Название</th>
                        <th class="text-left">Тип фильтра</th>
                        <th class="text-left">Сегмент</th>
                        <th class="text-left">Время выполнения</th>
                        <th class="td-last"></th>
                    </tr>
                </thead>

                <tbody>
                    <?if($conditions_list):
                        foreach($conditions_list as $condition):
                            $model = SegmentFilter::getFilterModel($condition['filter_model']);
                            $segment = $model::getSegment($condition['segment_id']);?>

                            <tr<?if($condition['status'] == 0) echo ' class="off"'; ?>>
                                <td class="text-left col-width-50">
                                    <i class="status-icon<?=$condition['status'] ? ' active' : '';?>"></i>
                                    <a href="/admin/conditions/edit/<?="{$condition['id']}?filter=фильтр&segment={$segment['segment_id']}&filter_model={$condition['filter_model']}";?>"><?=$condition['name'];?></a>
                                </td>

                                <td class="text-left rdr_1"><?=$condition['filter_model'] == SegmentFilter::FILTER_TYPE_ORDERS ? 'Заказы' : 'Пользователи';?></td>

                                <td class="text-left col-width-50">
                                    <a href="<?=SegmentFilter::getSegmentUrl($model, $segment['segment_id']);?>" target="_blank"><?=$segment['segment_name'];?></a>
                                </td>

                                <td class="text-left">
                                    <?=Conditions::getIntervalInfo($condition);?>
                                </td>

                                <td class="td-last">
                                    <a class="link-delete" onclick="return confirm('<?=System::Lang('YOU_SHURE');?>?')" href="/admin/conditions/del/<?=$condition['id'];?>?token=<?=$_SESSION['admin_token'];?>" title="<?=System::Lang('DELETE');?>"><i class="fas fa-times" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else:?>
                        <p>Нет условий</p>
                    <?endif;?>
                </tbody>
            </table>
        </div>
    </div>
    <?if(isset($is_pagination) && $is_pagination == true) echo $pagination->get();?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>