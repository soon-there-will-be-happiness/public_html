<?php defined('BILLINGMASTER') or die;?>
<!-- 5 Привлчечённые клиенты -->
<div>
    <div class="table-responsive">
        <?php if($clients):?>
        <form method="post" action="">
            <button type="submit" name="save_child_name" class="button save button-white font-bold">
                Сохранить
            </button>
            <p><?=System::Lang('TOTAL');?> <?=count($clients);?></p>
<style>
.save {
    text-transform: uppercase;
    margin-right: 15px;
    font-size: 14px;

    background: #5DCE59;
    color: #fff;
    border-color: #5DCE59;
}
.save:hover {
    color: #5DCE59;
    background: #fff;
    border-color: #fff;
}
.button-white {
    float: right;
    padding: 13px 25px 12px;
    cursor: pointer;
    display: inline-block;
    transition: all 0.2s ease 0s;
    background: #fff;
    border: 4px solid #5DCE59;
    color: #5DCE59;
    border-radius: 10px;
    text-align: center;
    text-decoration: none;
}
.button-white:hover {
    background: #5DCE59;
    color: #fff;
    border-color: #5DCE59;
}
.font-bold:after {
    width: 15px;
    height: 16px;
    background-image: url('../images/icons/font-bold.svg');
}
.font-bold {
    font-weight: bold !important;
}
</style>

            <table class="usertable">
                <tr>
                    <th><?=System::Lang('NAME');?></th>
                    <th><?=System::Lang('CHILD_NAME');?></th>
                    <th><?=System::Lang('EMAIL');?></th>
                    <th><?=System::Lang('REGISTRATION_DATE');?></th>
                </tr>

                <?php foreach($clients as $client):?>
                    <tr>
                        <td><?=$client['user_name'];?></td>
                        <td>
                        <input  type="text"
                                name="child_name[<?= (int)$client['user_id']; ?>]"
                                value="<?= htmlspecialchars($client['child_name'] ?? '', ENT_QUOTES); ?>"
                                class="form-control"
                                style="width:150px;">
                        </td>
                        <td><?=$params['params']['hidden_email'] == 1 ? System::hideEmail($client['email']) : $client['email']; ?><br />
                        <?=$params['params']['hidden_phone'] == 1 ? null : ($client['phone']) ;?>
                        </td>
                        <td><?=date("d.m.Y H:i:s", $client['reg_date']);?></td>
                    </tr>
                <?php endforeach;?>
            </table>
        </form>
        <?php endif;?>
    </div>
</div>
