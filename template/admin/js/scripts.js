/*jshint -W104*/

var url=document.location.href;
var url_split = url.split("?");

if(url_split[1] && (url_split[1] == 'success' || url_split[1] == 'error'))
  history.pushState(null, null, url_split[0]);

function responsive_filemanager_callback(field_id){
  $('#'+field_id).trigger('change');
}

var SMSCountCharacters = function() {
  $('[data-counting-characters]').each(function(i,s) {
    let $el = $(this);
    let max_length = $el.data('max_length');
    let reg = /[\u0400-\u04FF]/;

    $el.keyup(function() {
      let text = $(this).val();
      let cur_length = text.length;
      if (cur_length > max_length) {
        this.value = this.value.substr(0, max_length);
        cur_length = max_length;
        $(this).next('.counting-characters').find('.counting-characters_count').addClass('color-red');
      } else {
        $(this).next('.counting-characters').find('.counting-characters_count').removeClass('color-red');
      }

      $(this).next('.counting-characters').find('.counting-characters_count').text(cur_length);
      let sms_count_characters = text.search(reg) !== -1 ? 70 : 160;
      let count_sms = Math.ceil(cur_length / sms_count_characters);
      $(this).next('.counting-characters').find('.counting-characters_count-sms').text(count_sms);
    });
  });
};

var clickOver = function() {
  $('[data-toggle="popover"]').clickover({
    placement : 'top',
    global_close: true,
    html: true,
  });
};

$(document).ready(function() {
  $(".menu-apsell").lightTabs();
  //Accordion Nav
  $('.mainNav').navAccordion({
      expandButtonText: '<i class="icon-angle-down"></i>',  //Text inside of buttons can be HTML
      collapseButtonText: '<i class="icon-angle-down"></i>'
    },function(){
      console.log('Callback');
    });

  $('input[type="file"]').styler({});
  $('select[multiple="multiple"]:not(.not-select2)').select2();


  $('.nav-click').click(function () {
    $(this).closest('.nav_gorizontal__parent-wrap').toggleClass('active');
  });

  $(document).on('click', function(e) {
    if (!$(e.target).closest(".nav-click").length) {
      $('.nav_gorizontal__parent-wrap').removeClass('active');
    }
    e.stopPropagation();
  });

  $(document).mouseup(function(e) {
    var container = $(".nav_gorizontal__parent-wrap.active");
    if (!container.is(e.target) &&
      container.has(e.target).length === 0 &&
      !$(e.target).hasClass("nav_gorizontal__parent-wrap")) {
      container.removeClass("active");
    }
  });

  $('#inner-descr').on('click', function() {
    $(this).children('input').prop('checked', true);
    $('#external-descr').children('input').prop('checked', false);
    $('.external-descr-i').css('display', 'none');
    $('.inner-descr-i').css('display', 'block');
    $('.big-descr').css('display', 'block');
    $('.short-desct').css('display', 'none');
  });

  $('#external-descr').on('click', function() {
    $(this).children('input').prop('checked', true);
    $('#inner-descr').children('input').prop('checked', false);
    $('.external-descr-i').css('display', 'block');
    $('.inner-descr-i').css('display', 'none');
    $('.big-descr').css('display', 'none');
    $('.short-desct').css('display', 'block');
  });

  $('.filter .list > li > a').click(function () {
    $(this).parent('.filter .list li').toggleClass('active');
  });

  $(document).on('click', function(e) {
    if (!$(e.target).closest(".filter .list > li > a").length) {
      $('.filter .list li').removeClass('active');
    }
    e.stopPropagation();
  });

  $('.custom-radio:nth-child(2) ~ *').parent('.custom-radio-wrap:not(.not-lot-of)').addClass('custom-radio-lot-of');

  $('.table-sort').DataTable({
    "paging":   false,
    "sDom": '<"top"i>rt<"bottom"lp><"clear">',
    "info":     false,
    "order": [[ 0, "desc" ]],
    "columns": [
      null,
      null,
      null,
      {"sorting": false},
      {"sorting": false}
    ]
  });

  $('#checkbox-change').change(function() {
    $('.special-treatment').addClass('visible');
  });

  $('#checkbox-change-2').change(function() {
    $('.special-treatment').removeClass('visible');
  });

  if ($('#table-receipt').html() === '') {
    $('#table-receipt').html('<p>Здравствуйте, [NAME]!</p>\n' +
        '<p>В соответствии с положениями п. 2.1. ст. 2 ФЗ "О применении контрольно-кассовой<br />техники при осуществлении наличных денежных расчетов и (или) расчетов с<br />использованием электронных средств платежа" от 22.05.2003 N 54-ФЗ<br />направляем Вам документ, подтверждающий факт произведения расчета между<br />индивидуальным предпринимателем и покупателем.</p>\n' +
        '<table style="width: 100%; max-width: 100%; border-collapse: collapse; border-spacing: 0; font-size: 14px; color: #373a4c;">\n' +
        '<tbody>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Квитанция об оплате №:</td>\n' +
        '<td style="text-align: right;">[ORDER]</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Дата:</td>\n' +
        '<td style="text-align: right;">[DATE]</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Наименование</td>\n' +
        '<td style="text-align: right;">[ORG_NAME]</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">ИНН:</td>\n' +
        '<td style="text-align: right;">[INN]</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Система налогообложения:</td>\n' +
        '<td style="text-align: right;">Патент</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Признак расчета (приход, возврат прихода):</td>\n' +
        '<td style="text-align: right;">приход</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Форма расчета (электронные деньги, безналичный расчет, наличные):</td>\n' +
        '<td style="text-align: right;">электронные деньги</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Пользователь не является плательщиком НДС</td>\n' +
        '<td style="text-align: right;">&nbsp;</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Адрес электронной почты поставщика:</td>\n' +
        '<td style="text-align: right;">[EMAIL]</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Адрес электронной почты покупателя:</td>\n' +
        '<td style="text-align: right;">[CLIENT_EMAIL]</td>\n' +
        '</tr>\n' +
        '<tr>\n' +
        '<td style="padding-right: 15px; padding-top: 4px; padding-bottom: 4px;">Место расчета (наименование сайта):</td>\n' +
        '<td style="text-align: right;">[SITE]</td>\n' +
        '</tr>\n' +
        '</tbody>\n' +
        '</table>\n' +
        '<p><strong>Предмет расчёта:</strong></p>\n' +
        '<p>[ORDER_ITEMS]</p>');
  }
  
  $('#minmaxprice').on('click', function() {  
    if ($(this).is(':checked')){
      $('#customprice').css("display", "block");
    } else {
      $('#customprice').css("display", "none");
    }  
  });

  SMSCountCharacters();

  $('[data-uk-tooltip=""]').tooltip({
    create: function(ev, ui) {
    $(this).data("ui-tooltip").liveRegion.remove();
  }
});

  clickOver();
});

