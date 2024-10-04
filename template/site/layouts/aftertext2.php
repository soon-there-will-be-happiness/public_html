<?defined('BILLINGMASTER') or die;
$aftertext2 = Widgets::RenderWidget($this->widgets, 'aftertext2'); // проверка виджетов в позиции aftertext

if(isset($aftertext2) && $widget_arr = $aftertext2): ?>
    <div class="aftertext">
        <?require("{$this->widgets_path}/widget_wrapper.php");?>
    </div>
<?endif;?>
