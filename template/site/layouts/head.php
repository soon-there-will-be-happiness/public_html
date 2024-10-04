<?php defined('BILLINGMASTER') or die;
$is_auth = User::isAuth();
$sidebar = Widgets::RenderWidget($this->widgets, 'sidebar'); // проверка виджетов в сайдбаре

header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-Frame-Options:sameorigin");
header("X-Permitted-Cross-Domain-Policies: none");
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header("X-Content-Type-Options: nosniff");
?>

<!DOCTYPE html>
<html lang="ru-ru" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?if(isset($this->view['noindex'])):?>
        <meta name="robots" content="noindex, nofollow"/>
    <?endif;?>
    <link href="<?=$this->settings['script_url'];?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <?if(isset($canonical)):?>
        <link rel="canonical" href="<?=$canonical;?>"/>
    <?endif;?>
    <meta name="description" content="<?=$this->seo['meta_desc'];?>" />
    <meta name="keywords" content="<?=$this->seo['meta_keys'];?>" />
    <?if(isset($this->view['use_css']) && $this->view['use_css']):?>
        <link rel="stylesheet" href="<?=$this->settings['script_url'];?>/template/<?=$this->settings['template'];?>/css/normalize.css?v=<?=CURR_VER;?>" type="text/css" />
        <link rel="stylesheet" href="<?=$this->settings['script_url'];?>/template/<?=$this->settings['template'];?>/css/style.css?v=<?=CURR_VER;?>" type="text/css" />
        <link rel="stylesheet" href="<?=$this->settings['script_url'];?>/template/<?=$this->settings['template'];?>/css/mobile.css?v=<?=CURR_VER;?>" type="text/css" />
    <?endif;

    if(isset($jquery_head)):?>
        <script src="<?=$this->settings['script_url'];?>/template/<?=$this->settings['template']?>/js/jquery-3.4.1.min.js"></script>
        <script src="https://widget.cloudpayments.ru/bundles/cloudpayments"></script>
        <script src="/template/<?=$this->settings['template'];?>/js/jquery-ui-1.12.1.min.js"></script>
    <?endif;?>

    <title><?=$this->seo['title'];?></title>

    <?if(isset($this->view['no_tmpl']) && $this->view['no_tmpl'] == 1):
        echo $this->view['in_head'];
    endif;?>

    <?if(isset($comments) && $comments == 1 && !empty($params['params']['commenthead'])) {
        echo $params['params']['commenthead'];
    }
    
    if (!empty($this->settings['counters_head'])) {
        echo $this->settings['counters_head'];
    }?>
	
	<?php $main_settings = System::getSettingMainpage();
	if(isset($main_settings['sidebar'])):?>
        <style>
            <?if ($main_settings['sidebar'] == 'left') {
                echo '.sidebar {order:-1}';
            }
            if ($main_settings['sidebar'] == 'right') {
                echo '.sidebar {order:2}';
            }?>
        </style>
	<?endif;?>
    <?php if(!isset($og_image)) $og_image = $this->settings['logotype'];?>
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?=$this->seo['title'];?>"/>
    <meta property="og:description" content="<?=$this->seo['meta_desc'];?>" />
    <meta property="og:image" content="<?=$this->settings['script_url'].$og_image;?>" />
    <meta property="og:image:type" content="image/png" />
	
	<?if(!empty($main_settings['custom_css'])):?>
        <style><?=$main_settings['custom_css'];?></style>
    <?endif;
    $fb_api = System::CheckExtensension('facebookapi', 1);
    if($fb_api):
        echo Facebook::getPixelCode();
    endif;?>
</head>
