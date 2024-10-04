<?php defined('BILLINGMASTER') or die;?>

<form class="filter-form" action="/admin/training/statistics/<?=$training_id;?>/" method="POST">
    <p><strong>Фильтровать</strong></p>

    <div class="filter-row filter-flex-end mb-20">
        <div class="filter-1-4">
            <label>Анализируем период:</label>
            <div class="datetimepicker-wrap">
                <input type="text" class="datetimepicker" name="start_date" value="<?=$filter['start_date'] ? date("d.m.Y", $filter['start_date']) : '';?>" placeholder="От" autocomplete="off" data-format="d.m.Y H:i">
            </div>
        </div>

        <div class="filter-1-4">
            <div class="datetimepicker-wrap">
                <input type="text" class="datetimepicker" name="finish_date" value="<?=$filter['finish_date'] ? date("d.m.Y", $filter['finish_date']) : '';?>" placeholder="До" autocomplete="off" data-format="d.m.Y H:i">
            </div>
        </div>

        <div class="filter-1-2 px-label-wrap">
            <label>Считать бросившим, если не активен:<span class="px-label">дн.</span></label>
            <input type="text" name="stop_out_day" placeholder="" value="<?=$filter['stop_out_day'] ? $filter['stop_out_day'] : '30';?>">
        </div>

        <div class="filter-bottom">
            <div>
                <div class="order-filter-result">
                    <?php if($curators_ids):?>
                        <input class="csv__link"  type="submit" name="load_csv" value="Выгрузить в csv">
                    <?php endif;?>
                </div>
            </div>

            <div class="button-group">
                <?php if($filter['is_filter']):?>
                    <a class="red-link" href="/admin/training/statistics/<?=$training_id;?>/?reset">Сбросить</a>
                <?php endif;?>

                <input class="button-blue-rounding" type="submit" name="filter" value="Найти">
            </div>
        </div>
    </div>
    <hr>
    <input type="hidden" name="stat_type" value="curators">
</form>

<div class="admin_result">
    <?php if($curators_ids):?>
        <div class="overflow-container">
            <table class="table fz-12">
                <thead>
                    <tr>
                        <th class="text-left">Куратор</th>
                        <th class="text-right">Учеников</th>
                        <th class="text-right">Бросили</th>
                        <th class="text-right">В процессе</th>
                        <th class="text-right">Прошли</th>
                        <th class="text-right">Проверено заданий</th>
                    </tr>
                </thead>

                <tbody>
                    <?foreach($curators_ids as $curator_id):
                        $curator = User::getUserById($curator_id);
                        $curator_name = $curator['surname'] ? "{$curator['user_name']}<br>{$curator['surname']}" : $curator['user_name'];
                        $stat = TrainingStatistics::getCuratorStatistics($training_id, $curator_id, $filter)?>
                        <tr>
                            <td class="text-left">
                                <a href="/admin/users/edit/<?=$curator_id;?>" target="_blank"><?=$curator_name;?></a>
                            </td>
                            <td class="text-right">
                                <a href="#modal_users_for_curator" data-uk-modal="{center:true}" data-url="/admin/training/statistics/curator/<?="{$training_id}/{$curator_id}/students";?>"><?=$stat['students'];?></a>
                            </td>
                            <td class="text-right">
                                <a href="#modal_users_for_curator" data-uk-modal="{center:true}" style="color:#E04265;font-weight:700" data-url="/admin/training/statistics/curator/<?="{$training_id}/{$curator_id}/throw";?>"><?=$stat['throw'] .' ('. ($stat['students'] ? round($stat['throw']/$stat['students']*100) : 0) . ' %)';?></a>
                            </td>
                            <td class="text-right">
                                <a href="#modal_users_for_curator" data-uk-modal="{center:true}" style="color:#FFCA10;font-weight:700" data-url="/admin/training/statistics/curator/<?="{$training_id}/{$curator_id}/process";?>"><?=$stat['in_process'] .' ('. ($stat['students'] ? round($stat['in_process']/$stat['students']*100) : 0) . ' %)';?></a>
                            </td>
                            <td class="text-right">
                                <a href="#modal_users_for_curator" data-uk-modal="{center:true}" style="color:#5DCE59;font-weight:700" data-url="/admin/training/statistics/curator/<?="{$training_id}/{$curator_id}/completed";?>"><?=$stat['completed'] . ' ('. ($stat['students'] ? round($stat['completed']/$stat['students']*100) : 0) . ' %)';?></a>
                            </td>
                            <td class="text-right">
                                <?=$stat['task_checked'];?>
                            </td>
                        </tr>
                    <?endforeach;?>
                </tbody>
            </table>
        </div>
    <?php else:?>
        <p><?=$filter['is_filter'] ? 'Ничего не найдено' : 'Пользователей ещё нет';?></p>
    <?php endif;?>
</div>

<script>
  $('.datetimepicker').datetimepicker({
    format:'d.m.Y H:i',
    lang:'ru'
  });
</script>