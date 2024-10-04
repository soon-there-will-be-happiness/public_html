<tr<?php if($user['status'] == 0) echo ' class="off" style="color:#d0cdce"'; if($user['status'] == 6) echo ' class="refund"'; ?>>
    <td><?=$user['user_id'];?></td>
    <td class="text-left">
        <div class="table-user-name__wrap">
            <div class="table-user-name<?if(!$setting['multiple_authorizations'] && UserSession::hasSuspiciousActivity($user['user_id'])) echo ' suspicious-activity';?>">
                <a href="/admin/users/edit/<?=$user['user_id'];?>"><?=$user['user_name'];?> <?=$user['surname'];?></a>
                <?if(in_array($user['role'], ['manager', 'admin'])):?>
                    <span><i class="icon-role--<?=$user['role'];?>"></i></span>
                <?endif;?>
            </div>

            <div class="table-user-mail"><?=$user['email'];?></div>
        </div>
    </td>

    <td class="text-left"><?=date("d.m.Y H:i", $user['reg_date']);?></td>
    <td class="td-last">
        <a href="/admin/users/edit/<?=$user['user_id'];?>" target="_blank">Смотреть</a>
    </td>

    <td class="td-last">
        <?if($user['is_client'] == 1):?>
            <span class="stat-yes"><i class="icon-stat-yes"></i></span>
        <?php else:?>
            <span class="stat-no"></span>
        <?php endif;?>
    </td>
</tr>