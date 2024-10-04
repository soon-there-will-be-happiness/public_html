<?php defined('BILLINGMASTER') or die;?>

<div class="maincol-inner">
    <h1><?=$page['name'];?></h1>
    <?=System::renderContent($page['content']);?>
    <?=$page['custom_code'];?>
</div>