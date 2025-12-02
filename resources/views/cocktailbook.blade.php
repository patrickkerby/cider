{{--
  Template Name: Cocktail Book Landing Page
--}}

@php

  $heading_image = get_field("header_image");
  $title = get_field("header_title");

  if (is_null($title)) {
      $title = get_the_title();
  }

@endphp

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
  
  <div id="hero">
        {{-- <a href="/"><img class="logo" src="@asset('images/logo-thick.svg')" /></a> --}}
        <h2>
          {!! $header_title !!}
        </h2>
        <img class="full-bg" src="{{ $image }}" />
      </div>

    <section class="woocommerce-product-block">
        @include('partials.content-page')
    </section>

    <!-- Excerpt Modal -->
    <div id="excerpt-modal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div class="modal-body">
              <img src="{{ get_field('book_excerpt')['url'] }}" alt="{{ get_field('book_excerpt')['alt'] }}" />
            </div>
        </div>
    </div>
    <div id="form-modal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div class="modal-body">
              <!-- @php gravity_form( 1, false, false, false, '', true, 12 );@endphp -->
               test
            </div>
        </div>
    </div>
    <section class="features">
        @foreach ($feature_textimage as $item)
            <div class="feature-card">
                <img src="{{ $item->photo->url }}" alt="{{ $item->photo->alt }}" />
                <div class="content">
                    <h4>{{ $item->title }}</h4>
                    {!! $item->text !!}
                </div>
            </div>  
        @endforeach
    </section>
    <section class="gallery">
        @foreach ($gallery as $image)
            <div class="gallery-item">
                <img src="{{ $image->url }}" alt="{{ $image->alt }}" />
            </div>
        @endforeach
    </section>

    
    {{-- <section class="social">
      <h3>Follow along</h3>
      <p>Our journey has just begun and we still have a lot to do! Follow us for product and orchard updates on Instagram and Facebook.</p>
      <ul class="social-buttons">
        <li><a href="https://instagram.com/prairiebearsciderco" target="_blank"><img src="@asset('images/facebook.svg')" /></a></li>
        <li><a href="https://www.facebook.com/profile.php?id=100060722582049" target="_blank"><img src="@asset('images/instagram.svg')" /></a></li>
      </ul>
      @php dynamic_sidebar('sidebar-footer') @endphp
    </section> --}}
  @endwhile
@endsection

