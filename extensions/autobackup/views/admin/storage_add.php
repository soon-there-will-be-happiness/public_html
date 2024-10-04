<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');
/**
 * @var array $buckets
 * @var array $files
 *///y0_AgAAAABm3-ZhAAkHuAAAAADaQI4G7repFTIHSoGJl69PNyRk2MDHP1k
?>

<div class="main">
    <div class="top-wrap">
    <h1>Создать хранилище</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/autobackup/">Резервное копирование</a>
        </li>
        <li>Создать хранилище</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Создать хранилище</h3>
                    <p class="mt-0">для резервного копирования</p>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="addStorage" value="Создать" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/autobackup/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1" style="margin-bottom: 0;">
                    <h4>Основное</h4>
                </div>
                <div class="col-1-2">
                    <p class="width-100"><label>Название: </label><input type="text" name="name" placeholder="Название задания" required="required"></p>

                    <div class="width-100">
                        <label>Тип хранилища</label>
                        <div class="select-wrap">
                            <select name="type">
                                <?php foreach (adminBackupController::BUCKETS_LIST as $key => $bucket) {?>
                                    <option value="<?= $key ?>" data-show_on="bucket<?= $key ?>"><?= $bucket ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>
                <div class="col-1-2"></div>

                <div class="col-1-1" style="margin-bottom: 0;">
                    <h4>Настройки хранилища</h4>
                </div>

                <div class="col-1-2" id="bucket0">
                    <p class="width-100">
                        <label>Сервер (ip)</label>
                        <input type="text" name="params[ftp_ip]" placeholder="Айпи ftp сервера">
                    </p>

                    <p class="width-100">
                        <label>Порт</label>
                        <input type="text" name="params[ftp_port]" placeholder="Порт ftp сервера" value="21">
                    </p>

                    <p class="width-100">
                        <label>Имя пользователя</label>
                        <input type="text" name="params[ftp_login]" placeholder="Логин от ftp">
                    </p>

                    <p class="width-100">
                        <label>Пароль</label>
                        <input type="text" name="params[ftp_pass]" placeholder="Пароль от ftp">
                    </p>

                    <p class="width-100">
                        <label>Путь</label>
                        <input type="text" name="params[ftp_path]" placeholder="Директория на сервере">
                    </p>

                </div>

                <div class="col-1-2" id="bucket1">
                    
                    <p class="width-100">
                        <label>YANDEX DISK CLIENTID</label>
                        <input type="text" name="params[yandex_client_id]" placeholder="" oninput="changeYandexDiskLink(event)">
                    </p>
					
					<p class="width-100">
                        <label>oAuth token
                            <span
                                class="result-item-icon" data-toggle="popover"
                                data-content="Его можно получить, перейдя по ссылке ниже, предварительно установив YANDEX DISK CLIENTID. Нужно будет войти в свой аккаунт яндекс. Откроется страница, с которой нужно будет скопировать токен"
                            >
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <input type="text" name="params[yandex_disk]" placeholder="">
                    </p>

                    <p class="width-100">
                        <a target="_blank"
                           id="yandexdisk_get_token"
                           href="https://oauth.yandex.ru/authorize?response_type=token&client_id="
                        >Получить токен</a>
                    </p>

                    <script>
                        function changeYandexDiskLink (event) {
                            let appKey = event.target.value;
                            document.getElementById("yandexdisk_get_token").href = "https://oauth.yandex.ru/authorize?response_type=token&client_id=" + appKey;
                        }
                    </script>
                </div>

                <div class="col-1-2" id="bucket2">
       
                    <p class="width-100">
                        <label>DROPBOX APP KEY</label>
                        <input type="text" name="params[dropbox_app_key]" placeholder="" oninput="changeDropboxLink(event)">
                    </p>
                    <p class="width-100">
                        <label>DROPBOX APP SECRET</label>
                        <input type="text" name="params[dropbox_app_secret]" placeholder="">
                    </p>
					
					<p class="width-100">
                        <label>Access token
                            <span
                                class="result-item-icon" data-toggle="popover"
                                data-content="Код доступа от dropbox. Получить его можно перейдя по ссылке ниже, предварительно установив APP KEY, APP SECRET. Откроется страница, с которой нужно будет скопировать код доступа. После первой отправки файла в dropbox, код замениться токеном">
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <input type="text" name="params[dropbox_token]" placeholder="">
                    </p>
                    <p class="width-100">
                        <a
                            target="_blank"
                            id="dropbox_get_token"
                            href="https://www.dropbox.com/oauth2/authorize?client_id=&token_access_type=offline&response_type=code"
                        >Получить токен</a>
                    </p>
                    <script>
                        function changeDropboxLink (event) {
                            let appKey = event.target.value;
                            document.getElementById("dropbox_get_token").href = "https://www.dropbox.com/oauth2/authorize?client_id=" + appKey + "&token_access_type=offline&response_type=code";
                        }
                    </script>
                </div>

                <div class="col-1-2" id="bucket3">
                    <div class="width-100">
                        <label>Выберите ип хранилища</label>
                        <div class="select-wrap">
                            <select name="params[s3_type]" id="s3SelectType">
                                <option value="0">Другое</option>
                                <option value="1">Яндекс облако</option>
                                <option value="2">Selectel</option>
                            </select>
                        </div>
                    </div>

                    <p class="width-100">
                        <label>Адрес (endpoint)<span class="result-item-icon" data-toggle="popover" data-content="Адрес типа: storage.yandexcloud.net, s3.storage.selcloud.ru(без https://)"><i class="icon-answer"></i></span></label>
                        <input type="text" name="params[s3_endpoint]" placeholder="" id="endpointS3">
                    </p>
                    <p class="width-100">
                        <label>Папка (bucket)</label>
                        <input type="text" name="params[s3_bucket]" placeholder="">
                    </p>
                    <p class="width-100">
                        <label>Идентификатор ключа</label>
                        <input type="text" name="params[s3_keyid]" placeholder="">
                    </p>
                    <p class="width-100">
                        <label>Cекретный ключ</label>
                        <input type="text" name="params[s3_secret]">
                    </p>
                </div>
                <div class="col-1-2" id="bucket4">
                    <p class="width-100">
                        <label>
                            Путь
                            <span class="result-item-icon" data-toggle="popover"
                                  data-content="Путь указывается относительно, от корневой директории School-master. Например, /backups/ - сохранит в папку <путь до school-master>/backups/">
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <input type="text" name="params[local_path]" placeholder="Путь от корня SM" value="<?= adminBackupController::DEFAULT_LOCAL_STORAGE_PATH ?>">
                    </p>
                </div>

                <div class="col-1-2" id="bucket5">
                    <p class="width-100">
                        <label>Client ID</label>
                        <input type="text" name="params[google_client_id]" oninput="changeGoogleClientID(event)">
                    </p>
                    <p class="width-100">
                        <label>Client secret</label>
                        <input type="text" name="params[google_client_secret]">
                    </p>
                    <p class="width-100">
                        <label>Redirect url</label>
                        <input type="text" name="params[google_redirect_url]" oninput="changeGoogleRedirectUrl(event)">
                    </p>
                    <p class="width-100">
                        <label>ID папки</label>
                        <input type="text" name="params[google_folder_id]">
                    </p>
                    <p class="width-100">
                        <label>
                            Код подтверждения
                            <a href="https://accounts.google.com/o/oauth2/auth"
                               target="_blank"
                               id="google_get_code_link"
                            >(Получить)</a>
                        </label>
                        <input type="text" name="params[google_confirm_code]">
                    </p>

                    <script>
                        let googleScope = "https://www.googleapis.com/auth/drive";
                        let googleClientID = "";
                        let googleRedirectUrl = "";

                        function changeGoogleClientID (event) {
                            googleClientID = event.target.value;
                            updateGoogleLink();
                        }
                        function changeGoogleRedirectUrl (event) {
                            googleRedirectUrl = event.target.value;
                            updateGoogleLink();
                        }

                        function updateGoogleLink() {
                            let url = `https://accounts.google.com/o/oauth2/auth?scope=${googleScope}&redirect_uri=${googleRedirectUrl}&response_type=code&client_id=${googleClientID}&access_type=offline`
                            document.getElementById("google_get_code_link").href = url;
                        }
                    </script>


                </div>

            </div>
        </div>
    </form>
</div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<script>
    let s3SelectType = document.getElementById("s3SelectType");
    let endpointS3 = document.getElementById('endpointS3');
    s3SelectType.addEventListener("input", function (e) {
        switch (e.target.value) {
            case "0"://Другое
                endpointS3.value = ''
                break;
            case "1"://Я.облако
                endpointS3.value = 'storage.yandexcloud.net'
                break;
            case "2"://Selectel
                endpointS3.value = 's3.storage.selcloud.ru'
                break;
        }
    });
</script>

</body>
</html>