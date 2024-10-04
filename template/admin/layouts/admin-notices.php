<?php defined('BILLINGMASTER') or die;

$count_notices = AdminNotice::getCountNotices(1);
?>
<div class="bell">
    <div class="bell-click">
        <i class="icon-bell-ring"></i>
        <?if($count_notices):?>
            <span class="bell-count"><?=$count_notices;?></span>
        <?endif;?>
    </div>

    <div class="bell-popap">
        <div class="bell-popap-top">
            <div class="bell-popap-title">Важные события</div>

            <div class="bell-popap-list">
                <?php
                $notices = AdminNotice::getNotices();
                if($notices):
                    foreach($notices as $notice):
                        $fullText = $notice['text'];
                        $text = strlen($notice['text']) > 95 ? substr($notice['text'], 0, 95)."..." : $notice['text'];
                ?>
                        <div class="bell-popap-item">
                            <div class="bell-popap-item__text bell-status--<?=$notice['status'] ? 1 : 0;?>">
                                <?if($notice['url']):?>
                                    <a href="<?=$notice['url'];?>" target="_blank" title="<?= $fullText ?>"> • <?=date('d.m.y', $notice['date'])." {$text}";?></a>
                                <?else:?>
                                    <span title="<?= $fullText ?>"> • <?=date('d.m.y', $notice['date'])." {$text}";?></span>
                                <?endif;?>
                            </div>

                            <div class="bell-popap-item__remove" data-notice_id="<?=$notice['id'];?>"><i class="icon-close"></i></div>
                        </div>
                    <?endforeach;
                endif;?>
            </div>
        </div>
        <div class="bell-popap-bottom bell-popap-remove">Очистить список</div>
    </div>
</div>