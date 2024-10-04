<?php defined('BILLINGMASTER') or die;
$is_show_cpbutton = CallPassword::isShowButton($user);

# окно настроек Connect
System::modalFormGenerate("connect_setting", "/connect/ajax/lk/setting", ['method' => 'setting', 'dd' => 'h'], 'connect_set');
?>

<div class="login-userbox" style="position:relative;">
    <?if($is_show_cpbutton):?>
        <div class="loader_box">
            <div class="loader"></div>
        </div>
    <?endif;?>

    <h1 class="cource-head"><?=System::Lang('MY_PROFILE');?></h1>

    <?php if(isset($_GET['success'])):?>
        <div class="success_message">
            <span class="icon-check"></span><?=System::Lang('USER_SUCCESS_MESS');?>
        </div>
    <?endif;?>
    <?php if(isset($_GET['dublemail'])):?>
        <div class="warning_message">
            <?=System::Lang('USER_DUBLEMAIL_MESS');?>
        </div>
    <?endif;?>
    
    <?php if (isset($_GET['registered_by_connect'])) { ?>
    <div class="alert-message">
        Вы зарегистрировались в системе через telegram. Укажите вашу почту и имя
    </div>
<?php } ?>
<?if($user && isset($_SESSION['name'])):?>
    <div class="client-menu <?=$widget_params['params']['orient'] == 'gorizontal' ? 'gorizontal' : 'vertical';?>">
        <div class="client-menu__row">
            <div class="client-menu__left">
                <img id="avatar" src="<?=User::getAvatarUrl($user, $this->settings);?>"/>
                <?if($avatar_enable):?>
                    <form method="post">
                        <input type="file" name="image" class="image input-avatar" data-browse="<?=System::Lang('UPLOAD_PHOTO');?>">
                    </form>
                <?endif;?>
            </div>
        </div>
    </div>

            

    <?if($avatar_enable):?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" integrity="sha256-jKV9n9bkk/CTP8zbtEtnKaKf+ehRovOYeKoyfthwbC8=" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js" integrity="sha256-CgvH7sz3tHhkiVKh05kSUgG97YtzYNnWt6OXcmYzqHY=" crossorigin="anonymous"></script>

        <div id="modal-avatar" class="uk-modal">
            <div class="uk-modal-dialog">
                <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close">
                    <span class="icon-close"></span>
                </a>

                <div class="uk-modal-body">
                    <div class="img-container">
                        <img id="image" src="">
                        <div class="preview"></div>
                    </div>
                </div>

                <div class="uk-modal-footer">
                    <button type="button" class="btn-green" id="crop"><?=System::Lang('UPLOAD');?></button>
                </div>
            </div>
        </div>
    <?endif;?>