var dependent_blocks = function() {
  if ($('[data-show_on]').length > 0) {
    $('[data-show_on]').each(function () {
      let $block = $('#' + $(this).data('show_on'));
      if ($block.length > 0) {
        if (($(this).is(':selected') || $(this).is(':checked')) || ($(this).get(0).tagName === 'DIV' && $(this).hasClass('active'))) {
          $block.removeClass('hidden');
        } else {
          $block.addClass('hidden');
        }
      }
    });
  }

  let visible_blocks = [];

  let change_blocks = function (blocks, action) {

    if (blocks && action) {
      blocks.forEach(function (val) {

        let $block = $('#' + val).length > 0 ? $('#' + val) : $('[data-id="' + val + '"]');

        if ($block.length > 0) {

          if (action === 'show') {
            $block.removeClass('hidden');

          } else if(action === 'hide') {
            $block.addClass('hidden');

          }else if(action === 'checked'){
            if ($block.attr('type') === "checkbox" || $block.attr('type') === "radio")
              $block.prop('checked', true);

          }else if(action === 'unchecked'){
            if ($block.attr('type') === "checkbox" || $block.attr('type') === "radio")
              $block.prop('checked', false);
            
          }else if(action === 'setrequired'){
            $block.prop('required', true);
            
          }else if(action === 'unsetrequired'){
            $block.prop('required', false);
            
          }
        }
      });
    }
  };

  if ($('[data-show_on]').length > 0) {
    $('[data-show_on]').each(function () {
      let $el = $(this);
      let blocks = $el.data('show_on').split(',');
      blocks.forEach(function (val) {
        let $block = $('#' + val);
        if ($block.length > 0 && ($el.is(':selected') || $el.is(':checked'))) {
          $block.removeClass('hidden');
        }
      });
    });
  }

  $(document).on('change', 'select', function (e) {
    let $el = $("option:selected", this);
    let blocks = '';
    visible_blocks = [];
    if (typeof($el.data('show_on')) !== 'undefined') {
      blocks = $el.data('show_on').split(',');
      blocks.forEach(function (val) {
        let $block = $('#' + val);
        if ($block.length > 0) {
          $block.removeClass('hidden');
          visible_blocks.push(val);
        }
      });
    }

    let $select = $el.parent('select');
    let $els = $select.find('option[data-show_on]');
    $els.each(function () {
      let blocks2 = $(this).data('show_on').split(',');
      if (blocks !== blocks2 && !$(this).is(':selected')) {
        blocks2.forEach(function (val) {
          let $block = $('#' + val);
          if($block.is(':visible') && visible_blocks.indexOf(val) == -1) {
            $($block).addClass('hidden');
          }
        });
      }
    });
  });

  $(document).on('change', 'input[type="radio"], input[type="checkbox"][data-show_on]', function () {
    let $el = $(this);
    if ($el.attr('type') === 'checkbox' || $el.parents('.custom-radio-wrap').find('input[type="radio"][data-show_on]').length > 0) {
      let show_blocks = [], hide_blocks = [];

      if ($el.attr('type') === 'checkbox') {
        show_blocks = $el.is(':checked') ? $el.data('show_on').split(',') : null;
        hide_blocks = !$el.is(':checked') ? $el.data('show_on').split(',') : null;
      } else {
        show_blocks = $el.is(':checked') && typeof($el.data('show_on')) !== 'undefined' ? $el.data('show_on').split(',') : null;
        let $els = $el.parents('.custom-radio-wrap').find('input[type="radio"][data-show_on]:not(:checked)');
        if ($els) {
          $els.each(function () {
            $(this).data('show_on').split(',').forEach(function (val) {
              if (hide_blocks.indexOf(val) === -1 && (!show_blocks || show_blocks.indexOf(val) === -1)) {
                hide_blocks.push(val);
              }
            });
          });
        }
      }

      change_blocks(show_blocks, 'show');
      change_blocks(hide_blocks, 'hide');
    }
  });

  $(document).on('change', 'input[type="radio"], input[type="checkbox"][data-set_checked]', function () {
    let $el = $(this);
    if ($el.attr('type') === 'checkbox' || $el.parents('.custom-radio-wrap').find('input[type="radio"][data-set_checked]').length > 0) {
      let set_checked = [], set_unchecked = [];

      if ($el.attr('type') === 'checkbox') {
        set_checked = $el.is(':checked') ? $el.data('set_checked').split(',') : null;
        set_unchecked = !$el.is(':checked') ? $el.data('set_unchecked').split(',') : null;
      } else {
        set_checked = $el.is(':checked') && typeof($el.data('set_checked')) !== 'undefined' ? $el.data('set_checked').split(',') : null;
        let $els = $el.parents('.custom-radio-wrap').find('input[type="radio"][data-set_checked]:not(:checked)');

        if ($els) {
          $els.each(function () {
            $(this).data('set_checked').split(',').forEach(function (val) {
              if (set_unchecked.indexOf(val) === -1 && (!set_checked || set_checked.indexOf(val) === -1)) {
                set_unchecked.push(val);
              }
            });
          });
        }
      }

      change_blocks(set_checked, 'checked');
      change_blocks(set_unchecked, 'unchecked');
    }
  });

  $(document).on('click', 'input[data-set_unchecked]', function () {
    let $el = $(this);
    let blocks = $(this).data('set_unchecked').split(',');

    if ($el.is(':checked') && ($block.attr('type') === "checkbox" || $block.attr('type') === "radio"))
      change_blocks(blocks, 'checked');
  });

  $(document).on('change', 'input[type="radio"], input[type="checkbox"][data-set_required]', function () {
    let $el = $(this);
    if ($el.attr('type') === 'checkbox' || $el.parents('.custom-radio-wrap').find('input[type="radio"][data-set_required]').length > 0) {
      let set_required = [], unset_required = [];

      if ($el.attr('type') === 'checkbox') {
        set_required = $el.is(':checked') ? $el.data('set_required').split(',') : null;
        unset_required = !$el.is(':checked') ? $el.data('unset_required').split(',') : null;
      } else {
        set_required = $el.is(':checked') && typeof($el.data('set_required')) !== 'undefined' ? $el.data('set_required').split(',') : null;
        let $els = $el.parents('.custom-radio-wrap').find('input[type="radio"][data-set_required]:not(:checked)');

        if ($els) {
          $els.each(function () {
            $(this).data('set_required').split(',').forEach(function (val) {
              if (unset_required.indexOf(val) === -1 && (!set_required || set_required.indexOf(val) === -1)) {
                unset_required.push(val);
              }
            });
          });
        }
      }

      change_blocks(set_required, 'setrequired');
      change_blocks(unset_required, 'unsetrequired');
    }
  });

  $(document).on('click', 'input[data-unset_required]', function () {
    let $el = $(this);
    let blocks = $(this).data('unset_required').split(',');

    if ($el.is(':checked') && ($block.attr('type') === "checkbox" || $block.attr('type') === "radio"))
      change_blocks(blocks, 'checked');
  });

  if ($('[data-set_required]').length > 0) {
    $('[data-set_required]').each(function () {
      let $el = $(this);
      let blocks = $(this).data('set_required').split(',');

      if ($el.is(':selected') || $el.is(':checked')) 
        change_blocks(blocks, 'setrequired');
    });
  }

  if ($('[data-show_off]').length > 0) {
    $('[data-show_off]').each(function () {
      let $el = $(this);
      let blocks = $(this).data('show_off').split(',');

      if ($el.is(':selected') || $el.is(':checked')) 
        change_blocks(blocks, 'hide');
    });
  }

  $(document).on('change', 'select', function () {
    let $els = $(this).find('option:not(:selected)[data-show_off]');
    let $el_selected = $(this).find('option:selected[data-show_off]');
    let els_hidden = $el_selected.length > 0 ? $el_selected.data('show_off').split(',') : [];
    let selected_index = -1;

    if (els_hidden) {
      els_hidden.forEach(function (val) {
        let $block = $('#' + val);
        if ($block.length > 0) {
          $block.addClass('hidden');
        }
      });
    }

    if ($els.length > 0) {
      $els.each(function () {
        let blocks = $(this).data('show_off').split(',');

        blocks.forEach(function (val) {
          let $block = $('#' + val);

          if ($block.length > 0 && els_hidden.indexOf(val) === -1) {
            $block.removeClass('hidden');

            if ($block.parent('select').length > 0) { // если блок является опцией селекта и сброс выбранного секлета не производился
              selected_index = $block.index();
              $block.parent('select').prop('selectedIndex', selected_index);
            }
          }
        });
      });
    }
  });

  $(document).on('click', 'input[data-show_off]', function () {
    let $el = $(this);
    let blocks = $(this).data('show_off').split(',');

    if($el.is(':checked'))
      change_blocks(blocks, 'hide');
    else
      change_blocks(blocks, 'show');
  });
};

