{{--
The Template for displaying all single products

This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@see 	    https://docs.woocommerce.com/document/template-structure/
@author 		WooThemes
@package 	WooCommerce/Templates
@version     1.6.4
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @php
      do_action('woocommerce_before_single_product');
      
      if (post_password_required()) {
        echo get_the_password_form();
        return;
      }
    @endphp

    <div id="product-{{ get_the_ID() }}" @php wc_product_class('', get_queried_object()) @endphp>
      <div id="hero">
        @php
          // Check for custom hero image (ACF field for future use)
          $custom_hero = get_field('product_hero_image');
        @endphp
        @if($custom_hero && isset($custom_hero['url']))
          <img class="full-bg" src="{{ $custom_hero['url'] }}" alt="{{ get_the_title() }}" />
        @else
          <img class="full-bg" src="@asset('images/sketch.svg')" alt="{{ get_the_title() }}" />
        @endif
      </div>

      <section class="content single-product-content">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="product-details">
              {{-- Product Image Gallery --}}
              <div class="product-images">
                @php do_action('woocommerce_before_single_product_summary') @endphp
              </div>

              {{-- Product Summary (Price, Add to Cart, etc) --}}
              <div class="product-summary summary entry-summary">
                @php do_action('woocommerce_single_product_summary') @endphp
              </div>
            </div>

            {{-- Product Description & Additional Info Tabs --}}
            <div class="product-tabs">
              @php do_action('woocommerce_after_single_product_summary') @endphp
            </div>
          </div>
        </div>
      </section>
    </div>

    @php do_action('woocommerce_after_single_product') @endphp
  @endwhile
@endsection
