@extends('layouts.app')

{{-- WooCommerce Shop Archive (also used for front page when it's set as shop page) --}}

@section('content')
  @php
    // Check if this is also the front page
    $is_front = is_front_page();
  @endphp

  @if($is_front)
    {{-- Front Page Content --}}
    @while(have_posts()) @php the_post() @endphp
      @if($colour_scheme && isset($colour_scheme['image']) && $colour_scheme['image'])
      <div id="slideshow">
        <img class="full-bg" src="{{ $colour_scheme['image']['url'] }}" />
        <img class="logo" src="@asset('images/PBCco-Logo.svg')" />
        <h3>
          <span>{{ $call_to_action ?? 'Welcome to Prairie Bears Cider Co.' }}</span>
        </h3>
      </div>
      @endif
      
      <div class="content-wrap">
        <section class="shop row justify-content-center">
          <div class="col-12">
            <h2>Online ordering + delivery available in the Edmonton area!</h2>
            @include('partials.content-page')
          </div>
        </section>

        {{-- Display WooCommerce products --}}
        @if(function_exists('woocommerce_product_loop_start') && have_posts())
          {!! woocommerce_product_loop_start() !!}
          @while(have_posts())
            @php
              the_post();
              wc_get_template_part('content', 'product');
            @endphp
          @endwhile
          {!! woocommerce_product_loop_end() !!}
        @endif
        
        <section class="contact row">
          <div class="col-half col1">
            <h2>Look around.</h2>
            <h2>Try our cider.</h2>
            <h2>Send us a message!</h2>
          </div>
          <div class="col-half col2">
            <h4>Where you can find us</h4>
            <p>Find us at markets around Edmonton (<a href="/events/">check out our market schedule</a>) or order online. Follow us on facebook or instagram for more info!</p>
            @php 
              if (function_exists('gravity_form')) {
                gravity_form( 1, false, false, false, '', true, 12 );
              }
            @endphp
          </div>
        </section>
      </div>
    @endwhile
  @else
    {{-- Regular Shop Page --}}
    @php do_action('woocommerce_before_main_content') @endphp
    
    @if(have_posts())
      @php do_action('woocommerce_before_shop_loop') @endphp
      {!! woocommerce_product_loop_start() !!}
      @while(have_posts())
        @php
          the_post();
          wc_get_template_part('content', 'product');
        @endphp
      @endwhile
      {!! woocommerce_product_loop_end() !!}
      @php do_action('woocommerce_after_shop_loop') @endphp
    @else
      @php do_action('woocommerce_no_products_found') @endphp
    @endif

    @php do_action('woocommerce_after_main_content') @endphp
  @endif
@endsection

