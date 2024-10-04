document.addEventListener('DOMContentLoaded', function() {
  $('.filters-2 select').change(function (e) {
    let url = '/catalog';
    let data = new Map();

    $('.filters-2 option:selected').each(function (i, s) {
      let filter_name = $(this).data('filter');
      let value = $(this).val();
      url += (i == 0 ? '?' : '&') + filter_name + '=' + value;
    });

    history.pushState(null, null, url);

    $.ajax({
      method: 'get',
      url: url,
      dataType: 'html',
      success: function(html) {
        $('.product-list').html(($(html).find('.product-list').html()));
      },
      error: function(e) {
        console.error(e.message);
      }
    });
  });
});
      