<?php 
session_start();
isset($_SESSION['admin_token'], $_GET[$_SESSION['admin_token']]) or die('rrr');

if(!isset($_POST['root'], $_POST['selected_paths'])
	|| !is_string($_POST['root'])
)
	exit('no_data');

define('FILESWALK', 1);

require_once 'main.php';


$root = $_POST['root'];
$use = is_array($_POST['selected_paths']) ? array_keys($_POST['selected_paths']) : [];

$path_list = FilesWalk::getFiles($root);

?>

<div class="get_folder" data-root="<?=$root?>">
    <?
    if($path_list):
        foreach($path_list as $path => $info):?>
            <div class="type type_<?=$info['type']?>" 
            	data-path="<?=str_replace('\\', '/', $path)?>" 
            	data-type="<?=$info['type']?>" 
            	data-time="<?=$info['time']??0?>"
            	data-size="<?=$info['size']??0?>"
            >
                <div class="line">
                    <div class="info">
                        <i class="icon <?=$info['type']?>"></i>
                        <div>
                            <p class="name">
                                <?=str_replace($root, '', $path) . ($info['type'] == 'folder' ? '\\' : '')?>
                            </p>
                        	<p class="details">
                            	<?=($info['type'] == 'folder' 
                                    ? 'Файлов: ' . $info['files'] . '  Папок: ' . $info['folders']
                                    : 'Последнее имзенение: ' . date('H:i d.m.Y', $info['time']) . '  Вес: ' . cоnvert($info['size'])
                                )?>
                        	</p>
                        </div>
                    </div>
                    <div class="status">
                    <?if($info['type'] == 'folder'):?>
                        <span class="title show" data-show="false">
                        </span>
                    <?else:?>
                        <span class="title <?=isset($use[$path]) ? 'del der' : 'add'?>" data-show="false">
                        </span>
                    <?endif;?>
                    </div>
                </div>
				<!--  -->
            </div>
        <? endforeach;
    endif;?>
</div>
		
<?php
function cоnvert($bytes){
	if($bytes < 1024)
		return $bytes . 'байт';

	if($bytes < 1048576)
		return round($bytes/1024, 2) . 'кб';

	if($bytes < 1073741824)
		return round($bytes/1048576, 2) . 'мб';

	return round($bytes/1073741824, 2) . 'гб';
}
?>