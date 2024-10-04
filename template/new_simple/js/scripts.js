$(document).ready(function() {
  var url = document.location.href;
  var url_split = url.split("?");

  if(url_split[1] && (url_split[1] == 'success' || url_split[1] == 'error'))
    history.pushState(null, null, url_split[0]);

  $.each($(".client-menu__info a, .client-menu__bottom-line a"),function(){
    if(this.href==url){
      $(this).addClass('current');
    }
  });

  var block_heading_click = $('.block-heading__click');
  block_heading_click.click(function (e) {
    $(this).siblings('.mini_cut').slideToggle(300);
    $(this).parent('.cut').toggleClass('active');
  });
  $('.cut:first-child:not(.training-block):not(.un-login-cut)').addClass('active');

  $('.block-login__click').click(function () {
    $(this).parent('.block-login').toggleClass('active');
  });

  $(document).on('click', function(e) {
    if (!$(e.target).closest(".block-login__click").length) {
      $('.block-login').removeClass('active');
    }
    e.stopPropagation();
  });

  $('.promo-link').click(function (event) {
    $('.promo-block').slideToggle();
    event.preventDefault();
  });

  $(document).on('click', '.decryption-spoiler-title',function () {
    $('.decryption-spoiler-content').slideToggle();
    $(this).toggleClass('show');
  });

  $('input[type="file"]').styler();

  $('.review_desc a').each(function() {
    var a = new RegExp('/' + window.location.host + '/');
    if(!a.test(this.href)) {
      $(this).click(function(event) {
        event.preventDefault();
        event.stopPropagation();
        window.open(this.href, '_blank');
      });
    }
  });

  if ($('.datetimepicker').length > 0) {
    $.datetimepicker.setLocale('ru');
    $('.datetimepicker').datetimepicker({
      dayOfWeekStart : 1
    });
  }

  $('select[multiple="multiple"], .select2').select2();

  $('input[pattern="[A-Za-zА-Яа-яЁё]+$"]').keydown(function(e) {
    let reg = /[A-Za-zА-Яа-яЁё]/;

    if (e.key.search(reg) === -1) {
      return false;
    }
  });

  $('header .main-menu .icon-arrow-down').click(function() {
    $(this).closest('a').next('.submenu').toggleClass('active');
    return false;
  });
});