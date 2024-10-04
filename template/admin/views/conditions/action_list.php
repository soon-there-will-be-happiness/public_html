<?php defined('BILLINGMASTER') or die;?>

<div class="condition-actions">
    <?if(isset($cond_actions) && $cond_actions):
        foreach($cond_actions as $key => $action):?>
            <div class="condition-action" data-action_key="<?=$key;?>" data-action_id="<?=isset($action['action_id']) ? $action['action_id'] : 0;?>">
                <a class="condition-action-edit" href="#edit_action" data-uk-modal="{center:true}" data-filter_model="<?=$filter_model;?>">
                    <span class="condition-action-title"><?=Conditions::getActions($action['action']);?></span>
                </a>
                <a class="icon-remove ajax" href="javascript:void(0)" data-url="/admin/conditions/del-action"></a>
            </div>
        <?endforeach;
    endif;?>
</div>