@php
  $GLOBALS['pbc_rendering_home_product_grid'] = true;
  \App\pbc_render_home_product_filters();
@endphp
<div class="pbc-home-product-grid">
  {!! do_shortcode('[products limit="-1" columns="3" category="cider" orderby="menu_order" order="ASC"]') !!}
</div>
@php unset($GLOBALS['pbc_rendering_home_product_grid']); @endphp
