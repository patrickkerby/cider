{{--
  Template Name: Events Home PAge
--}}

@extends('layouts.app')

@php

  $heading_image = get_field("landing_page_heading_header_image", 'option');
  $title = get_field("landing_page_heading_title", 'option');

  if (is_null($title)) {
    $title = get_the_title();
  }

@endphp

@dump()

@section('content')
  @while(have_posts()) @php the_post() @endphp
    {{-- @include('partials.page-header') --}}
    <div id="hero">
      <a href="/"><img class="logo" src="@asset('images/logo-thick.svg')" /></a>
      <h2>
        {{ $title }}
      </h2>
      <img class="full-bg" src="{{ $heading_image }}" />
    </div>

    <main id="tribe-events-pg-template" class="tribe-events-pg-template">

      <div id="primary" class="content-area col-md-12">
          <div id="content" class="site-content" role="main">
            @include('partials.content-page')

              <?php while (have_posts()) : the_post(); ?>
      
                  <?php get_template_part( 'content', 'page' ); ?>
      
                  <?php
                  // If comments are open or we have at least one comment, load up the comment template
                  if ( comments_open() || '0' != get_comments_number() )
                      comments_template( '', true );
                  ?>
      
              <?php endwhile; // end of the loop. ?>
      
          </div><!-- #content .site-content -->
      </div><!-- #primary .content-area -->
    </main>

  @endwhile
@endsection
    