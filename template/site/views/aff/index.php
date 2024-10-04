<?php defined('BILLINGMASTER') or die;?>

<div class="maincol-inner">
    <div class="aff-desc">
        <?=$params['params']['aff_desc'];?>
    </div>


    <?if (!isset($params['params']['hide_btns']) || (isset($params['params']['hide_btns']) && $params['params']['hide_btns'] == 0) ) {
    if($show_aff):?>
        <p><a class="button btn-yellow" href="/aff/reg"><?=System::Lang('BECOME_PARTER');?></a></p>
    <?php endif;

    if($show_cabinet):?>
        <p><a class="btn-blue-thin" href="/lk"><?=System::Lang('LOG_IN_LK');?></a>
            <br><span style="font-size: 12px"><?=System::Lang('ALREADY_REG');?></span>
        </p>
    <?php endif;
    }?>
</div>