<?php defined('BILLINGMASTER') or die;
$delivery_list = Responder::getDeliveryList(2,1,100);
$group_list = User::getUserGroups();

if (isset($_GET['filter_model'])) {
    $filter_model = $_GET['filter_model'];
} else {
    $filter_model = isset($condition) ? $condition['filter_model'] : null;
}?>

<div class="row-line">
    <div class="col-1-1 mb-0">
        <h4>Действия</h4>
    </div>

    <div class="col-1-1">
        <a class="add_action" href="#add_action" data-uk-modal="{center:true}" data-filter_model="<?=$filter_model;?>"><nobr>Добавить действие</nobr></a>
        <?require_once (__DIR__ .'/action_list.php');?>
    </div>
</div>

<?require_once (__DIR__ .'/add_action.php');?>