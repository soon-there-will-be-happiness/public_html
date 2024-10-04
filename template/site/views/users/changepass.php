<?php defined('BILLINGMASTER') or die;?>

<div class="login-userbox">
    <h1 class="cource-head"><?=System::Lang('CHANGE_PASSWORD');?></h1>
    <?if(isset($_GET['success'])):?>
        <div class="success_message">Успешно</div>'
    <?endif;?>

    <form action="" method="POST">
        <div class="form-line"><label><?=System::Lang('NEW_PASSWORD');?></label>
            <div class="form-line-input"><input type="text" name="pass"></div>
        </div>

        <div class="form-line-submit">
            <input class="btn-yellow-fz-16 font-bold button" type="submit" name="changepass" value="<?=System::Lang('SAVE');?>">
        </div>
    </form>
</div>