<?endif;?>

    <form action="" method="POST">
        <?php $use_sorts = $custom_fields ? array_column($custom_fields, 'field_sort') : [];
        $form_items = [
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'title' => System::Lang('YOUR_NAME'),
                'name' => 'name',
                'value' => $user['user_name'],
                'city' => $user['city'],
            ],
        ];

        if ($this->settings['show_surname'] != 0) {
            $form_items[] = [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'title' => System::Lang('YOUR_SURNAME'),
                'name' => 'surname',
                'value' => $user['surname'],
            ];
        }

        if ($this->settings['show_patronymic'] != 0) {
            $form_items[] = [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'title' => System::Lang('YOUR_PATRONYMIC'),
                'name' => 'patronymic',
                'value' => $user['patronymic'],
            ];
        }

        $form_items = array_merge($form_items, [
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'title' => System::Lang('YOUR_EMAIL'),
                'name' => 'email',
                'value' => $user['email'],
                'readOnly' => true,
                'city' => $user['city'],
            ],
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'name' => 'phone',
            ],
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'name' => 'nick_telegram',
            ],
            /*[
                'sort' => Helpers::getNextSortValue($use_sorts),
                'title' => System::Lang('INSTAGRAM_NIK'),
                'name' => 'nick_instagram',
                'value' => $user['nick_instagram'],
            ],*/
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'title' => System::Lang('ADRESS_VK'),
                'name' => 'vk_url',
                'value' => $user['vk_url'],
            ],
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'name' => 'sex',
            ],
            [
                'sort' => Helpers::getNextSortValue($use_sorts),
                'name' => 'bith_day',
            ],
        ]);

        if ($custom_fields) {
            foreach ($custom_fields as $custom_field) {
                $form_items[] = [
                    'sort' => $custom_field['field_sort'],
                    'title' => $custom_field['field_name'],
                    'name' => "",
                    'type' => 'custom_field',
                    'data' => $custom_field
                ];
            }
            $form_items = Helpers::arraySort($form_items);
        }

        foreach ($form_items as $form_item):
            if($form_item['name'] == 'phone'):?>
                <div class="form-line"><label><?=System::Lang('YOUR_PHONE');?></label>
                    <div class="form-line-input <?=$is_show_cpbutton ? ' button_right_box' : '';?>">
                        <input type="text" name="phone" value="<?=$user['phone'];?>">
                        <?if($is_show_cpbutton):?>
                            <a id="cp_confirm" class="btn getlink btn-red button_right" href="javascript:void(0);"><?=System::Lang('CONFIRM');?></a>
                        <?endif;?>
                    </div>
                </div>
            <?elseif($form_item['name'] == 'nick_telegram'):?>
                <div class="form-line" data-id="nick_telegram">
                    <label><?=System::Lang('YOUR_TELEGRAM');?></label>
                    <div class="form-line-input connect_btn">
                        <input type="text" name="nick_telegram" readonly disabled value="<?=$user['nick_telegram'];?>">
                        <a href="javascript:void(0);" 
                            data-set_elmnt_id="nick_telegram"
                            data-connect="telegram" 
                            data-connect-setting_text="<?=System::Lang('SETTINGS')?>" 
                            data-connect-attach_text="<?=System::Lang('BIND');?>" 
                            class="btn getlink btn-red button_right"
                        >
                            <span class="loading_text-ani"></span>
                        </a>
                    </div>
                </div>
            <?elseif($form_item['name'] == 'vk_url'):?>
                <div class="form-line" data-id="vk_url">
                    <label><?=System::Lang('ADRESS_VK');?></label>
                    <div class="form-line-input connect_btn">
                        <input type="text" name="vk_url" readonly disabled value="<?=$user['vk_url'];?>">
                        <a href="javascript:void(0);" 
                            data-set_elmnt_id="vk_url"
                            data-connect="vkontakte" 
                            data-connect-setting_text="<?=System::Lang('SETTINGS')?>" 
                            data-connect-attach_text="<?=System::Lang('BIND');?>" 
                            class="btn getlink btn-red button_right"
                        >
                            <span class="loading_text-ani"></span>
                        </a>
                    </div>
                </div>
            <?elseif($form_item['name'] == 'sex'):?>
                <div class="form-line"><label><?=System::Lang('POL');?></label>
                    <div class="form-line-input">
                        <div class="select-wrap">
                            <select name="sex">
                                <option value=""><?=System::Lang('NOT_SELECTED');?></option>
                                <option value="male"<?php if($user['sex'] == 'male') echo ' selected="selected"';?>><?=System::Lang('MAN');?></option>
                                <option value="female"<?php if($user['sex'] == 'female') echo ' selected="selected"';?>><?=System::Lang('WOMAN');?></option>
                            </select>
                        </div>
                    </div>
                </div>
            <?php ?>
                <div class="form-line"><label><?=System::Lang('CITY');?></label>
                    <div class="form-line-input">
                        <div class="select-wrap">
                            <input type="text" name="city" value="<?=$user['city'];?>">
                        </div>
                    </div>
                </div>
            <?elseif($form_item['name'] == 'bith_day'):?>
                <div class="form-line"><label><?=System::Lang('BERTHDAY_DATE');?></label>
                    <div class="form-line-input">
                        <div class="form-line-inner">
                            <div class="form-line-inner-col">
                                <div class="select-wrap">
                                    <select name="bith_day">
                                        <option value=""><?=System::Lang('DAY');?></option>
                                        <?php $day = 1;
                                        while($day <= 31):?>
                                            <option value="<?=$day;?>"<?php if($user['bith_day'] == $day) echo ' selected="selected"';?>>
                                                <?=$day++;?>
                                            </option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-line-inner-col">
                                <div class="select-wrap">
                                    <select name="bith_month">
                                        <option value=""><?=System::Lang('MONTH');?></option>
                                        <option value="1"<?php if($user['bith_month'] == 1) echo ' selected="selected"';?>><?=System::Lang('JAN');?></option>
                                        <option value="2"<?php if($user['bith_month'] == 2) echo ' selected="selected"';?>><?=System::Lang('FEB');?></option>
                                        <option value="3"<?php if($user['bith_month'] == 3) echo ' selected="selected"';?>><?=System::Lang('MAR');?></option>
                                        <option value="4"<?php if($user['bith_month'] == 4) echo ' selected="selected"';?>><?=System::Lang('APR');?></option>
                                        <option value="5"<?php if($user['bith_month'] == 5) echo ' selected="selected"';?>><?=System::Lang('MAY');?></option>
                                        <option value="6"<?php if($user['bith_month'] == 6) echo ' selected="selected"';?>><?=System::Lang('JUN');?></option>
                                        <option value="7"<?php if($user['bith_month'] == 7) echo ' selected="selected"';?>><?=System::Lang('JUL');?></option>
                                        <option value="8"<?php if($user['bith_month'] == 8) echo ' selected="selected"';?>><?=System::Lang('AUG');?></option>
                                        <option value="9"<?php if($user['bith_month'] == 9) echo ' selected="selected"';?>><?=System::Lang('SEP');?></option>
                                        <option value="10"<?php if($user['bith_month'] == 10) echo ' selected="selected"';?>><?=System::Lang('OKT');?></option>
                                        <option value="11"<?php if($user['bith_month'] == 11) echo ' selected="selected"';?>><?=System::Lang('NOV');?></option>
                                        <option value="12"<?php if($user['bith_month'] == 12) echo ' selected="selected"';?>><?=System::Lang('DEC');?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-line-inner-col">
                                <div class="select-wrap">
                                    <select name="bith_year">
                                        <option value=""><?=System::Lang('YEAR');?></option>
                                        <?php $year = 2018;
                                        while($year > 1919):?>
                                            <option value="<?=$year;?>" <?php if($user['bith_year'] == $year) echo ' selected="selected"';?>>
                                                <?=$year--;?>
                                            </option>
                                        <?php endwhile;?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?else:?>
                <div class="form-line"><label><?=$form_item['title'];?></label>
                    <div class="form-line-input">
                        <?if(isset($form_item['type']) && $form_item['type'] == 'custom_field'):
                            echo CustomFields::getFieldTag2LK($form_item['data'], $user_id);
                        else:?>
                            <input 
                                type="text" 
                                name="<?=$form_item['name'];?>" 
                                value="<?=$form_item['value'];?>" 
                                <?php echo (isset($form_item['readOnly']) && $form_item['readOnly']) ? 'readOnly=""' : '' ?> 
                            >
                        <?endif;?>
                    </div>
                </div>
            <?endif;
        endforeach;?>

        <div class="form-line-submit">
            <input class="btn-yellow-fz-16 font-bold button" type="submit" name="update" value="<?=System::Lang('UPDATE_MYDATA');?>">
        </div>
    </form>

    <div class="text-right" style="margin-top: 20px;">
        <a href="/lk/changepass"><?=System::Lang('CHANGE_PASSWORD');?></a>
    </div>
