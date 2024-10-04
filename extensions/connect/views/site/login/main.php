<? defined('BILLINGMASTER') or die;

if((!isset($explr) || empty($explr)) && ($explr = System::generateStr(3)));

$count = 0;
$time = time();
?>

<style type="text/css">
    <? require_once __DIR__ . '/../../../web/css/login_style.css'; ?>
</style>

<div class="connect_login" data-id="connect_auth-buttons" id="connect_auth-buttons-<?=$explr?>">
	<div class="buttons">
		<div class="top_block">
			<p>
				<span id="msg_<?=$explr?>"></span>
			</p>
		</div>
		<div class="one_line">
		<? foreach ($services as $id => $service): 
			if($service['status'] == 1 
				&& isset($service['types']['auth'], $service['params']['auth_link']) 
				&& @ $service['params']['auth'] == 1
			): 
				if($count % 2 == 0 && $count != 0){ ?>
				</div>
				<div class="one_line">
				<? } $count++;?>
				<button onclick="auth_<?=$explr?>('<?=$service['name']?>', <?=$id?>);">
					<div>
						<span class="icon" style="background-image: url('/extensions/connect/web/images/<?=$service['name']?>.svg');"></span>
						<?=$service['title']?>
					</div>
			       <span class="load-line" id='load-<?=$service['name']?>-<?=$explr?>'></span>
				</button>
			<? endif; ?>
		<? endforeach; ?>
		</div>
	</div>

	<script defer type="text/javascript">
		var connectClass_<?=$explr?>;

		var auth_<?=$explr?> = function(service, id) {
			if(connectClass_<?=$explr?>)
				return connectClass_<?=$explr?>.auth(service,id);

			connectClass_<?=$explr?> = new Connect("<?=$explr?>", "<?=$_SESSION['connect_token']?>", <?=time();?>);

			return auth_<?=$explr?>(service, id);
		};
	</script>

	<? if($count < 1): ?>
	<script type="text/javascript">
		a_btns = document.getElementsByClassName('modal-form-connect-wrap')[0];

		a_btns.parentNode.removeChild(a_btns);
	</script>
	<? endif; ?>
</div>
