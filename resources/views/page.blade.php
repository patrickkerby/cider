@extends('layouts.app')

@php
  $is_fp = is_front_page();
@endphp

@if($is_fp)
  @section('content')
    @while(have_posts()) @php the_post() @endphp
      {{-- Front Page / Homepage --}}
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

        {{-- Display WooCommerce products using shortcode --}}
        <section class="shop row justify-content-center">
          <div class="col-12">
            {!! do_shortcode('[products limit="24" columns="3" orderby="date" order="desc"]') !!}
          </div>
        </section>
        
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
  @endsection
@else
  {{-- Regular Pages --}}
  @section('content')
    @while(have_posts()) @php the_post() @endphp
      <div id="hero">
        {{-- <a href="/"><img class="logo" src="@asset('images/logo-thick.svg')" /></a> --}}
        <h2>
          {{ $header_title ?? get_the_title() }}
        </h2>
        @if(isset($image) && $image)
        <img class="full-bg" src="{{ $image }}" />
        @endif
      </div>
      <section class="content">
        @include('partials.content-page')
      </section>
    @endwhile
  @endsection
@endif
