<?if($sidebar):
$widget_arr = $sidebar;?>
<aside class="sidebar">

<?
// Здесь делаем имитацию виджета корзины при включенной корзине, что-бы не создавать специально такой виджет
// но и не ломать общую концепцию вывода этого блока.
if($this->settings['use_cart'] == 1 && $this->view['is_page'] == 'catalog'):
    $cart_widget['widget_type'] = 'cart';
    $cart_widget['position'] = 'sidebar';
    $cart_widget['satus'] = 1;
    $cart_widget['suffix'] = '';
    $cart_widget['show_header'] = '';
    $cart_widget['show_subheader'] = '';
    $cart_widget['show_right_button'] = '';
    $cart_widget['private'] = 0;
    $cart_widget['show_for_training'] = null;
    $cart_widget['show_for_course'] = null;
    $cart_widget['params'] = '';
    $cart_widget['width'] = 0;
    array_unshift($widget_arr, $cart_widget);
endif;

require ("$this->widgets_path/widget_wrapper.php");?>
</aside>
<?php endif;?>