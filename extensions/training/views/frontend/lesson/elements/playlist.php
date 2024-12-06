<?php defined('BILLINGMASTER') or die;
$playlist = Traininglesson::getPlaylistItems($element['id']);
$playlist2player = [];?>

<?php if($playlist):?>
    <div class="player-wrap">
        <div class="player-content" data-title="<?=$element['params']['title'];?>">
            <?php foreach ($playlist as $key => &$playlist_item):
                if($playlist_item['params']['type'] == 1): //infoprotector?>
                    <a href="<?=$playlist_item['params']['url'];?>" target="_blank"<?php if($key > 0) echo ' style="display: none;"';?>>
                        <img src="<?=$playlist_item['params']['cover'];?>" alt="">
                    </a>
                <?php elseif($playlist_item['params']['type'] == 2 || $playlist_item['params']['type'] == 3): //video and audio
                    $playlist2player[] = $playlist_item;?>
                    <a href="javascript:void(0)" <?php if($key > 0) echo ' style="display: none;"';?>>
                        <div class="playlist_item" id="player_<?=$playlist_item['id'];?>" data-url="<?=base64_encode(trim($playlist_item['params']['url']));?>" data-cover="<?=$playlist_item['params']['cover'];?>" data-wid="<?php if($playlist_item['params']['show_watermark'] != false): echo $watermark; endif;?>"></div>
                    </a>
                <?php elseif($playlist_item['params']['type'] == 4): //youtube or vimeo
                    if(strpos($playlist_item['params']['url'], 'vimeo.com') !== false): //vimeo
                        if (strpos($playlist_item['params']['url'], 'https://vimeo.com') === 0) {
                            $playlist_item['params']['url'] = 'https://player.vimeo.com/video'.str_replace('https://vimeo.com', '', $playlist_item['params']['url']);
                        }?>
                        <a href="javascript:void(0)"<?php if($key > 0) echo ' style="display: none;"';?>>
                            <div class="video-responsive">
                                <iframe src="<?=$playlist_item['params']['url'];?>" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                            </div>
                        </a>
                    <?php else: //youtube?>
                        <a href="javascript:void(0)" <?php if($key > 0) echo ' style="display: none;"';?>>
                            <div class="video-responsive">
                                <iframe src="<?=$playlist_item['params']['url'] = System::getYoutubeUrl2Iframe($playlist_item['params']['url']);?>" frameborder="0" modestbranding="1" showinfo="0" rel="0" enablejsapi="1" allowfullscreen></iframe>
                            </div>
                        </a>
                    <?php endif;?>
                <?php elseif ($playlist_item['params']['type'] == 6): //kinescope video?>
                    <a href="javascript:void(0)"<?php if ($key > 0) echo ' style="display: none;"'; ?>>
                        <div class="playlist_item_k" id="k_player_<?= $playlist_item['id']; ?>"
                             data-url="<?= $playlist_item['params']['url']; ?>"><?= $playlist_item['params']['url']; ?></div>
                    </a>
                <?php elseif ($playlist_item['params']['type'] == 7): //Rutube?>
                    <a href="javascript:void(0)"<?php if ($key > 0) echo ' style="display: none;"'; ?>>
                        <div class="video-responsive">
                            <iframe src="<?= System::getRutubeUrl2Iframe($playlist_item['params']['url']); ?>"
                                    frameborder="0" modestbranding="1" showinfo="0" rel="0" enablejsapi="1"
                                    allowfullscreen></iframe>
                        </div>
                    </a>

                <?php elseif($element['params']['element_type'] == 8): //PeerTube video work??? ?>
                    <div class="video-responsive">
                        <iframe width="560" height="315" src="$element['params']['url']" frameborder="0" allowfullscreen="" sandbox="allow-same-origin allow-scripts allow-popups allow-forms"></iframe>
                    </div>
                <?php elseif($playlist_item['params']['type'] == 5): //изображение?>
                    <a href="<?=$playlist_item['params']['cover'];?>" data-uk-lightbox="{group:'group2'}" data-lightbox-width="900" <?php if($key > 0) echo ' style="display: none;"';?>>
                        <img src="<?=$playlist_item['params']['cover'];?>" alt="">
                    </a>
                <?php endif;
            endforeach;?>
        </div>

        <div class="playlist-wrap">
            <div class="open-playlist">
                <div class="open-playlist-btn"><span></span><span></span><span></span></div>
                <div class="open-playlist-word"><span><?=System::Lang('PLAY_LIST');?></span></div>
            </div>
            <div class="player-playlist">
                <div class="playlist-top">
                    <div class="playlist-title"><?=$element['params']['title'];?></div>
                    <div class="playlist-count">
                        <div class="playlist-count-icon"></div>
                        <div class="playlist-count-text"><?=count($playlist)?></div>
                    </div>
                </div>

                <div class="playlist-content">
                    <?php foreach ($playlist as $key => $playlist__item):?>
                        <a href="<?=$playlist__item['params']['url'];?>" class="playlist-link<?=$key == 0 ? ' active' : '';?>" data-type="<?=$playlist__item['params']['type'];?>">
                            <div class="playlist-link-number"><?=$key+1;?></div>
                            <div class="playlist-link-title"><?=$playlist__item['params']['title'];?></div>
                            <div class="playlist-link-time"><?=$playlist__item['params']['time'];?></div>
                        </a>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // 3. Эта функция создаст <iframe> (и Kinescope Player).
        // Она будет вызвана автоматически когда скрипт API будет загружен.
        function onKinescopeIframeAPIReady(playerFactory) {
            <?php foreach ($playlist as $key => $playlist__item):
            if ($playlist__item['params']['type'] == 6):
            $watermark_text = (isset($playlist__item['params']['show_watermark']) && $playlist__item['params']['show_watermark'] == 1) ? $watermark : ' ';
            $scale_watermark = isset($playlist__item['params']['show_watermark_scale']) && is_numeric($playlist__item['params']['show_watermark_scale']) ? $playlist__item['params']['show_watermark_scale'] : 0;
            $time_visible = isset($playlist__item['params']['show_watermark_visible']) && is_numeric($playlist__item['params']['show_watermark_visible']) ? $playlist__item['params']['show_watermark_visible'] * 1000 : 0;
            $time_hidden = isset($playlist__item['params']['show_watermark_hidden']) && is_numeric($playlist__item['params']['show_watermark_hidden']) ? $playlist__item['params']['show_watermark_hidden'] * 1000 : 0;
            ?>
            playerFactory
                .create('k_player_<?=$playlist__item['id'];?>', {
                    url: '<?=$playlist__item['params']['url'];?>',
                    size: {width: '100%', height: 434},
                    ui: {
                        watermark: {
                            text: '"<?=$watermark_text;?>"',
                            mode: 'random',
                            scale: "<?=$scale_watermark;?>",
                            displayTimeout: {visible: ". $time_visible.", hidden: ".$time_hidden."}
                        }
                    },
                })
                .then(function (player) {
                    player
                        // 4. Этот обработчик будет вызван когда плеер будет готов к проигрыванию.
                        .once(player.Events.Ready, function (event) {
                            event.target.setVolume(0.5);
                        })
                });
            <?php endif;
            endforeach;?>
        }
    </script>
    <script src="/extensions/training/web/frontend/js/playlist.js?v=<?= CURR_VER; ?>"></script>
<?php endif; ?>