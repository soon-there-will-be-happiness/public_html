<?defined('BILLINGMASTER') or die;

$template = $this->settings['template'];
$pos2big_headers = ['aftertext', 'aftertext2', 'bottom', 'footer'];
if ($is_auth) {
    $user_groups = User::getGroupByUser($is_auth);
    $user_groups = $user_groups ? $user_groups : [];
}

if($widget_arr):
    foreach ($widget_arr as $widget):
        $suffix = $widget['suffix'];
        $widget_params = unserialize($widget['params']);

        if ($widget['show_for_course'] != null && isset($course['course_id'])) {
            $show = unserialize(base64_decode($widget['show_for_course']));
            if (!in_array($course['course_id'], $show)) continue;
        }

        if ($widget['show_for_training'] != null && isset($training['training_id']) && in_array($this->view['is_page'], ['training', 'section', 'lesson'])) {
            $show_training = json_decode($widget['show_for_training'], true);
            if (!in_array($training['training_id'], $show_training)) {
                continue;
            }
        }

        //Проверка на группу
        if ($widget['showByGroup'] != 0 && $is_auth) {
            

            if ($widget['showGroups']) {
                $widget_groups = json_decode($widget['showGroups'], true);

                $find = false;
                foreach ($widget_groups as $widgetGroup) {
                    if (in_array($widgetGroup, $user_groups)) {
                        $find = true;
                    }
                }

                if ($widget['showByGroup'] == 1) { // Показывать определенным группам
                    if (!$find) {
                        continue;
                    }
                } else { // Показывать всем, кроме выбранных групп
                    if ($find) {
                        continue;
                    }
                }
            }



        }
        if(!$widget['private'] || $is_auth):?>
            <section class="widget<?=$suffix;?> col-<?=$widget['width'];?>">
                <?if($widget['show_header'] || $widget['show_subheader'] || $widget['show_right_button']):?>
                    <div class="widget-header-top">
                        <?if($widget['show_header']):?>
                            <div class="widget-header-box">
                                <?if(in_array($widget['position'], $pos2big_headers)):?>
                                    <h2 class="h2-widget-header"><?=$widget['header'];?></h2>
                                <?else:?>
                                    <h3 class="widget-header"><?=$widget['header'];?></h3>
                                <?endif;?>
                            </div>
                        <?endif;?>
                    </div>

                    <?if($widget['show_subheader'] || $widget['show_right_button']):?>
                        <div class="widget-with-button-box">
                            <?if($widget['show_subheader']):?>
                                <div class="widget-subheader-box">
                                    <?if(in_array($widget['position'], $pos2big_headers)):?>
                                        <h2 class="widget-subheader"><?=$widget['subheader'];?></h2>
                                    <?else:?>
                                        <h4 class="widget-subheader"><?=$widget['subheader'];?></h4>
                                    <?endif;?>
                                </div>
                            <?endif;

                            if($widget['show_right_button']):?>
                                <div class="z-1 widget-right-button-box">
                                    <a class="btn-yellow btn-orange widget-right-button" href="<?=$widget['right_button_link'];?>"><?=$widget['right_button_name'];?></a>
                                </div>
                            <?endif;?>
                        </div>
                    <?endif;
                endif;?>

                <div class="widget-inner">
                    <?require ("{$this->widgets_path}/{$widget['widget_type']}.php");?>
                </div>
            </section>
        <?endif;
    endforeach;
endif;?>