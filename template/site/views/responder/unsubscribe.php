<?php defined('BILLINGMASTER') or die;?>

<div id="content">
    <div class="layout" id="responder">
        <div class="content-wrap">
            <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
                <div class="maincol-inner">
                    <?=System::Lang('UNSUBSCRIBE_FROM_NEWSLETTER');?>
                    <p><?=System::Lang('YOUR_EMAIL_ADRESSE');?> <strong><?=$email;?></strong></p>

                    <form action="" method="POST">
                        <p><label class="custom-radio"><input name="type" type="radio" value="single"> <span><?=System::Lang('UNSUBSCRIBE_FROM_THIS_NEWSLETTER');?></span></label></p>
                        <p><label class="custom-radio"><input name="type" type="radio" value="all"> <span><?=System::Lang('UNSUBSCRIBE_FROM_ALL_NEWSLETTER');?></span></label></p>
                        <?if(!$check):?>
                            <p><label class="custom-radio"><input name="type" type="radio" value="delete"> <span><?=System::Lang('DELLIT_ME_FROM_DATABASE');?></span></label></p>
                        <?endif;?>
                        <p><label class="custom-radio"><input name="type" type="radio" value="none"> <span><?=System::Lang('STAING');?></span></label></p>
                        <p><label><?=System::Lang('WHY_YOUR_ARE_UNSUBSCRIBED');?></label>
                            <textarea name="why" cols="55" rows="3"></textarea>
                        </p>
                        <p><input type="submit" value="Готово!" class="button btn-blue" name="gone"></p>
                    </form>
                </div>
            </div>
            <?require_once ("{$this->layouts_path}/sidebar.php");?>
        </div>
    </div>
</div>