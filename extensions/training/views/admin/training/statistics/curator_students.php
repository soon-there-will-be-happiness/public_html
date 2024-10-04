<?php defined('BILLINGMASTER') or die;?>

<form method="POST" action="">
    <div class="modal-admin_top">
        <h3 class="modal-traning-title">Список учеников</h3>
        <ul class="modal-nav_button">
            <li class="modal-nav_button__last">
                <a class="button uk-modal-close uk-close modal-nav_button__close" href="#close"><i class="icon-close"></i></a>
            </li>
        </ul>
    </div>

    <div class="admin_form">
        <?php if($users):?>
            <div class="overflow-container">
                <table class="table fz-12">
                    <thead>
                    <tr>
                        <th class="text-left">ID</th>
                        <th class="text-left">Пользователь</th>
                        <th class="text-right">Email</th>
                        <th class="text-right">Куратор</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?foreach($users as $user_id):
                        $user = User::getUserById($user_id);
                        $user_name = $user['surname'] ? "{$user['user_name']} {$user['surname']}" : $user['user_name'];?>
                        <tr>
                            <td class="text-left"><?=$user_id;?></td>
                            <td class="text-left">
                                <a href="/admin/users/edit/<?=$user_id;?>" target="_blank"><?=$user_name;?></a>
                            </td>
                            <td class="text-right"><?=$user['email'];?></td>
                            <td class="text-right">
                                <a href="/admin/users/edit/<?=$curator_id;?>" target="_blank"><?=$curator_name;?></a>
                            </td>
                        </tr>
                    <?endforeach;?>
                    </tbody>
                </table>

                <div class="mt-15">
                    <input class="csv__link"  type="submit" name="load_csv" value="Выгрузить в csv">
                </div>
            </div>
        <?php else:?>
            <p><?=$filter['is_filter'] ? 'Ничего не найдено' : 'Пользователей ещё нет';?></p>
        <?php endif;?>
    </div>
</form>