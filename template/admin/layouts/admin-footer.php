<?php defined('BILLINGMASTER') or die; 

if(!isset($acl) && $acl = AdminBase::checkAdmin());?>

    <div class="footer">
        <p><?php //echo System::Lang('COPYRIGHT');?></p>
        <div id="site_update_options" class="uk-modal">
            <div class="uk-modal-dialog" style="padding: 25px 30px;">
                <div class="userbox  modal-userbox-2">
                    <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
                    <div>
                        <h3 class="modal-head mb-20">Обновление School-master</h3>
                        <p class="font-16 text-center">Перед обновлением сделайте резервное копирование</p>
                        <div class="site-update-options">
                            <div class="reference-link updform">
                                <?php if (System::CheckExtensension('autobackup', 1)) { ?>
                                    <?php
                                    if (class_exists("BackupCronHandler")) {
                                        $extSettings = BackupCronHandler::getExtSettings();
                                    } else {
                                        $extSettings['smart_backup_update'] = 0;
                                    }
                                    if (@$extSettings['smart_backup_update'] == 1) { ?>
                                        <a class="on-site-backup button button-red-rounding" href="/admin/autobackup/startsmartbackup">Сделать резервную копию</a>
                                    <?php } else { ?>
                                        <a class="on-site-backup button button-red-rounding" href="/admin/backup/">Сделать резервную копию</a>
                                    <?php } ?>

                                <?php } else { ?>
                                    <a class="on-site-backup button button-red-rounding" href="/admin/backup/">Сделать резервную копию</a>
                                <?php } ?>
                            </div>
                            <div class="reference-link updform">
                                <a class="on-site-update button button-green-rounding">Обновить систему</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .site-update-options {
                justify-content: space-between;
            }
            .updform.reference-link {
                justify-content: center;
            }
            .updform .button {
                height: 30px; width: 250px; justify-content: center;
                font-size: 14px;
            }
        </style>

        <div id="loading-indicator"></div>
    </div>

<script type="text/javascript">
    var hideAdminMessage = function () {
      setTimeout(function(){
        if(!$('.admin_warning').is('[data-ntf_id]'))
          $('.admin_warning').fadeOut('fast');

        if(!$('.admin_message').is('[data-ntf_id]'))
          $('.admin_message').fadeOut('fast');
      },5000);
    };
    hideAdminMessage();
</script>

<script src="/template/admin/js/jquery-ui-1.12.1.min.js"></script>
<script src="/template/admin/js/jquery.ui.touch-punch-0.2.3.min.js"></script>
<!-- <script src="/template/admin/js/jquery.cookie.js"></script> -->
<script src="/template/admin/js/notification.js?v=3.8.0"></script>
<script src="/template/admin/js/session.js?v=3.8.0"></script>

<script src="<?php echo $setting['script_url'];?>/template/admin/js/libs.js" type="text/javascript"></script>

<script src="/lib/select2/js/select2.js"></script>
<link href="/lib/select2/css/select2.css" rel="stylesheet" type="text/css" />
<script>
  $(function() {
    UIkit.modal("").defaults.bgclose = false;

    let $course_list = $('.course-list').length > 0 ? $('.course-list') : $('.cource-list');
    let sort_upd_url = $course_list.children('input[name="sort_upd_url"]').val();

    if ($course_list.length > 0 && typeof(sort_upd_url) != 'undefined' && $course_list.find('input[name="sort[]"]').length > 1) {
      $course_list.sortable({
        cursor: "move",
        handle: ".button-drag",
        stop: function() {
          $.ajax({
            url: sort_upd_url,
            method: 'post',
            dataType: 'json',
            data: $course_list.find('input[name="sort[]"]').serialize(),
            success: function(resp) {
              if(!resp.status) {
                alert('Произошла ошибка при сохранении данных, обратитесь к разработчику')
                console.log(resp.error);
              }
            },
            error: function(err) {
              alert("Произошла ошибка при сохранении данных, обратитесь к разработчику");
              console.log(err);
            }
          });
          $('.numbering').each(function(i) {
            $(this).val(i + 1);
          });
        }
      });
    }
  });
</script>

