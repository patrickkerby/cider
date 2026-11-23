@php
  $page_url = admin_url('edit.php?post_type=product&page=pbc-sell-sheets');
  $message  = isset($_GET['message']) ? sanitize_key(wp_unslash($_GET['message'])) : '';
@endphp

<div class="wrap pbc-sell-sheets">
  <h1>{{ __('Sell Sheets', 'sage') }}</h1>

  @if($message === 'empty')
    <div class="notice notice-error"><p>{{ __('Select at least one product.', 'sage') }}</p></div>
  @endif

  @if(empty($dompdf))
    <div class="notice notice-error">
      <p>{{ __('PDF library is not installed. Run composer install in the cider theme directory.', 'sage') }}</p>
    </div>
  @endif

  <p class="pbc-sell-sheets__intro">
    {{ __('Choose a brand, tick the products to include, drag to set order, then generate a PDF. Brands never mix on one sheet.', 'sage') }}
    {!! sprintf(
      __('Logos and default contacts live in %s.', 'sage'),
      '<a href="'.esc_url(admin_url('admin.php?page=theme-general-settings')).'">'.esc_html__('Prairie Bears Settings → Sell sheets', 'sage').'</a>'
    ) !!}
  </p>

  <nav class="nav-tab-wrapper pbc-sell-sheets__tabs">
    @foreach($brands as $slug => $label)
      <a class="nav-tab {{ $brand === $slug ? 'nav-tab-active' : '' }}" href="{{ esc_url($page_url.'&brand='.$slug) }}">{{ $label }}</a>
    @endforeach
  </nav>

  <form method="post" action="{{ esc_url($action) }}" class="pbc-sell-sheets__form">
    @php wp_nonce_field('pbc_generate_sell_sheet') @endphp
    <input type="hidden" name="action" value="pbc_generate_sell_sheet" />
    <input type="hidden" name="brand" value="{{ esc_attr($brand) }}" />

    <table class="form-table" role="presentation">
      <tr>
        <th scope="row"><label for="pbc-sell-sheet-season">{{ __('Season label', 'sage') }}</label></th>
        <td>
          <input type="text" class="regular-text" id="pbc-sell-sheet-season" name="season" value="{{ esc_attr($settings['season']) }}" />
        </td>
      </tr>
    </table>

    <p class="pbc-sell-sheets__actions">
      <button type="button" class="button" data-sell-sheet-select="all">{{ __('Select all', 'sage') }}</button>
      <button type="button" class="button" data-sell-sheet-select="none">{{ __('Select none', 'sage') }}</button>
      <button type="submit" class="button button-primary" {{ empty($dompdf) || empty($products) ? 'disabled' : '' }}>
        {{ __('Generate PDF', 'sage') }}
      </button>
    </p>

    @if(empty($products))
      <p>{{ __('No cider products found for this brand.', 'sage') }}</p>
    @else
      <p class="description">{{ __('Drag rows to change PDF order. Coming soon products render as full cards; out of stock products go in the footer strip.', 'sage') }}</p>
      <ul class="pbc-sell-sheets__list" data-sell-sheet-sortable>
        @foreach($products as $product)
          <li class="pbc-sell-sheets__item is-{{ $product['status'] }}">
            <span class="pbc-sell-sheets__handle dashicons dashicons-menu" aria-hidden="true"></span>
            <label class="pbc-sell-sheets__check">
              <input type="checkbox" name="product_ids[]" value="{{ (int) $product['id'] }}" />
              @if($product['thumbnail'])
                <img src="{{ esc_url($product['thumbnail']) }}" alt="" width="48" height="48" />
              @else
                <span class="pbc-sell-sheets__placeholder"></span>
              @endif
              <span class="pbc-sell-sheets__meta">
                <span class="pbc-sell-sheets__name" style="color: {{ esc_attr($product['accent']) }}">{!! $product['name_html'] !!}</span>
                <span class="pbc-sell-sheets__status">{{ esc_html(ucwords(str_replace('_', ' ', $product['status']))) }}</span>
                @if(!empty($product['warnings']))
                  <span class="pbc-sell-sheets__warn">{{ esc_html(implode(' · ', $product['warnings'])) }}</span>
                @endif
              </span>
            </label>
            @if($product['edit_link'])
              <a class="pbc-sell-sheets__edit" href="{{ esc_url($product['edit_link']) }}">{{ __('Edit', 'sage') }}</a>
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  </form>
</div>
