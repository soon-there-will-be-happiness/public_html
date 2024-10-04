<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1><?php echo System::Lang('DELIVERY_LIST');?></h1>
    <div class="logout">
        <a href="/" target="_blank"><?php echo System::Lang('GO_SITE');?></a><a href="/admin/logout" class="red"><?php echo System::Lang('QUIT');?></a>
    </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <?php if(isset($name_responder)) : ?>
        <li><a href="/admin/responder/mass/"> Список email массовой рассылки </a>> <?=$name_responder;?></li>
        <?php else :?>
        <li>Список email массовой рассылки </li>
        <?php endif;?>
    </ul>

<div class="admin_form admin_form--margin-top">
<div class="overflow-container">
    <table class="table">
        <thead>
        <tr>
           <th class="text-left">e-mail</th>
           <th class="td-last"></th>
        </tr>
        </thead>
        <tbody>
    <?php if(isset($list_email_to_responder) && is_array($list_email_to_responder)): ?>

        <?php foreach ($list_email_to_responder as $el):?>
        <tr>
             <td class="text-left"> <?=$el['email'];?> </td>
                <td><a class="link-delete left" onclick="return confirm('Вы уверены?')" href="/admin/responder/delemail/<?php echo $el['task_id'];?>?id_responder=
                    <?=$id_responder?>&token=<?php echo $_SESSION['admin_token'];?>"
               title="Удалить"><i class="fas fa-times" aria-hidden="true"></i></a></td>
         </tr>
        <?php endforeach?>


    <?php endif; ?>
        </tbody>
    </table>
<!--    --><?//=setting['show_items']?>
<?php //print_r($setting['show_items']) ?>

</div>
</div>
    <?php if(isset($is_pagination) && $is_pagination == true) echo $pagination->get(); ?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>