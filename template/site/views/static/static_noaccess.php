<?php defined('BILLINGMASTER') or die; 
require_once ("{$this->layouts_path}/head.php");?>

<body class="invert-page<?=$this->view['body_class'];?>" id="page">
    <?if($page['tmpl'] == 1):
        require_once ("{$this->layouts_path}/header.php");
        require_once ("{$this->layouts_path}/main_menu.php")?>

        <div id="content">
            <div class="layout" id="landing">
                <ul class="breadcrumbs">
                    <li><a href="/"><?=System::Lang('MAIN');?></a></li>
                    <li><?=$page['name'];?></li>
                </ul>

                <div class="content-wrap">
                    <div class="maincol<?php if($sidebar) echo '_min content-with-sidebar';?>">
                        <div class="maincol-inner">
                            <h1>К сожалению у вас нет доступа к этой странице</h1>
                            <p>У вас нет доступа к этой странице.</p>
                            <?php if(!$userId):?>
                                <p>Возможно вы ещё не <a href="#modal-login" data-uk-modal="{center:true}">авторизовались</a>.</p>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once ("{$this->layouts_path}/footer.php");
        require_once ("{$this->layouts_path}/tech-footer.php");
    else:
        echo $page['content'];
        require_once ("{$this->layouts_path}/tech-footer.php");
    endif;?>
</body>
</html>