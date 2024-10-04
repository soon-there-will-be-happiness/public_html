<?defined('BILLINGMASTER') or die;
$catHero = json_decode($category['hero_params'], true); $catHero = isset($catHero['enabled']) && $catHero['enabled'] == 1 ? $catHero : null; ?>

<style>
    .hero-wrap{
        min-height: <?= $catHero['heroheigh'] ?? $this->tr_settings['heroheigh'];?>px;
        height: <?= $catHero['heroheigh'] ?? $this->tr_settings['heroheigh'];?>px;
        background-position: <?= $catHero['position'] ??  $this->tr_settings['position']?>;
        background-size: cover;
    }
    .hero-wrap:before{
        opacity:<?= $catHero['overlay'] ?? $this->tr_settings['overlay']?>;
        background:<?= $catHero['overlaycolor'] ?? $this->tr_settings['overlaycolor']?>;
    }

    .hero_header.h1 {color: <?= $catHero['color'] ?? $this->tr_settings['color']?>; font-size: <?= $catHero['fontsize'] ?? $this->tr_settings['fontsize']?>px; }

    @media screen and (max-width: 640px),
    only screen and (max-device-width:640px) {
        .hero-wrap {
            height: <?= $catHero['heromobileheigh'] ?? $this->tr_settings['heromobileheigh'];?>px;
            min-height: <?=$catHero['heromobileheigh'] ?? $this->tr_settings['heromobileheigh'];?>px;
        }
        .hero_header.h1 {font-size: <?= $catHero['fontsize_mobile'] ??  $this->tr_settings['fontsize_mobile'];?>px}
    }
</style>
<?if(isset($this->tr_settings['hero']) && $this->tr_settings['hero'] != null && (!isset($catHero['status']) || $catHero['status'])):?>
    <div id="hero" class="hero-wrap hero-text-center" style="background-image: url('<?= $catHero['img'] ?? $this->tr_settings['hero'];?>')">
        <h1 class="layout hero_header h1"><?= $catHero['heroheader'] ?? $this->tr_settings['heroheader'];?></h1>
    </div>
<?endif;?>

<div class="layout" id="courses">
    <?if(!empty($h1)):?>
        <h1><?=$h1;?></h1>
    <?endif;?>

    <?php if ($category['breadcrumbs_status'] == 1) { ?>
        <ul class="breadcrumbs mb-0">
            <li><a href="/"><?=System::Lang('MAIN');?></a></li>
            <li><a href="/training/"><?=System::Lang('ONLINE_COURSES');?></a></li>
            <li><?=$category['name'];?></li>
        </ul>
    <?php } ?>

    <div class="content-courses">
        <div class="maincol<?if($sidebar) echo '_min content-with-sidebar';?>">
            <?if ($subcategory_list) { // вывод подкатегорий
                require_once(__DIR__ . "/subcategory/templates/list/{$this->tr_settings['template']}.php");
            }  else { // вывод тренингов
                require_once (__DIR__ . "/../training/templates/list/{$this->tr_settings['template']}.php");
            }?>
        </div>

        <?require_once ("{$this->layouts_path}/sidebar.php");?>
    </div>
</div>