</div>

<style>
    .client-menu {
        padding:0px 0px 15px;
    }
    .client-menu__left {
        margin: 0 auto;
    }
    
    .form-line .check_label {
        padding-left: 0;
        padding-top: 0;
    }
    .form-line .custom-radio {
        margin-left: 0;
        padding-left: 24px;
        padding-top: 3px;
    }
</style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var $modal = UIkit.modal('#modal-avatar');
                var image = document.getElementById('image');
                var cropper;

                $("body").on("change", ".image", function(e){
                    var files = e.target.files;
                    var done = function (url) {
                        image.src = url;
                        $modal.show();
                    };
                    var reader;
                    var file;
                    var url;

                    if (files && files.length > 0) {
                        file = files[0];

                        if (URL) {
                            done(URL.createObjectURL(file));
                        } else if (FileReader) {
                            reader = new FileReader();
                            reader.onload = function (e) {
                                done(reader.result);
                            };
                            reader.readAsDataURL(file);
                        }
                    }


                });


                $modal.on('show.uk.modal', function () {
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                    });
                }).on('hide.uk.modal', function () {
                    cropper.destroy();
                    cropper = null;
                });

                $("#crop").click(function(){
                    canvas = cropper.getCroppedCanvas({
                        width: 160,
                        height: 160,
                        minWidth: 256,
                        minHeight: 256,
                        maxWidth: 1080,
                        maxHeight: 1080,
                        fillColor: '#fff',
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob(function(blob) {
                        url = URL.createObjectURL(blob);
                        var reader = new FileReader();
                        reader.readAsDataURL(blob);
                        reader.onloadend = function() {
                            var base64data = reader.result;

                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: "<?=$this->settings['script_url']?>/upload-avatar?token=<?=isset($_SESSION['user_token']) ? $_SESSION['user_token'] : '';?>",
                                data: {image: base64data},
                                success: function(data){
                                    $modal.hide();
                                    document.getElementById("avatar").src = data;
                                    document.getElementById("avatar-top").src = data;
                                }
                            });
                        }
                    });
                })
            }, false);
        </script>
</html>