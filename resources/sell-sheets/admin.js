(function ($) {
  function sortableList() {
    var $list = $('[data-sell-sheet-sortable]');

    if (!$list.length || typeof $list.sortable !== 'function') {
      return;
    }

    $list.sortable({
      handle: '.pbc-sell-sheets__handle',
      axis: 'y',
      placeholder: 'pbc-sell-sheets__item',
    });
  }

  $(function () {
    sortableList();

    $('[data-sell-sheet-select]').on('click', function () {
      var all = $(this).data('sell-sheet-select') === 'all';
      $('[data-sell-sheet-sortable] input[type="checkbox"]').prop('checked', all);
    });
  });
})(jQuery);
