<?defined('BILLINGMASTER') or die;$_SESSION['review'] = 222222?>

<div class="login-userbox">
    <?if(isset($_GET['success']) && isset($_SESSION['review'])):?>
        <p><?=$reviews_tune['after_text'];?></p>
    <?elseif(isset($_GET['fail'])):?>
        <p>Ошибка <br /><?=$_GET['fail'];?></p>
    <?else:?>
        <h1><?=System::Lang('ADD_NEW_REVIEW');?></h1>

        <form action="" method="POST" enctype="multipart/form-data">
            <style>
                .myrange {-webkit-appearance: none;width: 200px;height: 15px;border-radius: 5px;   background: #d3d3d3;outline: none;opacity: 0.7;-webkit-transition: .2s;
                    transition: opacity .2s}
                .myrange::-webkit-slider-thumb {-webkit-appearance: none;appearance: none;width: 25px;height: 25px;border-radius: 50%; background: #4CAF50;cursor: pointer}
                .myrange::-moz-range-thumb {width: 25px;height: 25px;border-radius: 50%;background: #4CAF50;cursor: pointer}
                #demo {font-weight:bold; font-size:1.5em; color:#359a39}
            </style>

            <div class="userbox">
                <div class="form-line"><label for="name"><?=System::Lang('YOUR_NAME');?> </label>
                    <div class="form-line-input">
                        <input type="text" name="name" required="required" id="name">
                    </div>
                </div>

                <?if($reviews_tune['email'] >= 1):?>
                    <div class="form-line"><label for="email"><?=System::Lang('YOUR_EMAIL');?> </label>
                        <div class="form-line-input">
                            <input type="email" name="email" <?if($reviews_tune['email'] == 2) echo ' required="required"';?> id="email">
                        </div>
                    </div>
                <?endif;

                if($reviews_tune['site_url'] >= 1):?>
                    <div class="form-line"><label for="site_url"><?=System::Lang('YOUR_SITE');?> </label>
                        <div class="form-line-input">
                            <input type="text" name="site_url" <?if($reviews_tune['site_url'] == 2) echo ' required="required"';?> id="site_url">
                        </div>
                    </div>
                <?endif;

                if($reviews_tune['vk_url'] >= 1):?>
                    <div class="form-line"><label for="vk_url"><?=System::Lang('VK_LINK');?> </label>
                        <div class="form-line-input">
                            <input type="text" name="vk_url" <?if($reviews_tune['vk_url'] == 2) echo ' required="required"';?> id="vk_url">
                        </div>
                    </div>
                <?endif;

                if($reviews_tune['fb_url'] >= 1):?>
                    <div class="form-line">
                        <label for="fb_url"><?=System::Lang('FB_LINK');?> </label>
                        <div class="form-line-input">
                            <input type="text" name="fb_url" <?if($reviews_tune['fb_url'] == 2) echo ' required="required"';?> id="fb_url">
                        </div>
                    </div>
                <?endif;

                if($reviews_tune['rate'] >= 1):?>
                    <div class="form-line"><label><?=System::Lang('YOUR_ASSESSMENT');?> <span id="demo"></span></label>
                        <div class="form-line-input" style="padding-top: 20px">
                            <input type="range" min="0" max="5" value="1" name="range" class="myrange" id="myRange">
                        </div>
                    </div>

                    <script>
                      var slider = document.getElementById("myRange");
                      var output = document.getElementById("demo");
                      output.innerHTML = slider.value; // Display the default slider value

                      // Update the current slider value (each time you drag the slider handle)
                      slider.oninput = function() {
                        output.innerHTML = this.value;
                      }
                    </script>
                <?endif;

                if($reviews_tune['photo'] > 0 ):?>
                    <div class="form-line"><label for="photo"><?=System::Lang('ADD_PHOTO');?> </label>
                        <div class="form-line-input">
                            <input type="file" name="photo" <?if($reviews_tune['photo'] == 2) echo ' required="required"';?> id="photo">
                        </div>
                    </div>
                <?endif;?>

                <div class="form-line"><label for="email"><?=System::Lang('YOUR_REVIEW');?></label>
                    <div class="form-line-input textarea-big">
                        <script>document.write(window.atob("PHRleHRhcmVhIG5hbWU9InJldmlldyIgY29scz0iNjAiIHJvd3M9IjkiIHJlcXVpcmVkPSJyZXF1aXJlZCI+PC90ZXh0YXJlYT4="));</script>
                        <input type="hidden" name="time" value="<?echo time();?>">
                    </div>
                </div>

                <div class="modal-form-submit text-right mb-0">
                    <input type="submit" class="button btn-blue" value="Отправить" name="addreview">
                </div>
            </div>
        </form>
    <?endif;?>
</div>