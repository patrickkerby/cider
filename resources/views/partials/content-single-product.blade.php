{{-- Single Product Content --}}

<div id="hero">
  <h2>{{ get_the_title() }}</h2>
  @if(has_post_thumbnail())
    <img class="full-bg" src="{{ get_the_post_thumbnail_url(get_the_ID(), 'large') }}" alt="{{ get_the_title() }}" />
  @endif
</div>

<section class="content single-product-content">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="product-details">
        {{-- Product Image Gallery --}}
        <div class="product-images">
          @php woocommerce_show_product_images() @endphp
        </div>

        {{-- Product Summary (Price, Add to Cart, etc) --}}
        <div class="product-summary">
          @php woocommerce_template_single_title() @endphp
          @php woocommerce_template_single_price() @endphp
          @php woocommerce_template_single_excerpt() @endphp
          @php woocommerce_template_single_add_to_cart() @endphp
          @php woocommerce_template_single_meta() @endphp
        </div>
      </div>

      {{-- Product Description & Additional Info Tabs --}}
      <div class="product-tabs">
        @php woocommerce_output_product_data_tabs() @endphp
      </div>

      {{-- Related Products --}}
      @php woocommerce_output_related_products() @endphp
    </div>
  </div>
</section>

