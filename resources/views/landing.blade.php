{{--
  Template Name: Landing Page
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    {{-- @include('partials.page-header') --}}
    @include('partials.content-page')
    <div id="slideshow">
      <img class="full-bg" src="{{ $colour_scheme['image']['url'] }}" />
      <img class="logo" src="@asset('images/PBCco-Logo.svg')" />
      <h3>
        <span style="background-color: var(--color-scheme); box-shadow: 10px 0 0px 0px var(--color-scheme), -10px 0 0px 0px var(--color-scheme);">{{ $call_to_action }}</span>
      </h3>
    </div>
  @endwhile
@endsection
