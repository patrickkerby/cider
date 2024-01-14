@extends('layouts.app')

@if(is_front_page())
  @section('content')
    @while(have_posts()) @php the_post() @endphp
      {{-- @include('partials.page-header') --}}
      <div id="slideshow">
        <img class="full-bg" src="{{ $colour_scheme['image']['url'] }}" />
        <img class="logo" src="@asset('images/PBCco-Logo.svg')" />
        <h3>
          <span style="background-color: var(--color-scheme); box-shadow: 10px 0 0px 0px var(--color-scheme), -10px 0 0px 0px var(--color-scheme);">{{ $call_to_action }}</span>
        </h3>
      </div>
      
      <div class="content-wrap">
        
        <section class="shop row justify-content-center">
          <div class="col-12">
            <h2>Online ordering + delivery available in the Edmonton area!</h2>
            @include('partials.content-page')
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
            @php gravity_form( 1, false, false, false, '', true, 12 );@endphp
          </div>
        </section>
      </div>
    @endwhile
  @endsection
@else  
  
  @section('content')
    @while(have_posts()) @php the_post() @endphp
      <div id="hero">
        <a href="/"><img class="logo" src="@asset('images/logo-thick.svg')" /></a>
        <h2>
          {{ $header_title }}
        </h2>
        <img class="full-bg" src="{{ $image }}" />
      </div>
      <section class="content">
        @include('partials.content-page')
      </section>
    @endwhile
  @endsection
@endif