$(function () {
  var accordeon = $('.block-collapse-head');
  accordeon.click(function (e) {
    $(this).toggleClass('active').siblings('.block-collapse-inner').slideToggle(300).parent('.block-collapse').toggleClass('show');
  });
  
  if ($('.datetimepicker').length > 0) {
    $.datetimepicker.setLocale('ru');
    $('.datetimepicker').datetimepicker({
      dayOfWeekStart : 1,
      firstDay: 1
    });
  }
  dependent_blocks();
});

document.addEventListener('DOMContentLoaded', function() {
  $('.bell-popap-remove').on('click', function (resp) {
    $.post('/admin/admin-notices/del/0', {del_notices: true}, function (resp) {
      if (resp.status) {
        $('.bell-popap-list').remove();
      }
    });
  });

  $('.bell-popap-item__remove').on('click', function () {
    let $el = $(this);
    let id = $el.data('notice_id');

    $.post('/admin/admin-notices/del/'+id, {del_notices: true}, function (resp) {
      if (resp.status) {
        $el.parent('.bell-popap-item').remove();
      }
    });

  });

  $('.bell-click').on('click', function () {
    $('.bell-popap').toggleClass('show');
    if ($('.bell-count').length > 0) {
      $.post('/admin/admin-notices/read', {read_notices: true}, function (resp) {
        if (resp.status) {
          $('.bell-count').remove();
        }
      });
    }
  });
});

