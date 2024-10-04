<?php defined('BILLINGMASTER') or die; 

$setting = System::getSetting();
$_SESSION['MAX_UPLOADS_FILES'] = $setting['max_upload'];

?>

<!DOCTYPE html>
<html lang="ru-ru" dir="ltr">
<head>
  <?php 
  if(isset($_GET['full']))
    $_SESSION['full_width'] = 1;

  if(isset($_GET['reset'])) 
    $_SESSION['full_width'] = false;

  if(@ $_SESSION['full_width'] == 1): ?>
  <style>
    #page {
      width:100%!important; 
      max-width: 100%!important;
    } 
    html #page {
      padding-left: 0px !important; 
      padding-right: 0px !important;
    }
    .off {
      background:#ffebeb!important;
    }
  </style>
  <? endif;?>

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link href="/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
  <title><? echo isset($title) ? $title : 'Админка'?></title>

  <link rel="stylesheet" href="<?php echo $setting['script_url'];?>/template/admin/css/jquery.formstyler.css">
  <link rel="stylesheet" href="<?php echo $setting['script_url'];?>/template/admin/css/jquery.dataTables.css">

  <link rel="stylesheet" href="<?php echo $setting['script_url'];?>/template/admin/css/admin.css?v=<?=CURR_VER;?>" type="text/css" />
  <link href='https://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css' />
  <!--style>.row_table.data  a > img {opacity: 0.0} .row_table.data:hover a > img {opacity:0.9}</style-->

  <script type="text/javascript">var admin_token = '<?=$_SESSION["admin_token"]?>';</script>
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

  <script src="<?php echo $setting['script_url'];?>/template/admin/js/tabs.js?v=<?=CURR_VER;?>" type="text/javascript"></script>
  <script src="<?php echo $setting['script_url'];?>/template/admin/js/navAccordion.js"></script>
  <script src="<?php echo $setting['script_url'];?>/lib/tinymce/tinymce.min.js"></script>
  <script src="<?php echo $setting['script_url'];?>/template/admin/js/jquery.formstyler.min.js" type="text/javascript"></script>
  <script src="<?php echo $setting['script_url'];?>/template/admin/js/jquery.dataTables.min.js" type="text/javascript"></script>

  <script>
    var editor_init = function() {
      tinymce.init({
        selector: 'textarea.editor',
        language: 'ru',
        plugins: [
          'advlist autolink lists link image charmap print preview anchor',
          'searchreplace visualblocks code fullscreen emoticons',
          'insertdatetime media table paste code wordcount'
        ],
        toolbar: 'insert | undo redo |  code | formatselect | bold italic backcolor forecolor  emoticons | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | bullist numlist',
        image_dimensions: false,
        image_advtab: true,
        convert_urls: false,
        height: 400,
        external_filemanager_path: "/lib/file_man/filemanager/",
        filemanager_title: "Responsive Filemanager",
        external_plugins: {"filemanager": "<?php echo $setting['script_url'];?>/lib/file_man/filemanager/plugin.min.js"},
        relative_urls: false,
        remove_script_host : false,
        deprecation_warnings: false,
        setup: function (editor) {
          editor.on('change', function (e) {
            editor.save();
          });
        }
      });
      tinymce.init({
        selector: 'textarea.editorsmall',
        language: 'ru',
        height: 200,
        plugins: [
          'code'
        ],
        remove_script_host : false,
        relative_urls: false,
        convert_urls: false,
        deprecation_warnings: false,
      });
    };
    editor_init();
  </script>

  <script src="<?php echo $setting['script_url'];?>/template/admin/js/scripts.js?v=<?=CURR_VER;?>" type="text/javascript"></script>
  <script src="<?php echo $setting['script_url'];?>/lib/FilesWalk/web/main.js?v=<?=CURR_VER;?>" type="text/javascript"></script>
  <link rel="stylesheet" href="<?php echo $setting['script_url'];?>/lib/FilesWalk/web/main.css">

</head>