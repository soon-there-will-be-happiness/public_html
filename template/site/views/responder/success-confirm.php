<?php defined('BILLINGMASTER') or die;?>

<div id="content">
    <div class="layout" id="responder">
        <div class="content-wrap">
            <div class="maincol<?php if($sidebar) echo '_min content-with-sidebar';?>">
                <div class="maincol-inner">
                    <?if(empty($delivery['after_confirm_text'])):?>
                        <?=System::Lang('YOUR_EMAIL_SUCCESSFULLY_CONFIRM');?>
                    <?else:
                        echo $delivery['after_confirm_text'];
                    endif;?>
                </div>
            </div>
            <?require_once ("{$this->layouts_path}/sidebar.php");?>
        </div>
    </div>
</div>