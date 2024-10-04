<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');
$type = 'расширение';
if(isset($_GET['type']) && $_GET['type'] == 'template') $type = 'шаблон';?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Расширения</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/settings/">Настройки</a></li>
        <li>Расширения</li>
    </ul>

    <div class="admin_form">
        <form action="" method="POST" enctype="multipart/form-data">
            <ul>
                <li class="search-row">
                    <span class="search-row mr-auto">Установить <?=$type;?> (макс. <?=System::getPostMaxSize('mb');?> Мб)
                        <input type="file" name="extens" value="Выбрать">
                    </span>
                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    <input type="submit" class="button save button-green-rounding" name="install_ext" value="Установить">
                </li>
            </ul>
        </form>
        <ul>
            <li class="search-row">
                <label class="custom-chekbox-wrap">
                    <input type="checkbox" data-show_off="extensions_off">
                    <span class="custom-chekbox"></span>Показать только активные расширения
                </label>
            </li>
        </ul>
    </div>
    
    
    <?if(System::hasSuccess()) System::showSuccess();?>
    <?if(System::hasError()) System::showError();?>

    <div class="extension">
        <?if(isset($exts) && !empty($exts)):
            foreach($exts as $ext):?>
            
            <div class="extension-item" data-id="extensions_<?=$ext['enable'] == 1 ? "on" : "off"?>" data-ext_name="<?=$ext['name'];?>">
                <div class="extension-img">
                    <img src="/template/admin/images/ext/<?=$ext['name'];?>.svg">
                </div>

                <div class="extension-center">
                    <h4>
                        <?if($ext['type'] != 'template'):?>
                            <a href="<?=$setting['script_url'];?>/admin/<?=$ext['link'];?>"><?=System::Lang($ext['title']);?></a>
                        <?else:?>
                            <span><?=System::Lang($ext['title']);?></span>
                        <?endif;?>
                    </h4>

                    <?if($ext['menu']):
                        $links = unserialize(base64_decode($ext['menu']));
                        foreach($links as $link):?>
                            <a class="ext_menu_link" href="<?=$link['link'];?>"><?=$link['title'];?></a>
                        <?endforeach;
                    endif;?>
                </div>
                
                <?php if($type != 'шаблон'):?>
                <div class="extension-status">
                    <?$status = $ext['enable'] == 1 ? 'on' : 'off';?>
                    <a class="ext-status <?=$status;?>" style="text-decoration: none" href="/admin/extensions/changestatus/<?= $ext['id']?>?<?= $ext['enable'] ? "?status=0" : "status=1"?>&token=<?=$_SESSION['admin_token'];?>"></a>
                </div>
                <?php endif;?>
            </div>
            <?endforeach;
        endif;?>
    </div>
    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>