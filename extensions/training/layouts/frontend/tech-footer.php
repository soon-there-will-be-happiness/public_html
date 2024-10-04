<?defined('BILLINGMASTER') or die;?>

<div id="ModalAccess" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="userbox modal-userbox-2"></div>
    </div>
</div>

<?require_once("{$this->layouts_path}/tech-footer.php");?>

<?if($this->view['is_page'] == 'lesson'):
    if(isset($task) && $task['task_type'] == 2):?>
        <script>
          $(function() {
            $('form.form-complete').submit(function() {
              if ($(this).children('input[name="is_allow_submit_homework"]').val() < 1) {
                alert('Сначала пройдите тест');
                return false;
              }
            });
          });
        </script>
    <?endif;
endif;

if(in_array($this->view['is_page'], ['lesson', 'lk'])):?>
    <link rel="stylesheet" type="text/css" href="/lib/fancybox/css/jquery.fancybox.min.css" media="screen" />
    <script type="text/javascript" src="/lib/fancybox/js/jquery.fancybox.min.js"></script>
    <script>
      $(function() {
        $('.lesson-inner .user_message img, .dialog_item .user_message img').each(function() {
          let src = $(this).attr('src');
          $(this).wrapAll('<a data-fancybox="" href="'+src+'">');
        });
      });
    </script>
<?endif;?>

<? if (defined('useGallery')):
    $params = unserialize(System::getExtensionSetting('gallery'));

    $params['params']['style'] = useGallery['style'] ?? $params['params']['style'];
    $params['params']['height'] = useGallery['height'] ?? $params['params']['height'];
    $params['params']['width'] = useGallery['width'] ?? $params['params']['width'];

    $style = $params['params']['style'] ?? "slider";
?>

    <script src="/template/<?=$this->settings['template'];?>/js/gallery.min.js"></script>
    <link href="/template/<?=$this->settings['template'];?>/css/gallery.css" rel="stylesheet" type="text/css" />

    <?if($style == 'grid'){?>
    <script src="/template/<?=$this->settings['template'];?>/themes/tiles/theme-tilesgrid.js"></script>
<?php }?>

    <?if($style == 'columns' || $style == 'justified' ){?>
    <script src="/template/<?=$this->settings['template'];?>/themes/tiles/theme-tiles.js"></script>
<?php }?>

    <?if($style == 'slider'){?>
    <script src="/template/<?=$this->settings['template'];?>/themes/slider/theme-slider.js" type="text/javascript"></script>
<?php }?>

    <?if($style == 'carousel'){?>
    <script src="/template/<?=$this->settings['template'];?>/themes/carousel/theme-carousel.js" type="text/javascript"></script>
<?php }?>

    <script>
        jQuery(document).ready(function(){
            jQuery("#gallery").unitegallery({
                <?if($style == 'columns'){?>
                gallery_theme:"tiles",
                tile_show_link_icon: true,
                <?if(isset($params['params']['width'])):?>
                tiles_col_width:<?=$params['params']['width'];?>,
                <?endif;?>
                lightbox_textpanel_enable_description: true,
                <?php } ?>


                <?if($style == 'justified'){?>
                gallery_theme:"tiles",
                tiles_type:"justified",
                tile_show_link_icon: true,
                <?if(isset($params['params']['height'])):?>
                tiles_justified_row_height:<?=$params['params']['height'];?>,
                <?endif;?>
                lightbox_textpanel_enable_description: true,
                <?php } ?>

                <?if($style == 'grid'){?>
                gallery_theme:"tilesgrid",
                tile_show_link_icon: true,
                lightbox_textpanel_enable_description: true,
                <?if(isset($params['params']['width'])):?>
                tile_width: <?=$params['params']['width']?>,
                tile_height: <?=$params['params']['height'];?>,
                <?endif;?>
                tile_enable_border:false,
                tile_shadow_color:"#CCCCCC",
                tile_shadow_blur:2,
                tile_shadow_spread:1,
                grid_num_rows:4,
                <?php } ?>

                <?if($style == 'slider'){?>
                gallery_theme:"slider",
                slider_control_zoom: false,
                slider_enable_arrows: true,
                slider_enable_progress_indicator: false,
                gallery_images_preload_type:"visible",
                slider_link_newpage: true,
                <?if(isset($params['params']['width'])):?>
                gallery_width: <?=$params['params']['width'];?>,
                gallery_height: <?=$params['params']['height'];?>,
                <?endif;?>

                <?if(!isset($params['params']['width'])):?>
                gallery_width: 1920,
                gallery_min_height: 250,
                <?endif;?>
                <?php }?>

                <?if($style == 'carousel'){?>
                gallery_theme: "carousel",
                <?if(isset($params['params']['width'])):?>
                tile_width: <?=$params['params']['width'];?>,
                tile_height: <?=$params['params']['height'];?>,
                <?endif;?>
                tile_show_link_icon: true,
                <?php }?>
            });
        });
    </script>
<?endif;?>