function getRealTime(){
  aTime = new Date().getTime() + '_';

  return parseInt(aTime.substring(0, aTime.length - 4));
}

function getDifferenceTime(sTime, elementID){
  dTime = Math.floor(
    ((getRealTime() - parseInt(sTime)) + 300) / 3600
  );

  if(elementID && $('#' + elementID))
    $('#' + elementID).value = dTime;

  return dTime;
}

function setValues(json_data, set_css_name, set_css_value){
  data = JSON.parse(json_data);
  values = data.POST;
  var updates_value = [];

  for (var key in values){
    if (values.hasOwnProperty(key)) {
      value = values[key];
      elmt = $('[name="' + key + '"]');

      upd_res = setNewValue(elmt, value);
      updated = upd_res[0];
      old_val = upd_res[1];

      if(updated){
        updates_value[key] = [old_val, value];
        elmt.css(set_css_name, set_css_value);
      }
    }
  }

  console.log(updates_value);

  return false;
}

function setNewValue(elmt, value){
  old_val = elmt.val();
  updated = false;

  if(elmt.is("input")){
    if(elmt.val() != value){
      elmt.val(value);
      updated = true;
    }

  }else
  if(elmt.attr('type') == "radio"){
    console.log(elmt.attr('name'));
    elmt.filter('[value=' + value + ']').prop('checked', true);

  }else
  if(elmt.prop('type') == "textarea"){
    if(elmt.val() != value){
      elmt.val(value);
      updated = true;
    }

  }else
  if(elmt.prop('type') == "select-one"){
    if(elmt.val() != value){
      elmt.val(value);
      updated = true;
    }

  }else
    console.log("ELSE " + elmt.attr('name') + ": " + elmt.val() + " = " + elmt.attr('type') + "  --  " + elmt.prop('type'));

  return [updated, old_val];
}



