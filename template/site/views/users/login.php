<?defined('BILLINGMASTER') or die;

$explr = System::generateStr(3);
?>

<div class="content-userbox" id="<?=$explr?>">
    <h1 class="text-center"><?=System::Lang('AUTHORIZATION');?></h1>
    <?if(isset($errors) && is_array($errors)):?>
        <ul style="color:#9F6000;">
            <?foreach($errors as $error): ?>
                <li><?=$error;?></li>
            <?endforeach; ?>
        </ul>
    <?endif;?>

    <form action="/login" method="POST" class="form-login">
        <?// РАСШИРЕНИЕ AUTOPILOT
        // if (System::CheckExtensension('autopilot', 1)) {
        //     require_once (ROOT.'/extensions/autopilot/views/simple/vk-auth.php');
        // }

        // if (System::CheckExtensension('telegram', 1)) {
        //     require_once (ROOT . '/extensions/telegram/views/tg-auth.php');
        // }
        ?>

        <div class="modal-form-line">
            <script>
                document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBpZD0iYXV0b0lucHV0X2ljdTg5YzQiIG5hbWU9ImVtYWlsIiBwbGFjZWhvbGRlcj0iRS1tYWlsIiByZXF1aXJlZD0icmVxdXJlZCI+"));
            </script>
            
            <? /* base64: <input type="email" id="autoInput_icu89c4" name="email" placeholder="E-mail" required="requred"> */ 
            if(isset($_COOKIE['emnam'])): ?>
            <script>
                document.getElementById('autoInput_icu89c4').value = "<?=explode('=', $_COOKIE['emnam'])[0];?>";
            </script>
            <? endif; ?>
        </div>

        <div class="modal-form-line">
            <input placeholder="Password" type="password" name="pass" required="requred">
        </div>

        <div class="modal-form-line">
            <label class="check_label remember-me">
                <input type="checkbox" name="remember_me"<?if(isset($_COOKIE["sm_remember_me"])) echo ' checked';?>>
                <span><?=System::Lang('REMEMBER_ME');?></span>
            </label>
        </div>

        <div class="modal-form-submit">
            <input type="submit" value="<?=System::Lang('LOGIN');?>" class="btn-yellow-fz-16 d-block button" name="enter">
        </div>
    </form>

    <div class="modal-form-forgot-wrap">
        <?if ($this->settings['enable_registration']):?>
            <div class="modal-form-reg">
                <a href="/lk/registration"><?=System::Lang('REGISTRATION');?></a>
            </div>
        <?endif;?>

        <div class="modal-form-forgot">
            <a href="/forgot"><?=System::Lang('FORGOT_PASSWORD');?></a>
        </div>
    </div>

    <div class="modal-form-connect-wrap">
        <? Connect::showAuthButtons($explr); ?>
    </div>
</div>