<?defined('BILLINGMASTER') or die;?>

<?$full_slide = $widget_arr = Widgets::RenderWidget($this->widgets, 'full_slide');
if($full_slide):?>
    <div class="full_layout">
        <?require("{$this->widgets_path}/widget_wrapper.php");?>
    </div>
<?endif;

$top = Widgets::RenderWidget($this->widgets, 'top');
$widget_arr = $top;
if($top):?>
    <div class="layout">
        <?require("{$this->widgets_path}/widget_wrapper.php");?>
    </div>
<?endif;

$slider = Widgets::RenderWidget($this->widgets, 'slider');
$widget_arr = $slider;
if($slider):?>
    <div class="user-menu">
        <div class="layout">
            <?require("{$this->widgets_path}/widget_wrapper.php");?>
        </div>
    </div>
<?endif;?>