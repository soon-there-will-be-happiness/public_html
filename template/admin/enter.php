<?php defined('BILLINGMASTER') or die;
if (!empty($security_key)) {
    $check_security_key = isset($_GET['key']) ? $_GET['key'] : System::getCookie('sm_security_key');
    $checked = $check_security_key == $security_key ? true : false;
} else {
    $checked = true;
}

if($checked):?>
<!DOCTYPE html>
<html lang="ru-ru" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <title>Вход</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&display=swap&subset=cyrillic');
        body {
            background: #373A4C;
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            line-height: 1.2;
            display: flex;
            min-height: 100vh;
            margin: 0;
            padding: 10px;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        a{
            color: #0772A0;
            text-decoration-skip-ink: none;
            text-decoration: underline;
        }
        a:hover{
            text-decoration: none;
        }
        .modal-form-line {
            margin-bottom: 22px;
            line-height: 1;
        }
        .modal-form-line input{
            border: 1px solid #D8DAE7;
            box-sizing: border-box;
            border-radius: 10px;
            padding: 0 15px;
            height: 40px;
            color: #636363;
            font-size: 16px;
            width: 100%;
        }
        .modal-form-line input:focus{
            outline: none;
            box-shadow: none;
        }
        .modal-form-submit{
            margin: 32px 0 38px;
        }
        .modal-form-submit input{
            display: block;
            width: 100%;
            line-height: 1;
            border: 1px solid #FFCA10;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease 0s;
            background: #FFCA10;
            height: 40px;
            padding: 11px 20px 10px;
            color: #fff;
            font-size: 16px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.05em;
        }
        .modal-form-submit input:hover{
            background: transparent;
            color: #FFCA10;
        }
        .enter{
            background: #FFFFFF;
            border-radius: 10px;
            padding: 45px 20px 50px;
            width: 520px;
            max-width: 100%;
        }
        .enter form{
            max-width: 326px;
            margin-left: auto;
            margin-right: auto;
        }
        .enter .admin_warning {
            text-align: center;
            margin-bottom: 15px;
            color: #9F6000;
        }
        h2{
            text-align: center;
            font-size: 32px;
            font-weight: normal;
            margin: 0 0 25px;
        }
        .modal-form-forgot{
            text-align: right;
        }
        ::-webkit-input-placeholder {color:#636363; opacity: 1;}
        ::-moz-placeholder          {color:#636363; opacity: 1;}
        :-moz-placeholder           {color:#636363; opacity: 1;}
        :-ms-input-placeholder      {color:#636363; opacity: 1;}


        [placeholder]:focus::-webkit-input-placeholder {color:transparent;}
        [placeholder]:focus::-moz-placeholder          {color:transparent;}
        [placeholder]:focus:-moz-placeholder           {color:transparent;}
        [placeholder]:focus:-ms-input-placeholder      {color:transparent;}
        @media screen and (max-width: 767px){
            .enter{
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="enter">
        <form action="" method="POST" id="enter_form">
            <h2>Авторизация</h2>
            <span id="enter_form_span"></span>
            <?php if (AdminBase::hasError()) AdminBase::showError();?>
            <div class="modal-form-line">
                <input type="text" name="login" placeholder="Логин" required>
            </div>
            <div class="modal-form-line">
                <input type="password" name="pass" placeholder="Пароль" required>
            </div>
            <div class="modal-form-submit">
                <input type="submit" name="enter" value="ВОЙТИ">
            </div>
            <input type="hidden" name="_server_time" value="<?=time()?>">
            <input type="hidden" name="_actual_time">
            <input type="hidden" name="_difference_time">
        </form>
    </div>
</body>

<script type="text/javascript">console.log('!!!!');
    var _aTime = document.getElementsByName('_actual_time')[0];
    var _sTime = document.getElementsByName('_server_time')[0];
    var _dTime = document.getElementsByName('_difference_time')[0];

    if(_sTime && (_aTime || _dTime)){
        aTime = new Date().getTime() + '_';

        aTime = parseInt(aTime.substring(0, aTime.length - 4));

        if(_aTime)
            _aTime.value = aTime;
    
        if(_dTime){
            _dTime.value = Math.floor(
                ((aTime - parseInt(_sTime.value)) + 30) / 3600
            );
        }
    }
</script>

<script type="text/javascript">
    window.addEventListener("focus", function(){
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.open("GET", '/admin/sessioncheck', false);
        xmlHttp.send(null);

        data = JSON.parse(xmlHttp.responseText);

        if(data.status == 'success' && data.result.authorization)
            location.reload();
    });
</script>

</html>
<?php else:
	header("Location: ".$setting['script_url']."/");
	exit();
endif;?>