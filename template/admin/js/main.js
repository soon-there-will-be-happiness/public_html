document.addEventListener('DOMContentLoaded', function() {
  $('.fonts .font, .text-align_item').click(function() {
    if ($(this).hasClass('text-align_item')) {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
        $(this).closest('.text-align').find('[name*="[text_align]"]').val('');
      } else {
        $(this).closest('.text-align').find('.text-align_item').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.text-align').find('[name*="[text_align]"]').val($(this).data('text_align_value'));
      }
    } else {
      let val = $(this).hasClass('active') ? 0 : 1;
      $(this).closest('.fonts').find('[name*="['+$(this).data('font_value')+']"]').val(val);
      $(this).toggleClass('active');
    }
  });

  $('.color_palette_wrap').click(function() {
    $(this).closest('.colors_palette').find('.color_palette_wrap').removeClass('active');
    $(this).addClass('active');
    $(this).closest('.colors_palette').find('[type="hidden"]').val($(this).data('val'));
  });

  $('.border-positions .border-position').click(function() {
    $(this).toggleClass('active');
    let val = $(this).hasClass('active') ? 1 : 0;
    $(this).closest('.border-positions').find('[name*="[position_'+$(this).data('val')+'"]').val(val);
  });

  $('.dropdown-toggle').click(function() {
    if ($(this).next('.dropdown-block').hasClass('active')) {
      $(this).next('.dropdown-block').hide(200);
      $(this).next('.dropdown-block').removeClass('active');
    } else {
      $(this).next('.dropdown-block').show(200);
      $(this).next('.dropdown-block').addClass('active');
    }
  });

  $('.condition-settings-control__normal, .condition-settings-control__active').click(function() {
    let $el = $(this);

    if ($el.hasClass('active')) {
      return false;
    }

    $('.condition-settings-control__normal,.condition-settings-control__active').removeClass('active');
    $el.addClass('active');
    $('.hover-settings__value').val($el.data('val'));

    let $select = $('.hover-setting').closest('select');
    if ($select.length > 0) {
      $select.find('option').toggleClass('hidden');

      let option_name = $select.val().indexOf('_hover') > 0 ? $select.val().replace('_hover', '') : $select.val() + '_hover';
      $select.find('[value="'+option_name+'"]').prop('selected', true);

      dependent_blocks();
    } else {
      let $show = $('#'+$el.data('show_on'));
      let $hide = $('#'+$el.closest('.condition-settings-control').find('div:not(.active)').data('show_on'));

      $show.removeClass('hidden');
      $hide.addClass('hidden');
    }
  });

  $('a.disabled').click(function() {
    return false;
  });
});

function updateRangeInput(elem) {
  $(elem).next().val($(elem).val());
}