{{--
  Template Name: Cider Cart Landing Page
--}}

@php

  $heading_image = get_field("header_image");
  $title = get_field("header_title");

  if (is_null($title)) {
      $title = get_the_title();
  }

@endphp

@extends('layouts.cidercart')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    {{-- @include('partials.page-header') --}}
      <h1 class="hidden">
          {{ $title }}
      </h1>
    <section class="landing-page">
        @include('partials.content-page')
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

