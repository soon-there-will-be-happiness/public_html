<?php defined('BILLINGMASTER') or die;?>

<div id="content">
    <div class="layout" id="responder">
        <div class="content-wrap">
            <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
                <div class="maincol-inner">
                    <h1 class="mb-0"><?=System::Lang('YOUR_SUCCESSFULL_SUBSCRIBED');?></h1>
                </div>
            </div>
            <?require_once ("{$this->layouts_path}/sidebar.php");?>
        </div>
    </div>
</div>