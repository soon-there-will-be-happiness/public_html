<? defined('BILLINGMASTER') or die; ?>
<style type="text/css">
    <? require_once __DIR__ . '/../../../web/css/admin_style.css'; ?>
    .connect_set .width-100{
    	display: flex;
    	flex-direction: column;
    }
    .connect_set h4:not(.h4-border){
    	margin: 0;
    	border-bottom: none;
    }
    .connect_set span.icon-close{
    	display: none;
    }
</style>

<div class="admin_form connect_set">
    <h4 class="h4-border">
        <ul class="nav_button close_bar">
            <li class="flex">
                <span class="logotp"></span>
                <h3 class="traning-title mb-0">Connect</h3>
            </li>
            <li class="nav_button__last">
                <a class="uk-modal-close uk-close modal-nav_button__close red-link" href="#close">Закрыть</a>
            </li>
        </ul>
    </h4>
    <form method="POST" action="post" id="connect_user_setting_form">
        <input type="hidden" name="token" value="<?=@ $_SESSION['admin_token'];?>">
        <input type="hidden" name="method" value="setting">

        <div class="row-line">
        <? if(!isset($services) || empty($services)): ?>

        	<h1>#404</h1>

	    <? else: ?>

        	<? row_line($services, $connect_user, false);?>
            
	    <? endif; ?>
        </div>
        <ul class="nav_button save_bar">
        <? if(System::issetPermission('change_users') && $use_js = true): ?>
        	<button class="button-green">Сохранить
        		<span class="load-line"></span>
        	</button>
        <? endif; ?>
        </ul>
    </form>
</div>

<? if(@ $use_js): ?>
<script type="text/javascript">
form = $('#connect_user_setting_form');
button = form.find('button');
loader = form.find('.load-line')[0];

form.submit( function (e) {
	e.preventDefault();

	button.addClass('hover');
	loader.style.display = 'block';

    $.ajax({
		method: 'POST',
		type: 'POST',
        url: '/admin/connect/ajax/user/<?=$connect_user["user_id"]?>?submit',
        data: $(this).serialize(),
        success: function(data) {
			loader.style.display = 'none';
			button.removeClass('hover');
			form.find('.row-line').html(data);
        },
        error: function(){
			loader.style.display = 'none';
			console.log('[Connect] > loading error');
        }
    });
    return false;
});
</script>

<? endif; ?>