<?php defined('BILLINGMASTER') or die;
$metriks = null;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('REG_PARTNER');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'reg_partner', 'submit');" : null;

if(!empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1) {
    $metriks = ' onsubmit="'.$ya_goal.$ga_goal.' return true;"';
}?>

<div class="login-userbox">
    <h1><?=System::Lang('BECOME_PARTER');?></h1>

    <?if(isset($message)):?>
        <p><?=$message;?></p>
    <?else:?>
        <form action="" method="POST"<?=$metriks;?>>
            <div class="form-line"><label><?=System::Lang('YOUR_NAME');?></label>
                <div class="form-line-input">
                    <input type="text" name="name">
                </div>
            </div>

            <div class="form-line"><label><?=System::Lang('YOUR_EMAIL');?></label>
                <div class="form-line-input">
                    <script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCIgcmVxdWlyZWQ9InJlcXVpcmVkIj4="));</script>
                </div>
            </div>

            <div class="form-line"><label><?=System::Lang('DEVISE_PASSWORD');?></label>
                <div class="form-line-input">
                    <input type="text" name="pass" required="required">
                </div>
            </div>

            <div class="form-line">
                <label><?=System::Lang('SMALL_ABOUT_YOU');?></label>
                <div class="form-line-input textarea-big textarea-big-max-width">
                    <textarea name="about" cols="53" rows="4"></textarea>
                </div>
            </div>

            <div class="modal-form-submit text-right mb-0">
                <input type="hidden" name="tm" value="<?=time();?>">
                <input type="submit" name="affreg" class="button btn-blue" value="Зарегистрироваться">
            </div>
        </form>
    <?endif;?>
</div>