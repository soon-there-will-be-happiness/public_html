<?php defined('BILLINGMASTER') or die;

$contentbody = Widgets::RenderWidget($this->widgets, 'contentbody');
$widget_arr = $contentbody;
if($contentbody): ?>
    <div class="contentbody">
        <?php require("{$this->widgets_path}/widget_wrapper.php");?>
    </div>
<?php endif;
echo System::renderContent($this->main_settings['main_page_text']);