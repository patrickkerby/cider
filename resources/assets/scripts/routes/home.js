export default {
  init() {
    // JavaScript to be fired on the home page
  },
  finalize() {
    const $grid = $('.pbc-home-product-grid ul.products');

    if (!$grid.length) {
      return;
    }

    const $filters = $('.pbc-product-filters');
    const $empty = $('.pbc-product-filters__empty');

    function applyHomeProductFilters() {
      const brand = $filters.find('[data-filter-brand].is-active').data('filter-brand');
      const flatOnly = $filters.find('[data-filter-flat-only].is-active').length > 0;
      const showOos = $filters.find('[data-filter-show-oos].is-active').length > 0;
      let visible = 0;

      $grid.find('li.product').each(function eachProduct() {
        const $item = $(this);
        let show = true;

        if (!showOos && $item.hasClass('pbc-out-of-stock')) {
          show = false;
        }

        if (brand === 'pbc' && !$item.hasClass('pbc-brand-pbc')) {
          show = false;
        }

        if (brand === 'tnc' && !$item.hasClass('pbc-brand-tnc')) {
          show = false;
        }

        if (flatOnly && !$item.hasClass('pbc-has-flat-sale')) {
          show = false;
        }

        $item.toggle(show);

        if (show) {
          visible += 1;
        }
      });

      $empty.prop('hidden', visible > 0);
    }

    $filters.on('click', '[data-filter-brand]', function onBrandClick(e) {
      e.preventDefault();
      $filters.find('[data-filter-brand]').removeClass('is-active').attr('aria-pressed', 'false');
      $(this).addClass('is-active').attr('aria-pressed', 'true');
      applyHomeProductFilters();
    });

    $filters.on('click', '[data-filter-flat-only], [data-filter-show-oos]', function onOptionClick(e) {
      e.preventDefault();
      const $btn = $(this);
      const isActive = $btn.toggleClass('is-active').hasClass('is-active');
      $btn.attr('aria-pressed', isActive ? 'true' : 'false');
      applyHomeProductFilters();
    });

    applyHomeProductFilters();
  },
};
