<?defined('BILLINGMASTER') or die;

require_once (ROOT . '/extensions/training/web/frontend/style/section.php');
if($training['cover_settings'] == 2):?><!-- большая картинка с оверлеем если есть  -->
<div id="hero" class="hero-wrap" style="background-image: url(/images/training/<?=$training['full_cover']?>);background-color: #373A4C; <?= empty($training['full_cover']) ? "opacity: 75%;" : "" ?>">
        <h1><?=$section['name'];?></h1>
        <ul class="breadcrumbs hero-breadcrumbs">
           <!-- <?$breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section);
            foreach ($breadcrumbs as $link => $name):?>
                <li><=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>-->

                 <!-- <?foreach($breadcrumbs as $link => $name):?>
                <li><=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
                <?endforeach;?>-->
        </ul>
    </div>
<?endif;?>

<div class="layout" id="courses">
    <?if($training['cover_settings'] != 2):?><!-- А тут нет большой обложки(картинки) -->
        <ul class="breadcrumbs">
            <?$breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section);
            foreach($breadcrumbs as $link => $name):?>
                <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>
        </ul>
    <?endif;?>

    <div class="content-wrap" id="training_<?=$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar">
            <div class="content-top">
                <span class="z-1"><a class="btn-orange" href="/feedback"><?=System::Lang('WRITE_REVIEW');?></a></span>
            </div>

            <div class="one-course-top">
                <?if($training['cover_settings'] != 2):?>
                    <h1><?=$section['name'];?></h1>
                <?endif;?>

                <div class="section-desc">
                    <?=html_entity_decode($section['section_desc']);?>
                </div>
            </div>

            <?if($block_list) {
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

                    <?if ($user_id) {?>
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
            endif;

            if($user_is_curator):?>
                <section class="widget elephant-widget">
                    <h3 class="elephant-title"><i class="icon-elephant"></i><?=System::Lang('LOGIN_AS_CURATOR');?></h3>
                    <p><?=System::Lang('ALL_LESSONS_AVAILABLE');?></p>
                </section>
            <?endif;

            if($sidebar):
                $widget_arr = $sidebar;
                require ("$this->widgets_path/widget_wrapper.php");
            endif;?>
        </aside>
    </div>
</div>