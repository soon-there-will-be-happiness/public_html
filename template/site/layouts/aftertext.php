<?defined('BILLINGMASTER') or die;
$aftertext = Widgets::RenderWidget($this->widgets, 'aftertext'); // проверка виджетов в позиции aftertext

if(isset($aftertext) && $widget_arr = $aftertext): ?>
    <div class="aftertext">
        <?require("{$this->widgets_path}/widget_wrapper.php");?>
    </div>
<?endif;?>