function addHiddenInput(theForm, key, value, id) {
    inpt = '<input type="hidden" name="' + key + '" value="' + value + '"';
    
    if(id)
        inpt = inpt + ' id="' + id + '"';
    inpt = inpt + '/>';
    theForm.append(inpt);
}



$(document).ready(function () {
    var form_ids = 1;
    $("form").each(function() {
        if(!$(this).attr("id"))
        $(this).attr("id", form_ids++);

        id = $(this).attr("id");
        addHiddenInput( $(this).closest('form'), 'all_start_elmnts-' + id + '-' + getRealTime(), $(this).serialize());
    });

    var form_updates = [];
    $("form").change(function() {
        if(!$(this).attr("id"))
            $(this).attr("id", form_ids++);

        id = $(this).attr("id");

        if(!form_updates[id]){
            addHiddenInput( $(this).closest('form'), 'form_is_update-' + id, getRealTime());
            form_updates[id] = true;
        }
    });

    /*Выбрать все - для чекбоксов*/
    $('input[data-id]:checkbox').click( function(){
        id = $(this).data('id');
        all_count = $('input[data-id="' + id + '"]:checkbox').length;
        ch_count = $('input[data-id="' + id + '"]:checkbox:checked').length;
        $('input[data-selectall="' + id + '"]').prop('checked', all_count == ch_count);
    });
    $('input[data-selectall]:checkbox').click(function(){
        id = $(this).data('selectall');
        $('input[data-id="' + id + '"]').prop('checked', $(this).is(":checked"));

    });
});

window.onload = function() {
    /*Скрыть меню*/
    $('h4[data-hidden-btn]').each( function(){      
        title = $(this);
        btn = 'show';
        trnsf = '';

        if($(this).data('autohiding') != "off"){
            block = ((title.parent()).parent()).parent();

            [].forEach.call(block.find('[data-id="' + $(this).data('hidden-btn') + '"]'), function (elmnt) {
                elmnt.setAttribute('style', 'display: none !important');
            });
        }else{
            btn = 'hide';
            trnsf = 'transform: rotate(180deg) scaleX(-1);';
        }

        title.append('<div style="position: relative;"><div class="show_all" data-btn="' + btn + '" data-btn-id="' + $(this).data('hidden-btn') + '" style="width: 18px; right: 10px; position: absolute; font-size: 10px; top: -20px; cursor: pointer; ' + trnsf + '"><i class="icon-down"></i></div></div>');
    });

    $('[data-btn][data-btn-id').click( function(){
        block = (((title.parent()).parent()).parent()).parent();

        if($(this).data('btn') == 'show'){
            // $(this).css('top', '10px');
            $(this).css('transform', 'rotate(180deg) scaleX(-1)');

            [].forEach.call(block.find('[data-id="' + $(this).data('btn-id') + '"]'), function (elmnt) {
                elmnt.removeAttribute('style');
            });

            $(this).data('btn', 'hide');
        }

        else
        if($(this).data('btn') == 'hide'){
            // $(this).css('top', '-20px');
            $(this).css('transform', 'rotate(0deg) scaleX(-1)');

            [].forEach.call(block.find('[data-id="' + $(this).data('btn-id') + '"]'), function (elmnt) {
                    elmnt.setAttribute('style', 'display: none !important');
            });

            $(this).data('btn', 'show');
        }
    });
};