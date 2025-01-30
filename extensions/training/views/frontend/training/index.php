<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/extensions/training/web/frontend/style/training.php');?>

<?if($training['cover_settings'] == 2):?>
    <div id="hero" class="hero-wrap" style="background-image: url(/images/training/<?=$training['full_cover']?>);background-color: #373A4C; <?= empty($training['full_cover']) ? "opacity: 75%;" : "" ?>">
        <h1><?=$training['name'];?></h1>
        <ul class="breadcrumbs hero-breadcrumbs">
           <!-- <?foreach($breadcrumbs as $link => $name):?>
                <li><=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>-->
        </ul>
    </div>
<?endif;?>

<div class="layout" id="courses">
    <?if($training['cover_settings'] != 2):?>
        <ul class="breadcrumbs">
            <?foreach($breadcrumbs as $link => $name):?>
                <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>
        </ul>
    <?endif;?>

    <div class="content-wrap" id="training_<?=$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar">
            <?if(empty($training['full_cover']) || $training['full_desc']):?>
                <div class="content-top one-course-top">
                    <?if(empty($training['full_cover']) && $training['cover_settings'] != 2):?>
                        <h1><?=$training['name'];?></h1>
                    <?endif;

                    if($training['full_desc']):?>
                        <div class="one-course-desk">
                            <?=html_entity_decode($training['full_desc']);?>
                        </div>
                    <?endif;?>
                </div>
            <?endif;

            if ($section_list) {
                require(__DIR__ . '/../section/list.php');
            }

            if ($block_list) {
                require(__DIR__ . '/../block/list.php');
            }

            if ($lesson_list) {
                require(__DIR__ . '/../lesson/list.php');
            }?>
        </div>

        <aside class="sidebar">
            <?if($training['cover'] && $training['cover_settings'] == 1 && $training['show_widget_progress']):?>
                <section class="widget _instruction traning-widget">
                    <div class="sidebar-image">
                        <img src="/images/training/<?=$training['cover']?>">
                    </div>

                    <h4 class="traninig-name"><?=$training['name']?></h4>

                    <?if($user_id) {?>
                        <p class="progress-text" style="margin-top: -10px !important;"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                    <?require_once (__DIR__ . '/../layouts/progressbar.php'); ?>
                    <?} else { ?>
                        <p><?=System::Lang('PROGRESS_OF_THE_TRAINING_WILL_BE_DISPLAYED_HERE');?></p>
                    <?} ?>
                </section>
            <?else:
                if($training['cover'] /*&& !$training['full_cover']*/ &&  $training['cover_settings'] == 1):?>
                    <section class="widget _instruction traning-widget">
                        <div class="sidebar-image">
                            <img src="/images/training/<?=$training['cover']?>">
                        </div>

                        <h4 class="traninig-name"><?=$training['name']?></h4>
                    </section>
                <?endif;

                if($user_id && $training['show_widget_progress']):?>
                    <section class="widget _instruction traning-widget">
                        <h3><?=System::Lang('YOUR_PROGRESS');?></h3>
                            <p class="progress-text"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                            <?require_once (__DIR__ . '/../layouts/progressbar.php'); ?>
                    </section>

                    <?elseif($training['show_widget_progress']):?>
                    <section class="widget traning-widget">
                        <p><?=System::Lang('PROGRESS_OF_THE_TRAINING_WILL_BE_DISPLAYED_HERE');?></p>
                    </section>
                <?endif;
            endif;?>
            
            <?if($have_certificate):?>
                <section class="widget traning-widget">
                    <?if(!empty($sertificate['header'])):?>
                        <h3 class="elephant-title"><i class="icon-sertifikat"></i><?=$sertificate['header'];?></h3>
                    <?endif;?>

                    <a target="_blank" href="<?=$this->settings['script_url'];?>/training/showcertificate/<?=$have_certificate['url'];?>">
                        <img src="<?=$this->settings['script_url'];?>/training/showcertificate/<?=$have_certificate['url'];?>" alt="">
                    </a>

                    <p class="text-center">
                        <a href="<?=$this->settings['script_url'];?>/training/showcertificate/<?=$have_certificate['url'];?>" class="btn-green" download>Скачать</a>
                    </p>
                </section>
            <?endif;

            if($user_is_curator):?>
                <section class="widget elephant-widget">
                    <h3 class="elephant-title"><i class="icon-elephant"></i><?=System::Lang('LOGIN_AS_CURATOR');?></h3>
                    <p><?=System::Lang('ALL_LESSONS_AVAILABLE');?></p>
                </section>
            <?endif;

            if($sidebar):
                $widget_arr = $sidebar;
                require (Template::getWidgetsPath($this->settings).'/widget_wrapper.php');
            endif;?>
        </aside>
    </div><hr>
</div>