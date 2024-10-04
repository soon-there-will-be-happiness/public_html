<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');
/**
 * @var array $storage
 */
$params = $storage['params'];

?>

<div class="main">
    <div class="top-wrap">
    <h1>Редактировать хранилище</h1>
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
        <li>Редактировать хранилище</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Редактировать хранилище</h3>
                    <p class="mt-0">для резервного копирования</p>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="addStorage" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/autobackup/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1" style="margin-bottom: 0;">
                    <h4>Основное</h4>
                </div>
                <div class="col-1-2">
                    <p class="width-100"><label>Название: </label><input type="text" name="name" placeholder="Название задания" required="required" value="<?=$storage['title'] ?>"></p>
                    <div class="width-100"><label>Статус</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="status" type="radio" value="1" <?php if($storage['status'] == 1) echo 'checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="status" type="radio" value="0" <?php if($storage['status'] == 0) echo 'checked';?>><span>Откл</span></label>
                        </span>
                    </div>
                    <div class="width-100">
                        <label>Тип хранилища</label>
                        <div class="select-wrap">
                            <select name="type">
                                <?php foreach (adminBackupController::BUCKETS_LIST as $key => $bucket) {?>
                                    <option
                                        value="<?= $key ?>"
                                        data-show_on="bucket<?= $key ?>"
                                        <?= $storage['type'] == $key ? "selected" : "" ?>
                                    >
                                        <?= $bucket ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>

                <div class="col-1-1" style="margin-bottom: 0;">
                    <h4>Настройки хранилища</h4>
                </div>

                <div class="col-1-2" id="bucket0">
                    <p class="width-100">
                        <label>Сервер (ip)</label>
                        <input type="text" name="params[ftp_ip]" placeholder="Айпи ftp сервера" value="<?= $params['ftp_ip'] ?>">
                    </p>

                    <p class="width-100">
                        <label>Порт</label>
                        <input type="text" name="params[ftp_port]" placeholder="Порт ftp сервера" value="<?= $params['ftp_port'] ?>">
                    </p>

                    <p class="width-100">
                        <label>Имя пользователя</label>
                        <input type="text" name="params[ftp_login]" placeholder="Логин от ftp" value="<?= $params['ftp_login'] ?>">
                    </p>

                    <p class="width-100">
                        <label>Пароль</label>
                        <input type="text" name="params[ftp_pass]" placeholder="Пароль от ftp" value="<?= $params['ftp_pass'] ?>">
                    </p>

                    <p class="width-100">
                        <label>Путь</label>
                        <input type="text" name="params[ftp_path]" placeholder="Директория на сервере" value="<?= $params['ftp_path'] ?>">
                    </p>

                </div>

                <div class="col-1-2" id="bucket1">
                    
                    <p class="width-100">
                        <label>YANDEX DISK CLIENTID</label>
                        <input type="text" name="params[yandex_client_id]" oninput="changeYandexDiskLink(event)" value="<?= $params['yandex_client_id'] ?? "" ?>">
                    </p>
                    
                    <p class="width-100">
                        <label>oAuth token
                            <span
                                class="result-item-icon" data-toggle="popover"
                                data-content="Его можно получить, перейдя по ссылке ниже, предварительно установив YANDEX DISK CLIENTID. Нужно будет войти в свой аккаунт яндекс. Откроется страница, с которой нужно будет скопировать токен">
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <input type="text" name="params[yandex_disk]" value="<?= $params['yandex_disk'] ?>">
                    </p>

                    <p class="width-100">
                        <a target="_blank"
                           id="yandexdisk_get_token"
                           href="https://oauth.yandex.ru/authorize?response_type=token&client_id=<?= @$params['yandex_client_id'] ?? "" ?>"
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
                        <input type="text" name="params[dropbox_app_key]" oninput="changeDropboxLink(event)" value="<?= $params['dropbox_app_key'] ?? "" ?>">
                    </p>
                    <p class="width-100">
                        <label>DROPBOX APP SECRET</label>
                        <input type="text" name="params[dropbox_app_secret]" value="<?= $params['dropbox_app_secret'] ?? "" ?>">
                    </p>
                    
                    <p class="width-100">
                        <label>Access token:
                            <span
                                class="result-item-icon" data-toggle="popover"
                                data-content="Код доступа от dropbox. Получить его можно перейдя по ссылке ниже. Откроется страница, с которой нужно будет скопировать код доступа. После первой отправки файла в dropbox, код замениться токеном">
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <input type="text" name="params[dropbox_token]" placeholder="" value="<?= $params['dropbox_token'] ?>">
                        <input type="hidden" name="params[dropbox_refresh_token]" value="<?=@$params['dropbox_refresh_token'] ?>">
                    </p>

                    <p class="width-100">
                        <a  target="_blank"
                            id="dropbox_get_token"
                            href="https://www.dropbox.com/oauth2/authorize?client_id=<?= $params['dropbox_app_key'] ?? "" ?>&token_access_type=offline&response_type=code"
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
                                <option value="0" <?= $params['s3_type'] == 0 ? " selected" : "" ?>>Другое</option>
                                <option value="1" <?= $params['s3_type'] == 1 ? " selected" : "" ?>>Яндекс облако</option>
                                <option value="2" <?= $params['s3_type'] == 2 ? " selected" : "" ?>>Selectel</option>
                            </select>
                        </div>
                    </div>

                    <p class="width-100">
                        <label>Адрес (endpoint)<span class="result-item-icon" data-toggle="popover" data-content="Адрес типа: storage.yandexcloud.net, s3.storage.selcloud.ru(без https://)"><i class="icon-answer"></i></span></label>
                        <input type="text" name="params[s3_endpoint]" placeholder="" value="<?= $params['s3_endpoint'] ?>" id="endpointS3">
                    </p>
                    <p class="width-100">
                        <label>Папка (bucket)</label>
                        <input type="text" name="params[s3_bucket]" placeholder="" value="<?= $params['s3_bucket'] ?>">
                    </p>
                    <p class="width-100">
                        <label>Идентификатор ключа</label>
                        <input type="text" name="params[s3_keyid]" placeholder="" value="<?= $params['s3_keyid'] ?>">
                    </p>
                    <p class="width-100">
                        <label>Cекретный ключ</label>
                        <input type="text" name="params[s3_secret]" value="<?= $params['s3_secret'] ?>">
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
                        <input type="text" name="params[local_path]" placeholder="Путь от корня SM" value="<?= $params['local_path'] ?? "" ?>">
                    </p>
                </div>

                <div class="col-1-2" id="bucket5">
                    <?php if ($storage['type'] == 5) { ?>
                        <?php if (!isset($params['google']) || $params['google'] === false) { ?>
                            <p>
                                <b>Токен авторизации в google не получен!</b><br>
                                Заполните настройки заново и сохраните.<br>
                                Если это сообщение не пропало, то <a href="/admin/logs?type=autobackup">проверьте логи</a>
                            </p>
                        <?php } ?>
                    <?php } ?>
                    <p class="width-100">
                        <label>Client ID</label>
                        <input type="text" name="params[google_client_id]" oninput="changeGoogleClientID(event)" value="<?= $params['google_client_id'] ?? "" ?>">
                    </p>
                    <p class="width-100">
                        <label>Client secret</label>
                        <input type="text" name="params[google_client_secret]" value="<?= $params['google_client_secret'] ?>">
                    </p>
                    <p class="width-100">
                        <label>Redirect url</label>
                        <input type="text" name="params[google_redirect_url]" oninput="changeGoogleRedirectUrl(event)" value="<?= $params['google_redirect_url'] ?? "" ?>">
                    </p>
                    <p class="width-100">
                        <label>ID папки</label>
                        <input type="text" name="params[google_folder_id]" value="<?= $params['google_folder_id'] ?? "" ?>">
                    </p>
                    <p class="width-100">
                        <label>
                            Код подтверждения
                            <a href="https://accounts.google.com/o/oauth2/auth"
                               target="_blank"
                               id="google_get_code_link"
                               onclick="updateGoogleLink()"
                            >(Получить)</a>
                        </label>
                        <input type="text" name="params[google_confirm_code]" value="<?= $params['google_confirm_code'] ?? "" ?>">
                    </p>

                    <input type="hidden" name="params[google][access_token]" value="<?= $params['google']['access_token'] ?? "" ?>">
                    <input type="hidden" name="params[google][expires_in]" value="<?= $params['google']['expires_in'] ?? "" ?>">
                    <input type="hidden" name="params[google][refresh_token]" value="<?= $params['google']['refresh_token']  ?? ""?>">
                    <input type="hidden" name="params[google][scope]" value="<?= $params['google']['scope'] ?? "" ?>">
                    <input type="hidden" name="params[google][token_type]" value="<?= $params['google']['token_type'] ?? "" ?>">

                    <script>
                        let googleScope = "https://www.googleapis.com/auth/drive";
                        let googleClientID = "<?= $params['google_client_id'] ?? "" ?>";
                        let googleRedirectUrl = "<?= $params['google_redirect_url'] ?? "" ?>";

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
    
    <p class="button-delete" style="margin-bottom: 24px;">
        <a onclick="return confirm('<?=System::Lang('YOU_SHURE');?>?')" href="/admin/autobackup/storage/remove/<?=$storage['id'];?>?token=<?=$_SESSION['admin_token'];?>" title="<?=System::Lang('DELETE');?>">
            <i class="icon-remove"></i><?=System::Lang('DELETE_STORAGE');?>
        </a>
    </p>
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