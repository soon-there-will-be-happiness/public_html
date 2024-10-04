$('[name="add_action"]').closest('form').submit(function(e) {
  e.stopPropagation();
  e.preventDefault();

  let $form = $(this);
  let data = $form.serializeArray();
  let $submit = $form.find('input[type="submit"]');
  data.push({name: $submit.attr('name'), value: $submit.val()});

  $.ajax({
    url: $form.attr('action'),
    type: "POST",
    dataType: "html",
    data: data,
    success: function (html) {
      if (html) {
        $('.condition-actions').replaceWith(html);
      }
      UIkit.modal('#add_action').hide();
    }
  });
});

$(document).on('click', '.condition-action-edit', function() {
  let $el = $(this).closest('.condition-action');
  let action_key = $el.data('action_key');
  let action_id = $el.data('action_id');
  let filter_model = $(this).data('filter_model');

  $.ajax({
    url: '/admin/conditions/edit-action/'+action_id,
    type: "POST",
    dataType: "html",
    data: {action_key: action_key, filter_model: filter_model},
    success: function (html) {
      if (html) {
        $('#edit_action .userbox').html(html);
        dependent_blocks();
        $('.multiple-select').select2();
        SMSCountCharacters();
        clickOver();
        editor_init();
      }
    }
  });
});

let sendEditActionFormData = function ($form, repeat_again) {
  let data = $form.serializeArray();
  data.push({name: 'save_action', value: $form.find('[name="save_action"]').val()});
  data.push({name: 'repeat_again', value: repeat_again});

  $.ajax({
    url: $form.attr('action'),
    type: "POST",
    dataType: "json",
    data: data,
    success: function (resp) {
      if (resp.status) {
        if ($form.find('[name="action_id"]').val() == 0) {
          UIkit.modal($('#edit_action')).hide();
        }
        $('.admin_message').show();
        hideAdminMessage();
      }
    }
  });
};

$(document).on('click', '#edit_action [name="save_action"]', function(e) {
  e.preventDefault();
  let $form = $('#edit_action form');
  let action_id = $form.find('[name="action_id"]').val();

  if (action_id == 0) {
    sendEditActionFormData($form, false);
  } else {
    $.post($form.attr('action'), {get_action_results: true, action_id: action_id}, function(resp) {
      if (resp.result > 0) {
        UIkit.modal('.admin-action-chose', {center: true}).show();
      } else {
        UIkit.modal($('#edit_action')).hide();
        sendEditActionFormData($form, false);
      }
    });
  }
});

$('.admin-action-chose-button').click(function(e) {
  e.preventDefault();
  UIkit.modal($(this).closest('.admin-action-chose')).hide();
  sendEditActionFormData($('#edit_action form'), $(this).data('value'));
});

$(document).on('click', '.condition-action .icon-remove', function() {
  if (!confirm('Вы уверены?')) {
    return false;
  }

  let $el = $(this).closest('.condition-action');
  let action_key = $el.data('action_key');
  let action_id = $el.data('action_id');

  $.ajax({
    url: '/admin/conditions/del-action',
    type: "POST",
    dataType: "json",
    data: {action_key: action_key, action_id: action_id},
    success: function (resp) {
      if (resp.status) {
        $el.remove();
      }
    }
  });
});