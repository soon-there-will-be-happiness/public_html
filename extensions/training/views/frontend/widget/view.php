<?php defined('BILLINGMASTER') or die;?>

<div class="layout" id="widget-training">
    <div class="content-courses">
        <div class="maincol<?php if($sidebar) echo '_min content-with-sidebar';?>">
            <?require_once (ROOT . "/extensions/training/views/frontend/training/templates/list/index.php");?>
        </div>
        <?require_once ("{$this->layouts_path}/sidebar.php");?>
    </div>
</div>