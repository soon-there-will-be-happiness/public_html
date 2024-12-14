<?defined('BILLINGMASTER') or die;?>

<?if(!isset($jquery_head)):?>
    <script src="/template/<?=$this->settings['template'];?>/js/jquery-2.1.3.min.js"></script>
    <script src="/template/<?=$this->settings['template'];?>/js/jquery-ui-1.12.1.min.js"></script>
<?endif;?>

<script src="/template/<?=$this->settings['template'];?>/js/libs.js"></script>
<script src="/template/<?=$this->settings['template'];?>/js/scripts.js?v340"></script>

<script src="/extensions/connect/web/js/main.min.js?v<?=CURR_VER?>"></script>

<link rel="stylesheet" type="text/css" href="/template/<?=$this->settings['template']?>/css/jquery.datetimepicker.min.css">
<script src="/template/<?=$this->settings['template']?>/js/jquery.datetimepicker.full.min.js"></script>

<script>
  objectFitImages();
</script>

<?php // JavaScripts and others elements or counters
$this->view['is_page'] = isset($this->view['is_page']) ? $this->view['is_page'] : null;
if($this->view['is_page'] == 'lk' || isset($this->view['js']) || $this->settings['use_cart'] == 1):?>
    <script src="<?=$this->settings['script_url'];?>/template/<?=$this->settings['template'];?>/js/tabs.js"></script>
    <script>
    jQuery(document).ready(function(){
    	jQuery(".tabs").lightTabs();
    });
    </script>

    <?if($this->view['is_page'] == 'lk'):
        if(isset($tg_link) && $tg_link): // Подключение скрипта для расширения Telegram?>
            <script type="text/javascript" src="/extensions/telegram/web/js/main.js?v=340"></script>
        <?endif;

        if(isset($is_show_cpbutton) && $is_show_cpbutton):?>
            <script type="text/javascript" src="/extensions/callpassword/web/js/main.js"></script>
        <?endif;
    endif;
endif;?>

<script>
jQuery(function() {
    jQuery(window).scroll(function() {
        if(jQuery(this).scrollTop() > 300) {
            jQuery('#toTop').fadeIn();
        } else {
            jQuery('#toTop').fadeOut();
        }
    });
    jQuery('#toTop').click(function() {
        jQuery('body,html').animate({scrollTop:0},800);
    });
});
</script>

