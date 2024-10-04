<?defined('BILLINGMASTER') or die; // Главный файл шаблона, отличие - нет диво с контентом
require_once ("{$this->layouts_path}/head.php");?>

<body class="<?=$this->view['body_class'];?>" id="page">
    <?require_once ("{$this->layouts_path}/header.php");
    require_once ("{$this->layouts_path}/main_menu.php");?>

    <div id="content" class="<?=$this->view['main_content_class'];?>">
        <?require_once($this->view['path']);?>
    </div>

    <?require_once ("{$this->layouts_path}/footer.php");

    if (isset($this->extension) && file_exists(ROOT."/extensions/{$this->extension}/layouts/frontend/tech-footer.php")) {
        require_once(ROOT."/extensions/{$this->extension}/layouts/frontend/tech-footer.php");
    } else {
        require_once ("{$this->layouts_path}/tech-footer.php");
    }?>
</body>
</html>
