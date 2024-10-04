<?defined('BILLINGMASTER') or die; // Главный файл шаблона

require_once ("{$this->layouts_path}/head.php");?>

<body class="<?=$this->view['body_class'];?>" id="page">
    <?require_once ("{$this->layouts_path}/header.php");
    require_once ("{$this->layouts_path}/main_menu.php");
    
    // Вывод CSS для HERO
    Template::showHeroCss($this->view['hero'], $this->params);?>

    <div id="content" class="<?=$this->view['main_content_class'];?>">
        
        <? // вывод блока HERO 
        Template::showHero($this->view['hero'], $this->params);?>

        <div class="layout" id="<?=$this->view['is_page'];?>">
            
            <? // вывод Breadcrumbs
            echo Template::showBreadcrumbs($this->view['breadcrumbs']);?>

            <div class="<?=$this->view['content_class'];?>">
                <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
                    <?require_once($this->view['path']);?>
                </div>

                <? // позиция sidebar
                require_once ("{$this->layouts_path}/sidebar.php");?>
            </div>

            <? // позиция aftertext
            require_once ("{$this->layouts_path}/aftertext.php");
    
            // позиция aftertext2
            require_once ("{$this->layouts_path}/aftertext2.php");
            ?>
        </div>
    </div>

    <?require_once ("{$this->layouts_path}/footer.php");

    if (isset($this->extension) && file_exists(ROOT."/extensions/{$this->extension}/layouts/frontend/tech-footer.php")) {
        require_once(ROOT."/extensions/{$this->extension}/layouts/frontend/tech-footer.php");
    } else {
        require_once ("{$this->layouts_path}/tech-footer.php");
    }?>
</body>
</html>