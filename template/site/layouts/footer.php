<?php defined('BILLINGMASTER') or die;

$explr = System::generateStr(3);

$setting_main_page = System::getSettingMainpage(); ?>

<?php $bottom = Widgets::RenderWidget($this->widgets, 'bottom');
$widget_arr = $bottom;
if($bottom): ?>
    <div id="bottom" class="site-bottom">
        <div class="layout">
            <?php require ("$this->widgets_path/widget_wrapper.php");?>
        </div>
    </div>
<?php endif; ?>


<footer id="footer" class="footer footer__pressed-footer">
    <?php $footer = Widgets::RenderWidget($this->widgets, 'footer');
    $widget_arr = $footer;
    if($footer):?>
        <div class="layout">
            <?php require ("$this->widgets_path/widget_wrapper.php");?>
        </div>
    <?php endif;?>

    <div class="layout">
        <div class="footer-inner">
            <div class="copyright"><?=$setting_main_page['copyright']?></div>

            <div class="footer-center">
                <div class="footer-center__inner">
                    <?php if(!empty($setting_main_page['politika_link'])):?>
                        <div class="politika-line">
                            <a data-uk-lightbox data-lightbox-type="iframe" class="politika" target="_blank" href="/privacy"><?=$setting_main_page['politika_link'];?></a>
                        </div>
                    <?php endif;?>

                    <!-- KEMSTAT-20 -->
                    <?php if(False & !empty($setting_main_page['oferta_link'])):?>
                        <div class="politika-line">
                            <a data-uk-lightbox data-lightbox-type="iframe" class="oferta" href="/oferta" target="_blank"><?=$setting_main_page['oferta_link'];?></a>
                        </div>
                    <?php endif;?>
                </div>
            </div>

            <div class="soc_buttons">
                <ul>
                    <?php $socbut = unserialize(base64_decode($this->settings['socbut']));?>
                    <?php if(!empty($socbut['tg'])):?><li><a href="<?=$socbut['tg'];?>" target="_blank"><i class="icon-telegram"></i></a></li><?php endif; ?>
					<?php if(!empty($socbut['vk'])):?><li><a href="<?=$socbut['vk'];?>" target="_blank"><i class="icon-vk-i"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['fb'])):?><li><a href="<?=$socbut['fb'];?>" target="_blank"><i class="icon-facebook"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['instagram'])):?><li><a href="<?=$socbut['instagram'];?>" target="_blank"><i class="icon-insta-2"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['ok'])):?><li><a href="<?=$socbut['ok'];?>" target="_blank"><i class="icon-odnoklassniki"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['tw'])):?><li><a href="<?=$socbut['tw'];?>" target="_blank"><i class="icon-twitter"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['youtube'])):?><li><a href="<?=$socbut['youtube'];?>" target="_blank"><i class="icon-youtube"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['google'])):?><li><a href="<?=$socbut['google'];?>" target="_blank"><i class="icon-google-plus"></i></a></li><?php endif; ?>
                    <?php if(!empty($socbut['dzen'])):?><li><a href="<?=$socbut['dzen'];?>" target="_blank"><i class="icon-dzen"></i></a></li><?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <? /* Телефон и е-мейл владельца сайта (главного админа)
    <div class="contact_box">
    <span class="tel"><a href="tel:<?=$this->settings['phone_link'];?>"><?=$this->settings['phone'];?></a></span>
    <?php if(!empty($this->settings['support_email'])):?>
    <span class="support_em">
                <?php $email_arr = explode("@", $this->settings['support_email']);?>
        <script>
                var login = '<?=$email_arr[0];?>';
                var server = '<?=$email_arr[1];?>';
                var email = login + '@' + server;
                var url = 'mailto:' + email;
                document.write('<a href="' + url + '">' + email + '</a>');
                </script></span>
    <?php endif; ?>
  </div>
  */ ?>

</footer>

<div id="toTop" class="totop"><i class="icon-totop"></i></div>

<div id="modal-login" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="userbox modal-userbox" id="<?=$explr?>">
            <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close">
                <span class="icon-close"></span>
            </a>

            <form action="/login" method="POST">
                <h3 class="modal-head"><?=System::Lang('AUTHORIZATION');?></h3>
                <?php // РАСШИРЕНИЕ AUTOPILOT
                /*if (System::CheckExtensension('autopilot', 1)) {
                    require_once (ROOT.'/extensions/autopilot/views/simple/vk-auth.php');
                }
                if (System::CheckExtensension('telegram', 1)) {
                    require_once (ROOT . '/extensions/telegram/views/tg-auth.php');
                }*/
                ?>

                <div class="modal-form-line">
                    <script>
                        document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBpZD0iYXV0b0lucHV0X2lLNWlvdzhEIiBuYW1lPSJlbWFpbCIgcGxhY2Vob2xkZXI9IkUtbWFpbCIgcmVxdWlyZWQ9InJlcXVyZWQiPg=="));
                    </script>
                    
                    <? /* base64: <input type="email" id="autoInput_iK5iow8D" name="email" placeholder="E-mail" required="requred"> */ 
                    if(isset($_COOKIE['emnam'])): ?>
                    <script>
                        document.getElementById('autoInput_iK5iow8D').value = "<?=explode('=', $_COOKIE['emnam'])[0];?>";
                    </script>
                    <? endif; ?>
                </div>

                <div class="modal-form-line">
                    <input type="password" name="pass" placeholder="Password" required="required">
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
                <?php if ($this->settings['enable_registration']):?>
                    <div class="modal-form-reg">
                        <a href="/lk/registration"><?=System::Lang('REGISTRATION');?></a>
                    </div>
                <?php endif;?>
                <div class="modal-form-forgot">
                    <a href="/forgot"><?=System::Lang('FORGOT_PASSWORD');?></a>
                </div>
            </div>

            <div class="modal-form-connect-wrap">
                <? Connect::showAuthButtons($explr); ?>
            </div>
        </div>
    </div>
</div>