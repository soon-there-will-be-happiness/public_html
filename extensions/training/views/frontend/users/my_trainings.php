<?php defined('BILLINGMASTER') or die;?>

<div class="layout" id="courses">
    <div class="content-wrap">
        <div class="maincol<?php if($sidebar) echo '_min content-with-sidebar';?>">
            <?php // Вывод промо кода
            require_once (ROOT . "/template/site/views/common/show_promo_code.php");

            // Вывод уведомления CallPassword
            if(CallPassword::isShowButton($user)):
                require_once (ROOT . '/extensions/callpassword/views/show_notice.php');
            endif;

            // Вывод уведомления Telegram
            Connect::showConnectNotice('telegram', $user['user_id'], true);
            ?>

            <h1><?=$this->seo['h1'];?></h1>

            <?php if(!empty($h2) || (isset($this->tr_settings['show_section_button']) && $this->tr_settings['show_section_button'])):?>
                <div class="widget-top">
                    <?php if(!empty($h2)):?>
                        <h2><?=$h2;?></h2>
                    <?php endif;

                    if(isset($this->tr_settings['show_section_button']) && $this->tr_settings['show_section_button']):?>
                        <div class="z-1" style="text-align: right">
                            <a class="btn-yellow btn-orange" href="/training"><?=System::Lang('GO_TO_SECTION');?></a>
                        </div>
                    <?php endif;?>
                </div>
            <?php endif;?>

            <?php // вывод тренингов
            require_once (__DIR__ . "/../training/templates/list/{$this->tr_settings['template']}.php");?>
        </div>
        <?php require_once ("{$this->layouts_path}/sidebar.php");?>
    </div>
</div>