<? defined('BILLINGMASTER') or die;

function row_line($services, $connect_user, bool $show_btn = true, string $addform_html = ''){
	foreach ($services as $id => $service) { ?>
	<div class="col-1-2 <?=$service['name']?>-block">
        <div class="width-100">
            <h4>
	        	<? if($service['name'] == 'telegram')
		        	echo "<span class=\"icon-telegram\"></span>";
		        if($service['name'] == 'vkontakte') 
		        	echo "<span class=\"icon-vk-i\"></span>";
		        ?>
            	<?=$service['title']?>:
            </h4>

            <? if(isset($connect_user[Connect::getServiceKey($service['name'])])): ?>
	            <? if(isset($service['types']['auth'], $service['params']['auth'])): ?>
	            <label class="check_label tab">
	                <input type="checkbox" name="params[<?=$service['name']?>][auth]" value="1"
	                <? 
	                if($service['params']['auth'] != 1)
		                echo "disabled";
	                elseif(@ $connect_user['params'][$service['name']]['auth']) 
	                	echo "checked=''";
	                ?>>
	                <span>Авторизация</span>
	            </label>
	        	<? endif; ?>

	            <? if(isset($service['types']['msg'], $service['params']['msg'])): ?>
	            <label class="check_label tab">
	                <input type="checkbox" name="params[<?=$service['name']?>][msg]" value="1"
	                <? 
	                if($service['params']['msg'] != 1)
		                echo "disabled";
	                elseif(@ $connect_user['params'][$service['name']]['msg']) 
	                	echo "checked=''";
	                ?>>
	                <span>Уведомления</span>
	            </label>
	            <?=$addform_html?>
	        	<? endif; ?>
        	<? else: ?>
        		<span class="icon-info" style="color: #FFCA10;"></span> Не привязан
        	<? endif; 
        	if($show_btn == true): ?>
	        	<a href="javascript:void(0);" 
	        		id = "unlink_button_<?=$service['name']?>_"
	                data-connect="<?=$service['name']?>" 
	                data-connect-method="unlink"
	                data-connect-unlink_text="<?=System::Lang('UNLINK')?>" 
	                data-connect-attach_text="<?=System::Lang('BIND');?>" 
	                class="btn getlink btn-red button_right"
	            >
	                <span class="loading_text-ani"></span>
	            </a>
	            <script type="text/javascript">
	            	if(!connect_attch)
						var connect_attch = new ConnectAttch();
	            	connect_attch.buttonCheck('<?=$service["name"]?>', $('#unlink_button_<?=$service["name"]?>_'));
	            </script>
	        <? endif; ?>
        </div>
    </div>
	<? }
}

if(isset($show)):
?>

<style type="text/css">
    <? require_once __DIR__ . '/../../../web/css/lk_sett_style.css'; ?>
</style>


<div class="connect_set">
	<h4 class="h4-border">
        <ul class="nav_button">
            <li>
                <h3 class="mb-0"><span class="logotp"></span>Connect</h3>
            </li>
            <li class="nav_button__last">
            	<a class="uk-modal-close uk-close modal-nav_button__close red-link" href="#close"><span class="icon-close"></span></a>
            </li>
        </ul>
    </h4>
    <form method="POST" action="post" id="connect_setting_form">
        <input type="hidden" name="token" value="<?=@ $_SESSION['admin_token'];?>">
        <input type="hidden" name="method" value="setting">

        <? if(empty($services)): ?>
        <div class="row-line">
        	#404
        </div>
	    <? else: ?>
        <div class="row-line">

        	<? row_line($services, $connect_user);?>
            
        </div>
	    <? endif; ?>

        <ul class="nav_button save_bar">
        	<button class="button-green">Сохранить
        		<span class="load-line"></span>
        	</button>
        </ul>
    </form>
</div>

<script type="text/javascript">

$("#connect_setting_form a[data-connect]").click( function () {
	button = $(this);
	href = button.attr('href');
		
	if(href.substring(0, 4) == 'http')
		return true;

	service = button.data('connect');
	if(service)
		connect_attch.buttonClick(service, button);

	return false;
});

elmnt_ctr = new ElementController();

form = $('#connect_setting_form');
button = form.find('button');
inputs =  $('#connect_setting_form :input[type="checkbox"]');
loader = form.find('.load-line')[0];
inputs.push(button[0]);

form.submit( function (e) {
	e.preventDefault();

	elmnt_ctr.setDisabled.apply(inputs, inputs);
	button.addClass('hover');
	loader.style.display = 'block';

    $.ajax({
		method: 'POST',
		type: 'POST',
        url: '/connect/ajax/lk/submit?' + $(this).serialize(),
        data: $(this).serialize(),
        success: function(data) {
			elmnt_ctr.unsetDisabled.apply(inputs, inputs);
			loader.style.display = 'none';
			button.removeClass('hover');
			form.find('.row-line').html(data);
        },
        error: function(data){
			console.log('[Connect] > loading error');
        }
    });
    return false;
});
</script>

<? endif; ?>