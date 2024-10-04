<?php defined('BILLINGMASTER') or die;

$metriks = null;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('FEEDBACK');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'feedback', 'submit');" : null;
if(!empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1) {
    $metriks = ' onsubmit="'.$ya_goal.$ga_goal.' return true;"';
}
$userid = User::isAuth();
if ($userid) {
    $userEmail = User::getUserById($userid);
    $userEmail = $userEmail['email'];
}

if(!isset($_GET['success'])):
    $_SESSION['feedback'] = 1;
    if(!empty($this->params['before'])):?>
        <p><?=$this->params['before'];?></p>
    <?php endif;?>

    <div class="login-userbox">
        <h1 class="cource-head"><?=$this->params['h1'];?></h1>

        <form action="" method="POST"<?=$metriks;?> onsubmit="onClick(e)">
            <?if($this->params['name'] > 0):?>
                <div class="modal-form-line">
                    <input type="text" name="name" placeholder="<?=System::Lang('NAME');?>" <?if($this->params['name'] == 2) echo ' required="required"';?>>
                </div>
            <?php endif;

            if($this->params['email'] == 1):?>
                <div class="modal-form-line">
                    <input type="email" name="email" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$" placeholder="E-mail" value="<?= $userEmail ?? "" ?>">
                </div>
            <?elseif($this->params['email'] == 2):?>
                <div class="modal-form-line">
                    <input type="email" name="email" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$" required="required" placeholder="E-mail" value="<?= $userEmail ?? "" ?>">
                </div>
            <?endif;

            if($this->params['phone'] > 0):?>
                <div class="modal-form-line">
                    <input type="text" name="phone" placeholder="<?=System::Lang('YOUR_PHONE');?>" <?if($this->params['phone'] == 2) echo ' required="required"';?>>
                </div>
            <?php endif;

            if($this->params['field1'] != 'no'){
                echo renderField($this->params['field1'], 1, $this->params['field1_name'], $this->params['field1_data']);
            }?>


            <?if($this->params['field2'] != 'no'){
                echo renderField($this->params['field2'], 2, $this->params['field2_name'], $this->params['field2_data']);
            }

            if($this->params['message'] > 0):?>
                <div class="modal-form-line"><textarea name="text" cols="55" rows="7" placeholder="<?=System::Lang('YOUR_MESSAGE');?>"<?if($this->params['message'] == 2) echo ' required="required"';?>></textarea></div>
            <?php endif;?>

            <?if($this->params['politika'] == 1):?>
                <div class="modal-form-line"><label class="check_label" style="width: 100%;"><input type="checkbox" name="politika" required="required"> <span><?=System::Lang('AGREED_TO_WRITE_PERSONAL_DATA');?></span></label></div>
            <?php endif;?>

            <div class="modal-form-submit text-right mb-0"><input type="hidden" name="time" value="<?=$now;?>"/>
                <input type="hidden" name="token_sm" value="<?=md5($now.'+'.$this->settings['secret_key']);?>"/>
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                <input type="submit" class="btn-yellow-fz-16 font-bold button" name="feedback" value="<?=$this->params['button_text'];?>">
            </div>
        </form>

        <div><?=$this->params['after'];?></div>
    </div>
<?else:?>
    <div class=" login-userbox"><?=$this->params['text'];?></div>
<?endif;

    
function renderField($type, $num, $name, $data) {
    $html = '';

    switch($type) {
        case 'text': 
            if($data == 'required') $attr = ' required="required"';
            $html = '<p><input type="text" name="field'.$num.'" placeholder="'.$name.'" '.$attr.'></p>';
            break;
        case 'radio':
            $options = explode(";", $data);
            $count = 1;
            $html = "<p><strong>$name</strong></p><ul>";
            foreach($options as $option){

                $data = explode('=', $option);
                $html .= '<li><input type="radio" id="field'.$num.$count.'" name="field'.$num.'" value="'.$data[1].'"> <label for="field'.$num.$count.'">'.$data[0].'</label></li>';
                $count++;
            }
            $html .= '</ul>';
            break;
        case 'select':
            $options = explode(";", $data);
            $html = '<p><strong>'.$name.'</strong></p><p><select name="field'.$num.'">';
            foreach($options as $option){

                $data = explode('=', $option);
                $html .= '<option value="'.$data[1].'">'.$data[0].'</option>';
            }
            $html .= '</select></p>';
            break;
        case 'chekbox':
            $options = explode(";", $data);
            $count = 1;
            $html = "<p><strong>$name</strong></p><ul>";
            foreach($options as $option){

                $data = explode('=', $option);
                $html .= '<li><input type="checkbox" id="field'.$num.$count.'" name="field'.$num.'[]" value="'.$data[1].'"> <label for="field'.$num.$count.'">'.$data[0].'</label></li>';
                $count++;
            }
            $html .= '</ul>';
            break;
    }
    
    return $html;
}?>
<?php
$reCaptcha = json_decode($this->settings['reCaptcha'], true);
if(isset($reCaptcha['enable']) && $reCaptcha['enable'] == 1){ ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $reCaptcha['reCaptchaSiteKey'] ?>"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= $reCaptcha['reCaptchaSiteKey'] ?>', {action: 'submit'}).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    </script>
<?php } ?>
</body>
</html>