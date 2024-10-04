<?php defined('BILLINGMASTER') or die;

require_once ("{$this->layouts_path}/head.php");?>

<body id="page">
    <?php require_once ("{$this->layouts_path}/header.php");
    require_once ("{$this->layouts_path}/main_menu.php")?>
    
    <div id="content">
        <div class="layout" id="courses">
            <ul class="breadcrumbs">
                <?php $breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section, $lesson);
                foreach ($breadcrumbs as $link => $name):?>
                    <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
                <?php endforeach;?>
            </ul>

            <div class="content-wrap">
                <div class="maincol<?php if($sidebar) echo '_min';?> content-with-sidebar">
                    <h1><?=$training['name'];?> : <?=$lesson['name'];?></h1>
                    <h3>К сожалению у вас пока нет доступа к этому уроку</h3>
                    <p>Для получения доступа <a href="/lk/#cp_confirm" target="_blank">подтвердите свой телефон</a></p>
                </div>

                <?php require_once ("{$this->layouts_path}/sidebar.php");?>
            </div>
        </div>
    </div>

    <?php require_once ("{$this->layouts_path}/footer.php");
    require_once ("{$this->layouts_path}/tech-footer.php")?>
</body>
</html>
<?php exit;?>