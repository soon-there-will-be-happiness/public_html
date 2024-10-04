<?defined('BILLINGMASTER') or die;
$subcat_hero = json_decode($sub_category['hero_params'], true);
$subcat_hero = isset($subcat_hero['enabled']) && $subcat_hero['enabled'] == 1 ? $subcat_hero : null;?>
<style>
    .hero-wrap{
        min-height: <?=$subcat_hero['heroheigh'] ?? $this->tr_settings['heroheigh'];?>px;
        height: <?=$subcat_hero['heroheigh'] ?? $this->tr_settings['heroheigh'];?>px;
        background-position: <?=$this->tr_settings['position']?>;
        background-size: cover;
    }
    .hero-wrap:before{
        opacity:<?= $subcat_hero['overlay'] ?? $this->tr_settings['overlay']?>;
        background:<?= $subcat_hero['overlaycolor'] ?? $this->tr_settings['overlaycolor']?>;
    }

    .hero_header.h1 {
        color: <?= $subcat_hero['color'] ?? $this->tr_settings['color']?>;
        font-size: <?= $subcat_hero['fontsize'] ?? $this->tr_settings['fontsize']?>px;
    }

    @media screen and (max-width: 640px),
    only screen and (max-device-width:640px) {
        .hero-wrap {
            height: <?=$subcat_hero['heromobileheigh'] ?? $this->tr_settings['heromobileheigh'];?>px;
            min-height: <?=$subcat_hero['heromobileheigh'] ?? $this->tr_settings['heromobileheigh'];?>px;
        }
        .hero_header.h1 {font-size: <?=$this->tr_settings['fontsize_mobile'];?>px}
    }
</style>

<?if(isset($this->tr_settings['hero']) && $this->tr_settings['hero'] != null ):?>
    <div id="hero" class="hero-wrap  hero-text-center" style="background-image: url(<?=$this->tr_settings['hero'];?>)">
        <h1 class="layout hero_header h1"><?=$this->tr_settings['heroheader'];?></h1>
    </div>
<?endif;?>

<div class="layout" id="courses">
    <ul class="breadcrumbs mb-0">
        <li><a href="/"><?=System::Lang('MAIN');?></a></li>
        <li><a href="/training/"><?=System::Lang('ONLINE_COURSES');?></a></li>
        <li><a href="/training/category/<?=$category['alias'];?>"><?=$category['name'];?></a></li>
        <li><?=$sub_category['name'];?></li>
    </ul>

    <div class="content-courses">
        <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
            <?if(!empty($h1)):?>
                <h1><?=$h1;?></h1>
            <?endif;?>

            <?if ($training_list) { // вывод тренингов
                require_once (__DIR__ . "/../../training/templates/list/{$this->tr_settings['template']}.php");
            }?>
        </div>

        <?require_once ("{$this->layouts_path}/sidebar.php");?>
    </div>
</div>