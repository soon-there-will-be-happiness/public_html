<?defined('BILLINGMASTER') or die;?>

<?require_once (ROOT . '/extensions/training/web/frontend/style/training-list.php');

if(isset($this->tr_settings['hero']) && $this->tr_settings['hero'] != null ):?>
    <div id="hero" class="hero-wrap  hero-text-center" style="background-image: url(<?=$this->tr_settings['hero'];?>)">
        <h1 class="layout hero_header h1"><span style="display:inline-block"><?=$this->tr_settings['heroheader'];?></span></h1>
    </div>
<?endif;?>

<div class="layout" id="courses">
    <div class="content-courses">
        <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
            <?if(!empty($h1)):?>
                <h1><?=$h1;?></h1>
            <?endif;?>

            <?if(!empty($h2) || (isset($this->tr_settings['show_section_button']) && $this->tr_settings['show_section_button'])):?>
                <div class="widget-top">
                    <?if(!empty($h2)):?>
                        <h2><?=$h2;?></h2>
                    <?endif;?>

                    <?if(isset($this->tr_settings['show_section_button']) && $this->tr_settings['show_section_button']):?>
                        <div class="z-1" style="text-align: right">
                            <a class="btn-yellow btn-orange" href="/training"><?=System::Lang('GO_TO_SECTION');?></a>
                        </div>
                    <?endif;?>
                </div>
            <?endif;

            $contentbody = $widget_arr = Widgets::RenderWidget($this->widgets, 'contentbody');
            if($contentbody):?>
                <div class="contentbody">
                    <?require("{$this->widgets_path}/widget_wrapper.php");?>
                </div>
            <?endif;

            require_once (__DIR__ . "/../training/templates/list/index.php")?>
        </div>
        <?require_once ("{$this->layouts_path}/sidebar.php");?>
    </div>
</div>