<?php if (System::isAvailblNewVrsn() && isset($acl['update_sm'])):
    if(isset($_SESSION['status']) && ($_SESSION['status'] == 'noupdate' || $_SESSION['status'] == 'stop')) {
        $str = '<div class="site-noupdate"><a target="_blank" href="https://lk.school-master.ru/buy/29">Вышла новая версия School-master '.$_SESSION['actual_ver'].' Продлить доступ к обновлениям</a></div>';
    } else {
        $str = '<div class="site-update"><a href="#site_update_options" data-uk-modal="{center:true}">Вышла новая версия School-master '.$_SESSION['actual_ver'].' Обновитесь</a></div>';
    }?>

    <script>
      $(document).ready(function(){
        let block = '<?=$str;?>';
        if ($('div.main').length > 0) {
          $('div.main .top-wrap').after(block);
        }
        $('.on-site-update').on('click', function() {
          UIkit.modal("#site_update_options").hide();
          $('#loading-indicator').show();

          $.ajax({
            url: '/admin/cmsupdate',
            method: 'post',
            dataType: 'html',
            data: {token: '<?php echo $_SESSION['admin_token'];?>'},
            success: function(html) {
              $('#loading-indicator').hide();
              if(html) {
                $('.site-update').replaceWith(html);
              }
            },
            error: function(err) {
              $('#loading-indicator').hide();
              alert("Произошла ошибка при обновлении, обратитесь к разработчику");
              console.log(err);
            }
          });
        });
      });
    </script>
<?php endif;?>

<?php if(isset($product['product_id']) && isset($product['type_id'])):?>
    <script>
      $(function() {
        $('a[data-prod_httpnotice_id]').click(function() {
          let notice_id = $(this).data('prod_httpnotice_id');

          $.ajax({
            url: "/admin/products/edithttpnotice/" + notice_id + "?prod_id",
            type: "GET",
            dataType: "html",
            data: {prod_id: "<?=$product['product_id'];?>", prod_type: "<?=$product['type_id'];?>"},
            success: function (html) {
              if (html !== '') {
                $("#prod_httpnotice_edit").html(html);
                UIkit.modal("#prod_httpnotice_edit").show();
              }
            }
          });
        });
      });
    </script>
<?php endif;
$notices = json_encode(mb_convert_encoding(AdminNotice::getNoticesHtml(), 'UTF-8', 'UTF-8') ?? "", JSON_UNESCAPED_UNICODE);
?>
<script>
    $(function() {
        let notices = <?= $notices ?>;
        $('.logout').prepend(notices);
    });
</script>


    <!-- Start of Omnidesk Widget script {literal}-->
<script>
!function(e,o){!window.omni?window.omni=[]:'';window.omni.push(o);o.g_config={widget_id:"13510-fjppr1kr"}; o.email_widget=o.email_widget||{};var w=o.email_widget;w.readyQueue=[];o.config=function(e){ this.g_config.user=e};w.ready=function(e){this.readyQueue.push(e)};var r=e.getElementsByTagName("script")[0];c=e.createElement("script");c.type="text/javascript",c.async=!0;c.src="https://omnidesk.ru/bundles/acmesite/js/cwidget0.2.min.js";r.parentNode.insertBefore(c,r)}(document,[]);
</script>
<!-- End of Omnidesk Widget script {/literal}-->


<?php //echo round((microtime(true) - START),3);?>

<script type="text/javascript">
  var notification = new Notification();
</script>

<? foreach ($_COOKIE as $key => $value) {
  $notif = explode("-", $key);

  if($notif[0] != 'notif' || empty($value) || $value == "-")
    continue;
  ?>
  <script type="text/javascript">
    notification.addMessage("<?=$value?>", "<?=$notif[1]?>", <?=$notif[2]?>);
  </script>
  <?
  unset($_COOKIE[$key]);
  @ setcookie($key, "-", time()-1, '/admin');
} ?>

<?
if($remained_data = System::getCookie('remained_data', 
    substr($_SERVER['REQUEST_URI'], -1) == '/'
      ? substr($_SERVER['REQUEST_URI'], 0, -1)
      : $_SERVER['REQUEST_URI']
  ) && !empty($remained_data)
): 
  $body = "У вас есть несохраненные данные от ";
  $body .= date('dmY') == date('dmY', $remained_data['time'])
    ? date('(сегодня) H:i', $remained_data['time'])
    : date('(d.m.Y) H:i', $remained_data['time']);
  $body .= "<input type='button' value='Применить их' class='btn-set_value' onclick='submitSetValues();' >";
?>

  <style type="text/css">
    .btn-set_value{
      margin: 0 10px;
      padding: 5px 10px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
  }
  </style>

  <script type="text/javascript">
    function submitSetValues() {
      json_data = '<?=(json_encode($remained_data))?>';
      addMessage("Несохраненные данные применены! Незабудьте сохранить", "message" , 7);
      hideMessage('non-save_message', 0.5);
      return setValues('<?=(json_encode($remained_data))?>', 'border-color', '#eab967');
    }
  </script>
  <script>
    addMessage("<?=$body?>", "message" , 200, 'non-save_message');
  </script>

<?endif;?>