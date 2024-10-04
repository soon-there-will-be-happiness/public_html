document.addEventListener('DOMContentLoaded', function() {
  let page_url = document.location.pathname;

  let datetimepickerSetFormat = function() {
    $.datetimepicker.setLocale('ru');
    $('.datetimepicker').each(function() {
      let format = $(this).data('format');
      $(this).datetimepicker({
        format: format,
        firstDay: 1,
        dayOfWeekStart : 1,
      });
    });
  };

  if ($('.datetimepicker').length > 0) {
    datetimepickerSetFormat();
  }

  $(document).on('change', '.condition_type', function() {
    let $el = $("option:selected", this);
    let condition_type = $el.val();
    let $condition_block = $el.closest('.condition-wrap').find('.condition');
    let cond_index = $condition_block.closest('.condition-wrap').data('condition_index');

    if (condition_type) {
      $.post('/admin/segment-filter/get-condition?page_url='+page_url, {condition_type: condition_type, cond_index: cond_index}, function (html) {
        $condition_block.removeClass('hidden');
        $condition_block.html(html);

        if ($('.datetimepicker').length > 0) {
          datetimepickerSetFormat();
        }
        $('select[multiple="multiple"]:not(.not-select2)').select2();
      });
    } else {
      $condition_block.addCass('hidden');
    }
  });

  if (page_url.indexOf('/admin/conditions') === 0) {
    $(document).on('change', '[name="filter_model"]', function() {
      document.location.href = page_url + '?filter_model='+$('[name="filter_model"]:checked').val();
    });

    $('[name="show_segment"]').click(function() {
      let segment = $('[name="segment"]').val();
      let url = $('[name="filter_model"]:checked').val() === '1' ? '/admin/orders' : '/admin/users';
      window.open(url+'?filter=фильтр&segment='+segment);
    });
  }

  $(document).on('change', '.segment-filter [name="segment"]', function() {
    let $el = $("option:selected", this);
    let filter_model = $('[name="filter_model"]:checked').val();
    let args = '?filter=фильтр&segment='+$el.val();

    if (page_url.indexOf('/admin/conditions') === 0) {
      filter_model = $('[name="filter_model"]:checked').val();
      args += '&filter_model='+filter_model;
      if (!filter_model || !$el.val()) {
        args = '';
      }
    }

    if ($el.val() == 'all') {
      document.location.href = page_url;
    } else if($el.val() !== 'segment') {
      document.location.href = page_url + args;
    } else {
      if(document.location.href.indexOf('filter') !== -1) {
        document.location.href = page_url + args;
      } else {
        $('.save-segment').removeClass('hidden');
        $('.submit-wrap').removeClass('hidden');
      }
    }
  });

  var events = function () {
    $('.conditions-group').hover(function() {
      $(this).addClass('active');
      if ($(this).parents('.conditions-group').length > 0) {
        $(this).parents('.conditions-group').removeClass('active');
      }
    }, function() {
      $(this).removeClass('active');
      if ($(this).parents('.conditions-group').length > 0) {
        $(this).parents('.conditions-group').addClass('active');
      }
    });

    $('.condition-wrap').hover(function() {
      $(this).addClass('active');
      if ($(this).parents('.conditions-group').length > 0) {
        $(this).parents('.conditions-group').removeClass('active');
      }
    }, function() {
      $(this).removeClass('active');
      if ($(this).parents('.conditions-group').length > 0) {
        $(this).parents('.conditions-group').addClass('active');
      }
    });
  };

  let getFreeCondIndex = function() {
    let index = 0;
    if ($('.condition-wrap').length) {
      $('.condition-wrap').each(function() {
        if ($(this).data('condition_index') > index) {
          index = $(this).data('condition_index');
        }
      });
      index += 1;
    }

    return index;
  };

  $(document).on('click', '.conditions-group > .logic-buttons-wrap .logic-button:not([disabled])', function() {
    let $el = $(this).parent('.logic-buttons-wrap').parent();
    let cond_index = getFreeCondIndex();
    let logic_type = $(this).attr('data-logic_type');

    if ((logic_type !== 'and' || logic_type !== 'or') && logic_type !== $el.attr('data-logic_type')) {
      return false;
    }
    
    $.post('/admin/segment-filter/get-condition?page_url='+page_url, {cond_index: cond_index, logic_type: logic_type}, function (html) {
      $el.append(html);
      events();
    });
  });

  let delGroupBlock = function($el) {
    if ($el.hasClass('condition-wrap')) {
      $el.closest('.conditions-group').children('.logic-buttons-wrap').remove();
      $el.closest('.conditions-group').children('.logic-button-change').remove();
      $el.unwrap('.conditions-group');
    }
  };

  $(document).on('click', '.logic-button:not([disabled])', function() {
    let logic_type = $(this).attr('data-logic_type');
    let $el = $(this).parent('.logic-buttons-wrap').parent();

    if (logic_type === 'del' || (logic_type === $el.attr('data-logic_type') && $el.hasClass('conditions-group'))) {
      return false;
    }

    let logic_text = logic_type === 'and' ? 'и' : 'или';
    let group_index = $('.conditions-group').length;
    let $group_block = $(
      '<div class="conditions-group" data-logic_type="'+logic_type+'" data-index="'+group_index+'" data-invert="0">'+
      '<a class="logic-button-change" href="javascript:void(0)">'+logic_text+'</a>'+
      '<div class="logic-buttons-wrap">'+
      '<a href="javascript:void(0);" class="logic-button" data-logic_type="and">и</a> '+
      '<a href="javascript:void(0);" class="logic-button" data-logic_type="or">или</a> '+
      '<a href="javascript:void(0);" class="logic-button" data-logic_type="not">не</a> '+
      '<a href="javascript:void(0);" class="logic-button" data-logic_type="del">&nbsp;</a>'+
      '</div>'+
      '</div>'
    );

    if (logic_type == 'and' || logic_type == 'or') {
      let cond_index = getFreeCondIndex();


      $.post('/admin/segment-filter/get-condition?page_url='+page_url, {cond_index: cond_index, logic_type: logic_type}, function (html) {
        $el.after($group_block);
        $el.appendTo($group_block);
        $group_block.append(html);
        events();
      });
    } else if(logic_type == 'not') {
      let invert = 0;
      if (typeof($el.attr('data-invert')) === 'undefined' || $el.attr('data-invert') == 0) {
        invert = 1;
      }

      $el.attr('data-invert', invert);
    }
  });

  $(document).on('click', '.condition-wrap > .logic-buttons-wrap .logic-button[data-logic_type="del"]:not([disabled])', function() {
    let $el = $(this).parent('.logic-buttons-wrap').parent();
    let $group_el = $el.parent('.conditions-group');

    if ($('.condition-wrap').length == 1) {
      $('.condition-wrap option').removeAttr('selected');
      $('.condition').html('');
    } else {
      if ($group_el.children('.condition-wrap').length == 1 && $group_el.children('.conditions-group').length == 1) { // если внутри группы одно условие и одна группа
        //console.log($group_el.children('.conditions-group').children('.condition-wrap').first());
        delGroupBlock($el);
      } else {
        if ($group_el.children('.condition-wrap').length < 3 && $group_el.children('.conditions-group').length == 0) {
          delGroupBlock($el);
        }
      }

      $el.remove();
    }
  });

  $(document).on('click', '.conditions-group > .logic-buttons-wrap .logic-button[data-logic_type="del"]:not([disabled])', function() {
    let $el = $(this).parent('.logic-buttons-wrap').parent();

    if ($('.condition-wrap').length > 2 && $el.parent('.conditions-group').length > 0) {
      if ($el.parent('.conditions-group').children('.conditions-group').length < 3) {
        let $cond_el = $el.parent('.conditions-group').children('.condition-wrap');

        if ($cond_el.length == 0) {
          if ($el.prev('.conditions-group').length > 0) {
            $cond_el = $el.prev('.conditions-group').children('.condition-wrap');
          } else {
            $cond_el = $el.next('.conditions-group').children('.condition-wrap');
          }
        }

        delGroupBlock($cond_el);
      }

      $el.remove();
    } else {
      let $cond_el = $('.condition-wrap').first();
      $cond_el.find('option').removeAttr('selected');
      $cond_el.find('.condition').html('');
      $el.replaceWith($cond_el.clone());
    }
  });

  let getGroupsData = function() {
    let data = {};

    if ($('.conditions-group').length > 0) {
      $('.conditions-group').each(function(i) {
        let group_index = $(this).data('index');
        let logic_type = $(this).attr('data-logic_type');
        let invert = $(this).data('invert');

        data[i] = {'groups': [], conditions: [], logic_type: logic_type, index: group_index, invert: invert};

        if ($(this).children('.conditions-group').length > 0) {
          $(this).children('.conditions-group').each(function(s) {
            data[i]['groups'][s] = $(this).data('index');
          });
        }

        if ($(this).children('.condition-wrap').length > 0) {
          $(this).children('.condition-wrap').each(function(t) {
            data[i]['conditions'][t] = $(this).data('condition_index');
          });
        }
      });
    }

    return data;
  };

  let formSubmitBefore = function($form) {
    $('.conditions-group .condition-wrap').each(function() {
      let logic_type = $(this).closest('.conditions-group').attr('data-logic_type');
      let group_index = $(this).closest('.conditions-group').data('index');
      let cond_index = $(this).data('condition_index');

      $(this).find('[name="logic_type['+cond_index+']"]').val(logic_type);
      $(this).find('[name="group_index['+cond_index+']"]').val(group_index);
    });

    $('.condition-wrap').each(function() {
      let cond_index = $(this).data('condition_index');
      let invert = $(this).data('invert');
      $(this).find('[name="invert['+cond_index+']"]').val(invert);
    });

    $form.find('[name="groups_data"]').val(JSON.stringify(getGroupsData($form)));
  };

  $('[name="filter"]').closest('form').submit(function() {
    let $form = $(this);

    $('.conditions-group .condition-wrap').each(function() {
      let logic_type = $(this).closest('.conditions-group').attr('data-logic_type');
      let group_index = $(this).closest('.conditions-group').data('index');
      let cond_index = $(this).data('condition_index');

      $(this).find('[name="logic_type['+cond_index+']"]').val(logic_type);
      $(this).find('[name="group_index['+cond_index+']"]').val(group_index);
    });

    formSubmitBefore($form);
    return true;
  });

  $('.save-segment').click(function() {
    let segment_id = $(this).closest('form').find('[name="segment"]').val();
    if (!segment_id.match(/[0-9]/)) {
      segment_id =  null;
    }

    let $form = $(this).closest('form');
    let send_data = function ($form) {
      formSubmitBefore($form);
      let data = $form.serialize();

      $.post('/admin/segment-filter/save-segment?page_url=' + page_url, {
        segment_id: segment_id,
        segment_name: segment_name,
        data: data
      }, function (resp) {
        if (resp.segment_id) {
          if (!segment_id) {
            $form.find('[name="segment"] option').removeAttr('selected');
            $form.find('[name="segment"]').append('<option value="' + resp.segment_id + '" selected="selected">' + segment_name + '</option>');
          }

          alert('Сегмент сохранен');
          $('.del-segment').removeClass('hidden');
          window.history.pushState("object or string", "Title", page_url + '?filter=фильтр&segment=' + resp.segment_id);
        }
      });
    };

    let segment_name = '';
    if (!segment_id) {
      segment_name = prompt('Введите название сегмента');
      if (!segment_name) {
        return false;
      }
      send_data($form)
    } else {
      $.post('/admin/segment-filter/check-segment?page_url=' + page_url, {segment_id: segment_id}, function (resp) {
        if (!resp.status) {
          if (confirm('Этот сегмент используется в условиях, вы точно хотите его изменить?')) {
            send_data($form);
          }
        } else {
          send_data($form);
        }
      });
    }
  });

  $('.del-segment').click(function() {
    if (confirm('Вы уверены')) {
      let segment_id = $(this).closest('form').find('[name="segment"]').val();
      $.post('/admin/segment-filter/del-segment/'+segment_id+'?page_url='+page_url, {}, function (resp) {
        if (resp.status) {
          alert('Сегмент успешно удален');
          document.location.href = page_url;
        }
      });
    }
  });

  $(document).on('click', '.logic-button-change:not([disabled])', function() {
    if ($(this).parent('.conditions-group').attr('data-logic_type') === 'and') {
      $(this).parent('.conditions-group').attr('data-logic_type', 'or');
      $(this).text('или');
    } else {
      $(this).text('и');
      $(this).parent('.conditions-group').attr('data-logic_type', 'and');
    }
  });

  $(document).on('change', '.segment-filter select', function() {
    let $el = $("option:selected", this);
    if (typeof($el.data('text_info') !== 'undefined')) {
      $(this).closest('.condition').find('.text-info').text($el.data('text_info'));
    }
  });
  events();


  let getAdditionalInfo = function($el) {
    let $cond_block = $el.closest('.condition-wrap');
    let cond_type = $cond_block.find('.condition_type').val();

    setTimeout(function() {
      if ($cond_block.find('.date-options.main-option').length == 0) {
        $cond_block.find('.additional-info').addClass('hidden');
        return false;
      }

      let cond_value = $cond_block.find('[name^="'+cond_type+'"]').val();
      if (['now', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'month', 'n_days_ago', 'n_hours_ago'].indexOf(cond_value) === -1) {
        $cond_block.find('.additional-info').addClass('hidden');
        return false;
      }

      let val1 = null, val2 = null;
      if (cond_value == 'n_days_ago' || cond_value == 'n_hours_ago') {
        val1 = $cond_block.find('[name^="'+cond_type+'_start"]').val();
        val2 = $cond_block.find('[name^="'+cond_type+'_end"]').val();
      }

      $.post('/admin/segment-filter/get-additional-info?page_url='+page_url,
        {cond_type: cond_type, cond_value: cond_value, val1: val1, val2: val2}, function (html) {
          $cond_block.find('.additional-info').removeClass('hidden');
          $cond_block.find('.additional-info').html(html);
      });
    },500);
  };

  $(document).on('change', '.date-options select, .date-options input, .condition_type', function() {
    getAdditionalInfo($(this));
  });

  $(document).on('change', '.users-segment-filter [name^="training_lesson_training_id["],' +
                                       '.users-segment-filter [name^="training_lesson_access_training_id["],' +
                                       '.users-segment-filter [name^="training_lessons_training_id["]', function() {
    let $training_el = $(this);
    let training_id = $("option:selected", this).val();
    let admin_token = $(this).parents('form').data('token');

    $.ajax({
      url: '/admin/trainingajax/lessonlist',
      method: 'post',
      dataType: 'json',
      data: {training_id: training_id, admin_token: admin_token},
      success: function(lessons) {
        let html = '<option value="">Выбрать урок</option>';
        if (Object.keys(lessons).length > 0) {
          for (let key of Object.keys(lessons)) {
            html += '<option value="' + key + '">' + lessons[key] + '</option>';
          }
        }

        if ($training_el.attr('name').indexOf('training_lesson_training_id') === 0) {
          $training_el.closest('.condition').find('[name^="training_lesson["]').html(html);
        } else if($training_el.attr('name').indexOf('training_lesson_access_training_id') === 0) {
          $training_el.closest('.condition').find('[name^="training_lesson_access_lesson_id["]').html(html);
        } else if($training_el.attr('name').indexOf('training_lessons_training_id') === 0) {
          $training_el.closest('.condition').find('[name^="training_lessons["]').html(html);
        }
      }
    });
  });
});