{{--
  Template Name: About Page
--}}

@php

  $heading_image = get_field("header_image", 'option');
  $title = get_field("header_title", 'option');

  if (is_null($title)) {
      $title = get_the_title();
  }

@endphp

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    {{-- @include('partials.page-header') --}}
    @include('partials.content-page')
    <div id="hero">
      <a href="/"><img class="logo" src="@asset('images/logo-thick.svg')" /></a>
      <h2>
          {{ $title }}
      </h2>
      <img class="full-bg" src="{{ $image }}" />
    </div>
    <section class="intro">
      <h4>{{ $intro_paragraph }}</h4>
    </section>
    <section class="story">
      <div class="gallery slideshow">
      @foreach ($full_width_images as $item)
        <div class="slide-item">
          <img class="cider-slide" src="{{ $item }}" />
        </div>
      @endforeach
      </div>
      <div class="content">
        <h3>{{ $story_title }}</h3>
        <div>{!! $story_text !!}</div>
      </div>
    </section>
    <section class="quote">
      <blockquote>{{ $big_quote }}
        @if($quote_source)
          <span>- {{ $quote_source }}</span>
        @endif
      </blockquote>
    </section>
    <section class="features">
      @foreach ($feature_blocks as $item)
        <div class="feature-card">
          <img class="" src="{{ $item->image }}" />
          <div class="content">
            <h4>{{ $item->feature_title }}</h4>
            <p>{{ $item->feature_text }}</p>
          </div>
        </div>
      @endforeach
    </section>
    <section class="social">
      <h3>Follow along</h3>
      <p>Our journey has just begun and we still have a lot to do! Follow us for product and orchard updates on Instagram and Facebook.</p>
      <ul class="social-buttons">
        <li><a href=""><img src="@asset('images/facebook.svg')" /></a></li>
        <li><a href=""><img src="@asset('images/instagram.svg')" /></a></li>
      </ul>
      @php dynamic_sidebar('sidebar-footer') @endphp
    </section>
    <section class="contact row">
      <div class="col-half col1">
        <h2>Look around.</h2>
        <h2>Try our cider.</h2>
        <h2>Send us a message!</h2>
      </div>
      <div class="col-half col2">
        <h4>Where you can find us</h4>
        <p>For now, you can find us at Bountiful Farmers' Market in Edmonton, Fri-Sun every weekend. Follow us on facebook or instagram for more info!</p>
        @php gravity_form( 1, false, false, false, '', true, 12 );@endphp
        {{-- <form class="dark">
          <input type="text" placeholder="Full Name" />
          <input type="email" placeholder="Email Adress" />
          <textarea placeholder="Your Message"></textarea>
          <button type="button" value="submit">Send</button>
        </form> --}}
      </div>
    </section>
  @endwhile
@endsection
