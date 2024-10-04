<?defined('BILLINGMASTER') or die;

foreach($public_homeworks as $public_homework):
    require (__DIR__ .'/answers.php');
endforeach;

if($count_public_homeworks > $offset + $limit):?>
    <div class="btn-show-public-homeworks-wrap">
        <a class="btn-show-public-homeworks" href="javascript:void(0)" data-offset="<?=$offset+=$limit;?>" data-lesson_id="<?=$lesson['lesson_id'];?>" data-user_id="<?=$user_id;?>" data-count_homeworks="<?=$count_public_homeworks;?>">Показать следующие домашние работы</a>
    </div>
<?endif;?>