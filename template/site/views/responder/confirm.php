<?php defined('BILLINGMASTER') or die;?>

<div id="content">
    <div class="layout" id="responder">
        <div class="content-wrap">
            <div class="maincol<?php if($sidebar) echo '_min content-with-sidebar';?>">
                <div class="maincol-inner">
                    <?if($error):?>
                        <p><?=System::Lang('ERROR');?> <?=$error; ?></p>
                        <div class="userbox bm_subs_form black">
                            <form action="" method="POST">
                                <p><input type="text" name="name" placeholder="Ваше имя" required="required"></p>
                                <p><input type="email" name="email" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$" placeholder="Ваш E-mail" required="required"></p>

                                <p><input type="submit" class="btn-yellow text-uppercase font-bold button" value="Подписаться" name="subscribe"></p>
                            </form>
                        </div>
                    <?elseif($error == false && $confirmed == 0):?>
                        <h1><?=System::Lang('CONFIRM_EMAIL');?></h1>
                        <?=System::Lang('LETTER_WITH_CONFIRM');?>

                        <p><strong><?=System::Lang('QUICK_LINKS');?></strong></p>
                        <style>
                            .icon {width:16px; height:16px}
                            .nostyle {padding:0.5em 0}
                            .nostyle li {list-style:none}
                        </style>
                        <ul class="nostyle">
                            <li><img class="icon" src="/template/<?=$this->settings['template'];?>/images/gmail.ico" alt="Google почта"> <a href="https://mail.google.com"><?=System::Lang('GMAIL');?></a></li>
                            <li><img class="icon" src="/template/<?=$this->settings['template'];?>/images/yandex.png" alt="Яндекс почта"> <a href="https://mail.yandex.ru"><?=System::Lang('YANDEX_EMAIL');?></a></li>
                            <li><img class="icon" src="/template/<?=$this->settings['template'];?>/images/mail-ru.png" alt="Почта Mail.ru"> <a href="https://e.mail.ru/messages/inbox"><?=System::Lang('MAIL_RU');?></a></li>
                        </ul>
                    <?else:?>
                        <h1><?=System::Lang('SUBSCRIBED_NEWSLETTER');?></h1>
                    <?endif;?>
                </div>
            </div>
            <?require_once ("{$this->layouts_path}/sidebar.php");?>
        </div>
    </div>
</div>