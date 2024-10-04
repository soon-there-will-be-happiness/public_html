<?php defined('BILLINGMASTER') or die;?>

<div class="login-userbox">
    <h1><?=System::Lang('REMMEMBER_PASSWORD');?></h1>
    <div class="userbox">
        <?php if(isset($_GET['mess']) && $_GET['mess'] == 'ok'):?>
            <p><?=System::Lang('FORGOT_MESS');?></p>
        <?php else:?>
            <form action="" method="POST">
                <div class="form-line">
                    <label><?=System::Lang('YOUR_EMAIL');?></label>
                    <div class="form-line-input">
                        <script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCIgcmVxdWlyZWQ9InJlcXVyZWQiPg=="));</script>
                    </div>
                </div>

                <div class="modal-form-submit text-right mb-0">
                    <input type="submit" value="<?=System::Lang('REMEMBER_PASSWORD');?>" class="btn-yellow-fz-16 font-bold button" name="forgot">
                </div>
            </form>
        <?php endif;?>
    </div>
</div>