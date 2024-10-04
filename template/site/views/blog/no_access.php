<?php defined('BILLINGMASTER') or die;?>

<h1>К сожалению у вас пока нет доступа к этой странице</h1>
<?if(!$user_id):?>
    <p>Возможно вы просто не авторизованы. <a href="#modal-login" data-uk-modal="{center:true}">Войти на сайт</a></p>
<?else:?>
    <p></p>
<?endif;?>