document.addEventListener('DOMContentLoaded', function() {
  $(document).on('click', '.public-homework-list .like_btn', function() {
    var $like_btn = $(this);
    var homework_id = $like_btn.data('homework_id');
    var user_id = $like_btn.data('user_id');
    var is_like = $like_btn.closest('.like_btn_wrap').hasClass('like') ? 0 : 1;

    $.ajax({
      url: '/trainingajax/answerlike',
      type: "POST",
      dataType: "json",
      data: {is_like: is_like, homework_id: homework_id, user_id: user_id},
      success: function ($resp) {
        if ($resp['status']) {
          if ($resp['is_like'] == 1) {
            $like_btn.closest('.like_btn_wrap').addClass('like');
          } else if($resp['is_like'] == 0) {
            $like_btn.closest('.like_btn_wrap').removeClass('like');
          }
          $like_btn.closest('.like_btn_wrap').find('.like_count').html($resp.likes)
        } else {
          console.error($resp['error']);
        }
      }
    });
  });

  $(document).on('click', '.public-homework-list .btn-show-sub-answers', function () {
    let $el = $(this);
    $.ajax({
      url: '/trainingajax/getsubanswers',
      type: "POST",
      dataType: "html",
      data: {homework_id: $el.data('homework_id')},
      success: function (html) {
        if (html) {console.log(html);
          $($el.closest('.sub_answer_list-list').html(html));
        }
      }
    });
  });

  $(document).on('click', '.btn-show-public-homeworks', function () {
    let $el = $(this);

    $.ajax({
      url: '/trainingajax/getpublichomeworks',
      type: "POST",
      dataType: "html",
      data: {lesson_id: $el.data('lesson_id'), user_id: $el.data('user_id'),
        offset: $el.data('offset'), count_homeworks: $el.data('count_homeworks')
      },
      success: function (html) {
        if (html) {
          $($el.closest('.public-homework-list').append(html));
          $el.closest('.public-homework-list').find('.btn-show-public-homeworks-wrap').first().remove();
        }
      }
    });
  });

  $(document).on('click', '.public-homework-list .reply_btn', function () {
    let $el = $(this);
    $('#add_user_comment').removeClass('hidden');
    $($el).closest('.answer_list').find('.add-user-comment-wrap').append($('#add_user_comment'));
    $($el).closest('.public-homework-list-wrap').find('[name="public_homework[homework_id]"]').val($el.data('homework_id'));

    let pos = $('#add_user_comment').offset().top - 100;
    $([document.documentElement, document.body]).animate({
      scrollTop: pos
    }, 500);
  });
}, false);