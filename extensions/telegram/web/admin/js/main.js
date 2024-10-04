$(document).ready(function () {
  function ajax(url, token, argv) {
    $.ajax({
      url: url  + (argv ? '?' + argv : ''),
      type: "POST",
      dataType: "json",
      data: {token: token},
      success: function (resp) {
        if (!resp.msg_error) {
          if (url === '/admin/telegramsetting/delstowaways') {
            $('.progressbar-wrap .traning-title').html('Удаление пользоваталей из чатов, у которых недолжно быть к ним доступа');
          } else if(url === '/admin/telegramsetting/remove-from-blacklist') {
            $('.progressbar-wrap .traning-title').html('Удаление пользоваталей из ЧС, у которых есть доступ');
          }

          $('.progressbar-wrap').show();
          $(".progressbar-loader").css('width', resp.progress + '%');
          $(".progressbar-counter").html('Пользователей обработано: ' + resp.processed + ' (' + resp.progress + '%)');

          if (!resp.is_finish) {
            ajax(url, token, '');
          } else {
            setTimeout(function() {
              $('.progressbar-wrap').hide();
              if (url === '/admin/telegramsetting/delstowaways') {
                alert('Удалено пользователей из чатов: ' + resp.del_users);
              } else if(url === '/admin/telegramsetting/remove-from-blacklist') {
                alert('Удалено пользователей из ЧС: ' + resp.del_users);
              }
            }, 1000);
          }
        } else {
          $('.progressbar-wrap').hide();
          alert(resp.msg_error);
        }
      }
    });
  };

  $('a[name="del_stowaways"]').click(function() {
    var token = $(this).parents('form').find('[name="token"]').val();
    $(".progressbar-loader").css('width', '0%');
    $(".progressbar-counter").html('Пользователей обработано: 0 (0%)');

    ajax('/admin/telegramsetting/delstowaways', token, 'start=1');
  });

  $('a[name="remove_from_blacklist"]').click(function() {
    var token = $(this).parents('form').find('[name="token"]').val();
    $(".progressbar-loader").css('width', '0%');
    $(".progressbar-counter").html('Пользователей обработано: 0 (0%)');

    ajax('/admin/telegramsetting/remove-from-blacklist', token, 'start=1');
  });
});
