{{--
  Template Name: Landing Page
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    {{-- @include('partials.page-header') --}}
    @include('partials.content-page')
    <img class="logo" src="@asset('images/PBCco-Logo.svg')" />
    <div id="slideshow">
   
      <img class="full-bg" src="{{ $colour_scheme['image']['url'] }}" />
   
    </div>
  @endwhile
@endsection