<?if($this->settings['use_cart'] == 1):?>
    <div id="cart_win" class="cart-win" style="display: none;"><div class="cart-win-close"><i class="icon-prod-add-close"></i></div><span class="cart-win-text"></span></div>

    <script>
        $(document).ready(function () {
        $(".add_to_cart").click(function () {
            var id = $(this).attr("data-id");
            var product = $(this).closest(".catalog_item");
            var name_product = product.find("h4").text();
            var image_product = product.find('img').attr("src");
            var currency = "<?=$this->settings['currency'];?>";
            $.post("/cart/add/"+id, {}, function (data) {
                var cart_data = JSON.parse(data);
                var old_price_product = cart_data['id'][id]['price'];
                var red_price_product = cart_data['id'][id]['real_price'];
                var totalnotdiscount = cart_data['totalnotdiscount'];
                var discount = cart_data['discount'];
                var total = cart_data['total'];
                $("#cart-count").html(cart_data['count']);
                $(".cart-win-text").html('Продукт добавлен в корзину');
                //jQuery("#cart_win").fadeOut(2000);
                $('#cart_win').css("display", "flex")
                setTimeout(function(){$('#cart_win').css("display", "none")},2000);
                //jQuery("#cart_win".css("display", "block");
                var product_tmp = `<div class="widget-cart-item">
                    <div class="widget-cart-item__left">
                        <img src=`+image_product+`>
                    </div>
                    <div class="widget-cart-item__right">
                        <div class="widget-cart-item__inner">
                            <h4 class="widget-cart-item__title">`+name_product+`</h4>
                            <div class="widget-cart-item__price" data-id="`+id+`">`;
                            if (old_price_product>red_price_product) {
                              product_tmp = product_tmp + `<div class="widget-cart-item__price-old">`+old_price_product+` `+currency+`</div>`;
                            }
                            product_tmp = product_tmp + `<div class="widget-cart-item__price-current">`+red_price_product+` `+currency+`</div>
                            </div>
                        </div>
                        <a class="widget-cart-item__delete" data-id="`+id+`" onclick="return deleteproduct(this);">
                            <span class="icon-remove"></span>
                        </a>
                    </div>
                </div>
                `;
                if ($("#empty-cart").length > 0) {
                  $("#empty-cart").remove();
                  var total = `<div class="widget-cart-all-summ">
                        <div class="widget-cart-summ"><span>Сумма заказа: </span>`+totalnotdiscount+` `+currency+`</div>
                        <div class="widget-cart-discount"><span>Скидка: </span>`+discount+` `+currency+`</div>
                        <div class="widget-cart-total"><span>Итого: </span>`+total+` `+currency+`</div>
                    </div>

                    <form action="/cart" method="POST" class="widget-cart-add-prod">
                        <input type="submit" class="button btn-blue" name="checkout" value="<?=System::Lang('CHECKOUT');?>">
                    </form>`;
                    $(".widget-cart-items").after(total);  
                } else {
                  Object.keys(cart_data['id']).forEach((element) => {
                    if (element !== id) {
                      var prod = document.querySelector('.widget-cart-item__price[data-id="'+element+'"]');
                      if (prod !== null) {
                        if (prod.querySelector('.widget-cart-item__price-old') !== null ) {
                          prod.querySelector('.widget-cart-item__price-old').textContent = cart_data['id'][element]['price']+` `+currency; 
                        } else {
                          if (cart_data['id'][element]['price']>cart_data['id'][element]['real_price']) {
                            let old_price = document.createElement('div');
                            old_price.classList.add('widget-cart-item__price-old');
                            old_price.textContent = cart_data['id'][element]['price']+` `+currency;
                            var items = prod.querySelector('.widget-cart-item__price-current');
                            items.before(old_price);
                          }
                        }
                        prod.querySelector('.widget-cart-item__price-current').textContent = cart_data['id'][element]['real_price']+` `+currency;
                      }
                    }
                  });
                  document.querySelector('.widget-cart-summ').innerHTML = `<span>Сумма заказа: </span>`+totalnotdiscount+` `+currency;
                  document.querySelector('.widget-cart-discount').innerHTML = `<span>Скидка: </span>`+discount+` `+currency;
                  document.querySelector('.widget-cart-total').innerHTML = `<span>Итого: </span>`+total+` `+currency;
                }
                if ($(".widget-cart-items").length > 0 && $("a[data-id='" + id +"']").length == 0) {
                  $(".widget-cart-items").append(product_tmp).clone(true);
                  //var widget_items = document.querySelector(".widget-cart-items");
                  //widget_items.insertAdjacentHTML('beforeend', product_tmp);
                }
            });
            return false;
        });
          $('.cart-win-close').on('click', function(){
            $('#cart_win').css("display", "none")
          });
        });
    </script>
<?endif; 

echo $this->settings['counters'];?>

<?if(defined('BM_GALLERY')):
    if ($this->view['is_page'] == 'gallery') {
        $params = unserialize(System::getExtensionSetting('gallery'));
        $style = $params['params']['style'];
    } else {
        $style = constant('BM_GALLERY');
    }?>

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

