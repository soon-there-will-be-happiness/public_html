<?php defined('BILLINGMASTER') or die ?>
<!DOCTYPE html>
<html lang="ru-ru" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
<title><?=$this->seo['title'];?></title>
<meta name="description" content="<?=$this->seo['meta_desc'];?>" />
<meta name="keywords" content="<?=$this->seo['meta_keys'];?>" />
<?=$page['in_head'];?>
<?if(isset($comments) && $comments == 1) {
    if(!empty($params['params']['commenthead'])) echo $params['params']['commenthead'];
}?>
</head>

<body id="page">
    <?=System::renderContent($page['content']);?>
    <?php if(isset($page['custom_code'])) echo $page['custom_code'];
    require_once ("{$this->layouts_path}/tech-footer.php");?>
    <?=$page['in_body']?>
</body>
</html>