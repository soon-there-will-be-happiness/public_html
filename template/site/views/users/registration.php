<?defined('BILLINGMASTER') or die;
$o= $_GET['o'];

$is_show_cpbutton = false;
$call_password = System::CheckExtensension('callpassword', 1);
if($call_password){
    $params = json_decode($call_password['params'], 1);
    $is_show_cpbutton = isset($params['params']['register']) && $params['params']['register'] == 1 ? true : false;
}

$use_sorts = $custom_fields ? array_column($custom_fields, 'field_sort') : [];
$form_items = [
    [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('YOUR_NAME'),
        'name' => 'name',
        'value' => isset($name) ? $name : '',
        'type' => 'text',
        'required' => true,
    ],
];

if ($this->settings['show_surname']) {
    $form_items[] = [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('YOUR_SURNAME'),
        'name' => 'surname',
        'value' => isset($surname) ? $surname : '',
        'type' => 'text',
        'required' => true,
        'add_html' => '<input type="hidden" name="fio">'
    ];
}

if ($this->settings['show_patronymic']) {
    $form_items[] = [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('YOUR_PATRONYMIC'),
        'name' => 'patronymic',
        'value' => isset($patronymic) ? $patronymic : '',
        'type' => 'text',
        'required' => true,
    ];
}

$form_items = array_merge($form_items, [
    [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('YOUR_EMAIL'),
        'name' => 'email',
        'value' => isset($name) ? $name : '',
        'type' => 'email',
        'required' => true,
        'params' => 'pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$"'
    ],
    [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('YOUR_PHONE'),
        'name' => 'phone',
        'value' => isset($phone) ? $phone : '',
        'type' => 'text',
        'required' => true,
        'class' => $is_show_cpbutton ? 'button_right_box' : null,
        'add_html' => $call_password && $is_show_cpbutton ? '<a id="cp_confirm" class="btn getlink btn-red button_right" href="javascript:void(0);">'.System::Lang('CONFIRM').'</a>' : null,
    ],
    [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('YOUR_PASSWORD'),
        'name' => 'pass',
        'value' => isset($pass) ? $pass : '',
        'type' => 'password',
        'required' => true,
        'add_html' => "<input type=\"hidden\" name=\"time\" value=\"$timer\">".
            (isset($_COOKIE[$this->settings['cookie']]) ? ("<input type=\"hidden\" name=\"sign\" value=\"".md5("{$timer}+{$this->settings['secret_key']}").'">') : ''),
        'params' => 'minlength="6"',
    ],
    [
        'sort' => Helpers::getNextSortValue($use_sorts),
        'title' => System::Lang('CONFIRM_PASSWORD'),
        'name' => 'confirm_pass',
        'value' => isset($confirm_pass) ? $confirm_pass : '',
        'type' => 'password',
        'required' => true,
        'params' => 'minlength="6"',
    ],
]);

if ($custom_fields) {
    foreach ($custom_fields as $custom_field) {
        $form_items[] = [
            'sort' => $custom_field['field_sort'],
            'title' => $custom_field['field_name'],
            'name' => "",
            'type' => 'custom_field',
            'data' => $custom_field,
            'required' => false,
        ];
    }
    $form_items = Helpers::arraySort($form_items);
}
?>

<div class="login-userbox">
    <h1><?=System::Lang('REGISTRATION');?></h1>

    <?if(isset($_SESSION['reg_status'])):?>
        <div class="userbox">
            <?if($_SESSION['reg_status'] == 1):?>
                <p><?=System::Lang('APPROVAL_EMAIL');?></p>
            <?elseif($_SESSION['reg_status'] == 2):?>
                <?=System::Lang('LINK_LOGED_SUCCESSFULL');?>
            <?endif;?>
        </div>
    <?else:
        if (User::hasError()) {
            User::showError('warning_message');
        }?>

        <form action="" method="POST" onsubmit="onClick(e)">
            <?foreach($form_items as $form_item):?>
                <div class="form-line"><label><?=$form_item['title'];?><sup class="required-red">*</sup></label>
                    <div class="form-line-input<?=isset($form_item['class']) ? " {$form_item['class']}": '';?>">
                        <?if(isset($form_item['type']) && $form_item['type'] == 'custom_field'):
                            echo CustomFields::getFieldTag2LK($form_item['data'], null);
                        elseif($form_item['name'] == 'email' && $this->settings['email_protection']):?>
                            <script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCI="));</script> required="required" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$">
                        <?else:?>
                            <input<?$form_item['required'] ? ' required' : '';?> type="<?=$form_item['type'];?>" name="<?=$form_item['name'];?>"
                                 value="<?=$form_item['value'];?>"<?if(isset($form_item['params'])) echo " {$form_item['params']}";?>>
                            <?if (isset($form_item['add_html'])) {
                                echo $form_item['add_html'];
                            }?>
                        <?endif;?>
                    </div>
                </div>
            <?endforeach;

            if(($params = @$this->settings['params']) && $params && @json_decode($params, true)['must_agree_yopd'] == 1):?>
                <div class="form-line" style="padding: 0 0 0 20px"><label class="check_label register">
                        <input type="checkbox" name="politika" required="required">
                        <?if(!isset($_SESSION['org'])):?>
                            <span><?=System::Lang('LINK_CONFIRMED');?></span>
                        <?else:?>
                            <span><?=System::Lang('LINK_CONFIRMED_2');?></span>
                        <?endif;?>
                    </label>
                </div>
            <?endif; ?>
          <input type="hidden" name="order_id" value="<?=$o?>">
            <div class="form-line-submit">
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
      
                <input class="btn-yellow-fz-16 font-bold button" type="submit" name="save" value="Зарегистрироваться">
            </div>
        </form>
    <?endif;?>
</div>


<script type="text/javascript">
  setTimeout(function(){$('.success_message').fadeOut('fast')},4000);
</script>

<?if(isset($is_show_cpbutton) && $is_show_cpbutton):?>
    <script type="text/javascript" src="/extensions/callpassword/web/js/main.js"></script>
<?endif;?>
<?php
$reCaptcha = json_decode($this->settings['reCaptcha'], true);
if(isset($reCaptcha['enable']) && $reCaptcha['enable'] == 1){ ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $reCaptcha['reCaptchaSiteKey'] ?>"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('<?= $reCaptcha['reCaptchaSiteKey'] ?>', {action: 'submit'}).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    </script>
<?} ?>

<style>
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