<? if(@ $this->settings['params']['mask_all_countries'] == 1):?>
    <script src="/lib/inputmask/js/jquery.inputmask.min.js?8"></script>
    <script src="/lib/inputmask/js/jquery.inputmask.js?8"></script>
    <script>

        var input_phone = $('input[name=phone]');

        input_phone.each(function (){ 
           $(this).attr("value", "7");

        });

        var maskList = $.masksSort($.masksLoad("/lib/inputmask/data/phone-codes.json"), ['#'], /[0-9]|#/, "mask");
        var maskOpts = {
            inputmask: {
                definitions: {
                    '#': {
                        validator: "[0-9]",
                        cardinality: 1
                    }
                },
                // clearIncomplete: true,
                showMaskOnHover: false,
                autoUnmask: true
            },

            match: /[0-9]/,
            replace: '#',
            list: maskList,
            listKey: "mask",

            onMaskChange: function(maskObj, completed) {

                if (completed) {
                    if (maskObj.desc_ru && maskObj.desc_ru != "")
                        maskObj.name_ru += " (" + maskObj.desc_ru + ")";
                }

                $(this).attr("placeholder", $(this).inputmask("getemptymask"));
            }
        };

        input_phone.inputmasks(maskOpts);

    </script>

<? elseif (!empty($this->settings['countries_list']) && ($countries_list = $this->settings['countries_list'])):?>

    <link href="/template/<?=$this->settings['template'];?>/css/intlTelInput-11.0.14.css" rel="stylesheet" />
    <script src="/template/<?=$this->settings['template'];?>/js/utils-11.0.14.js"></script>
    <script src="/template/<?=$this->settings['template'];?>/js/intlTelInput-11.0.14.js"></script>
    <script src="/template/<?=$this->settings['template'];?>/js/jquery.mask-1.14.11.js"></script>

    <script>
        let cntrs_list = <?=$countries_list;?>;
        let $phone_input = $("input[name='phone']");

        if ($phone_input.length == 1) {
            let iti = $phone_input.intlTelInput({
                initialCountry: cntrs_list.indexOf('ru') != -1 ? 'ru' : cntrs_list[0],
                preferredCountries: cntrs_list.indexOf('ru') != -1 ? ['ru'] : [cntrs_list[0]],
                separateDialCode: true,
                onlyCountries: cntrs_list
            });
        }

        $(document).ready(function() {
            if ($phone_input.length == 1) {

                if ($('.intl-tel-input .selected-flag .iti-flag').hasClass('ru')) {
                    $phone_input.attr('placeholder', '912 333-33-33');
                }

                let mask = $phone_input.attr('placeholder').replace(/[0-9]/g, 0);
                $phone_input.mask(mask);

                $phone_input.on("countrychange", function(e, countryData) {
                    if (countryData.iso2 == 'ru') {
                        $phone_input.attr('placeholder', '912 333-33-33');
                    }

                    mask = $phone_input.attr('placeholder').replace(/[0-9]/g, 0);
                    $phone_input.mask(mask).attr('maxlength', 13);
                });

                $phone_input.parents('form').submit(function() {
                    if ($('.selected-flag .selected-dial-code').length > 0) {
                        let phone_code = $(this).find('.selected-flag .selected-dial-code').text();
                        $(this).append('<input type="hidden" name="phone_code" value="' + phone_code + '">');
                    }

                    let phone = $phone_input.val();
                    let placeholder = $phone_input.attr('placeholder');

                    if (typeof(placeholder) !== 'undefined' && phone.length !== placeholder.length) {
                        $phone_input.addClass('error');
                        return false;
                    } else {
                        $phone_input.removeClass('error');
                    }
                });
            }
        });

    </script>
<? else: ?>
    <script>
    </script>
<? endif; ?>


<script>var editors = [];</script>
<?if($this->settings['editor'] == 1):?>
    <link rel="stylesheet" href="/lib/trumbowyg/dist/ui/trumbowyg.min.css">
    <script src="/lib/trumbowyg/dist/trumbowyg.min.js"></script>
    <script src="/lib/trumbowyg/dist/plugins/pasteembed/trumbowyg.pasteembed.js"></script>
    <script src="/lib/trumbowyg/dist/plugins/upload/trumbowyg.cleanpaste.js"></script>
    <script src="/lib/trumbowyg/dist/plugins/upload/trumbowyg.pasteimage.js"></script>
    <script src="/lib/trumbowyg/dist/langs/ru.js"></script>
    <script src="/lib/trumbowyg/dist/plugins/fontsize/trumbowyg.fontsize.js"></script>
    
    <script type="text/javascript">
      editor_transfiguration = function($el) {
        if ($el.length > 0) {
          $el.trumbowyg({
            btns: [
              ['strong', 'em', 'del', 'fontsize'],
              ['link'],
              ['insertImage'],
              ['unorderedList', 'orderedList']
            ],
            autogrow: true,
            lang: 'ru',
            removeformatPasted: false,
            plugins: {
              fontsize: {
                  sizeList: [
                      '14px',
                  ]
              },
              lang: 'ru',
          }
          });
        }
      };

      $(document).ready(editor_transfiguration($("textarea.editor")));
    </script>
<?php elseif($this->settings['editor'] == 2):?>
    <script src="/lib/ckeditor/ckeditor.js"></script>
    <script type="text/javascript">
      var editor_transfiguration = function (el) {
        let editor = CKEDITOR.replace(el, {
          uiColor: '#282f3a',
          toolbar: [
            ['Bold', 'Italic', 'Strike', '-', 'Link', '-', 'Image', '-', 'NumberedList', 'BulletedList']
          ],
          extraPlugins: 'stylesheetparser,image2,uploadimage,autogrow',
          contentsCss: '/template/<?=$this->settings['template'];?>/css/ckeditor.style.css',
          contentsJs: '/template/<?=$this->settings['template'];?>/css/ckeditor.style.css',
          stylesSet: [],
          uploadUrl: '<?=$this->settings['script_url']?>/upload-image?token=<?=isset($_SESSION['user_token']) ? $_SESSION['user_token'] : '';?>',
          height: 140,
          autoGrow_onStartup: true,
          autoGrow_minHeight: 140,
          autoGrow_maxHeight: 9999,
          autoGrow_bottomSpace: 20
        });
        editors.push(editor);
      };

      $(document).ready(function() {
        $("textarea.editor").each(function () {
          editor_transfiguration($(this).attr("name"));
        });
      });
    </script>
<?endif;?>

<script src="/template/<?=$this->settings['template'];?>/js/editor-draft.js"></script>


<?if(isset($_SESSION['user_token'])):?>
    <script type="text/javascript">
      $.ajaxSetup({
        headers: {
          'X-Csrf-Token': '<?=$_SESSION['user_token'];?>'
        }
      });

      $(document).ready(function() {
        $('form').each(function() {
            let method = $(this).attr('method');
            let action = $(this).attr('action');
            let script_url = '<?=$this->settings['script_url'];?>';
            if (method == 'POST' && (typeof(action) == 'undefined' || action == '' || action.indexOf('#') == 0 || action.indexOf('/') == 0 || action.indexOf(script_url)  == 0)) {
                $(this).append('<input type="hidden" name="token" value="<?=$_SESSION['user_token'];?>">');
            }
        });
      });
    </script>
<?endif;?>

<script src="/lib/select2/js/select2.js"></script>
<link href="/lib/select2/css/select2.css" rel="stylesheet" type="text/css" />

<?if(System::CheckExtensension('training', 1)): 
   if(isset($training_filter_enabled) && $training_filter_enabled):?>
      <link rel="stylesheet" href="/extensions/training/web/frontend/style/style.css?v=<?=CURR_VER;?>" type="text/css" />
      <script src="/extensions/training/web/frontend/js/main.js?v=<?=CURR_VER;?>"></script>
      <script src="/extensions/training/views/frontend/filter/main.js?v=<?=CURR_VER;?>"></script>
      <link href="/extensions/training/views/frontend/filter/style.css?v=<?=CURR_VER;?>" rel="stylesheet" type="text/css" />
    <?php else:?>
      <link rel="stylesheet" href="/extensions/training/web/frontend/style/style.css?v=<?=CURR_VER;?>" type="text/css" />
      <script src="/extensions/training/web/frontend/js/main.js?v=<?=CURR_VER;?>"></script>
  <?endif;?>
<?endif;

if(!$this->settings['multiple_authorizations'] && isset($_SESSION['user'])):?>
    <script>
        setInterval(() => {
            $.post("/check-session", {}, function (data) {
                if (!data.status) {
                    document.location.href = '/';
                }
            });
        }, 60000);
    </script>
<?endif;

if($this->view['is_page'] == 'order' && isset($order['expire_date']) && isset($is_show_timer) && $is_show_timer > 0):?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        if ($('#clockdiv').length > 0) {
          function getTimeRemaining(endtime) {
            const total = Date.parse(endtime) - Date.parse(new Date());
            const seconds = Math.floor((total / 1000) % 60);
            const minutes = Math.floor((total / 1000 / 60) % 60);
            const hours = Math.floor((total / (1000 * 60 * 60)) % 24);
            const days = Math.floor(total / (1000 * 60 * 60 * 24));

            return {
              total,
              days,
              hours,
              minutes,
              seconds
            };
          }

          function initializeClock(id, endtime) {
            const clock = document.getElementById(id);
            const daysSpan = clock.querySelector('.days');
            const hoursSpan = clock.querySelector('.hours');
            const minutesSpan = clock.querySelector('.minutes');
            const secondsSpan = clock.querySelector('.seconds');

            function updateClock() {
              const t = getTimeRemaining(endtime);

              daysSpan.innerHTML = t.days;
              hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
              minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
              secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

              if (t.total <= 0) {
                clearInterval(timeinterval);
              }
            }

            updateClock();
            const timeinterval = setInterval(updateClock, 1000);
          }

          const deadline = '<?=date("F d Y H:i:s O", $order['expire_date']);?>';
          initializeClock('clockdiv', deadline);
        }
      });
    </script>
<?endif;?>

<script type="text/javascript">
  $(function() {
    if ($('.success_message').length > 0) {
      setTimeout(function () {
        $('.success_message').fadeOut('fast')
      }, 4000);
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    $(document).on('click', '#promo [data-name="apply_promo"]', function(e) {
      e.preventDefault();
      let data = {promo: $(this).closest('#promo').find('[name="promo"]').val(), apply_promo: 1};

      $.ajax({
        method: 'post',
        dataType: 'html',
        data: data,
        success: function (html) {
          if ($('.cart-item').length > 0) {
            let cart_html = $(html).find('.cart-item').html();
            $('.cart-item').html(cart_html);
          } else if($('.offer.main').length > 0) {
            if ($('.offer.main .order_item').length > 0) {
              $('.offer.main .order_item').each(function(i) {
                $(this).html($(html).find('.offer.main .order_item').eq(i).html());
              });
            }

            $('.payment-itogo__total').html($(html).find('.payment-itogo__total').html());
          }

          if (html) {
            $("#promocode_msg").show('fast');
            setTimeout(function() {
              $('#promocode_msg').fadeOut('fast');
              $('.promo-block').slideToggle();
            },3000);
          }
        }
      });
    });
  });
</script>