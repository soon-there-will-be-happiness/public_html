<?php defined('BILLINGMASTER') or die;?>

<div id="content">
    <div class="layout" id="responder">
        <div class="content-wrap">
            <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
                <div class="maincol-inner">
                    <?=System::Lang('THANKS_FOR_STAING');?>
                </div>
            </div>
            <?require_once ("{$this->layouts_path}/sidebar.php");?>
        </div>
    </div>
</div>