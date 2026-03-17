<!doctype html>
<html {!! get_language_attributes() !!}>
  @include('partials.head')
  <body @php body_class() @endphp>

    @php do_action('get_header') @endphp
    
    {{-- Mobile Navigation --}}
    <nav class="nav-mobile">
      <input class="side-menu" type="checkbox" id="side-menu"/>
      <label class="hamb" for="side-menu" @if(is_front_page())style="background-color: var(--color-scheme);"@endif">
        <span class="hamb-line"></span>
        <span class="nav-title">Menu</span>
      </label>
      <nav class="side-nav col-md" role="navigation">
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
    </nav>

    {{-- Desktop Navigation --}}
    @if($minimal_header)
      @php
          $minimal_header_class = "minimal_header"
      @endphp
    @endif
    <nav class="nav-desktop {{ $minimal_header_class ?? '' }}" role="navigation">
      @if(is_front_page() || is_page('cocktail-book') )
        <a href="/"><img class="farmcidery" src="@asset('images/farmandcidery.svg')" /></a>
      @else
        <a class="home" href="/"><img class="farmcidery" src="@asset('images/logo-square-small.svg')" /></a>
      @endif
      @if (has_nav_menu('primary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav-top']) !!}
      @endif
      <a class="cart" href="/cart"><img src="@asset('images/cart.svg')" /></a>
    </nav>

    {{-- @include('partials.header') --}}
    <div class="wrap container" role="document">
      @yield('content')
      <div id="stockists" class="stockists modalDialog">
            {{-- <a href="#" class="close" onclick="return false;">close</a> --}}
        <div class="stockist-container">
          {{-- <a href="#close" title="Close" class="close">Close</a> --}}
          {{-- <p>We're slowly but surely getting our cider into the best bottle shops around! If you'd like your local to carry us, let them know and ask them to get in touch!</p>
          <div class="stockist-content">
            <p>{!! isset($acf_options->stockists) ? $acf_options->stockists : '' !!}</p>
          </div> --}}
          <div class="map-container">
            <div class="filter-bar">
              <h2>Stockists</h2>
              <p>We're slowly but surely getting our cider into the best bottle shops around! If you'd like your local to carry us, let them know and ask them to get in touch!</p>
              {!! facetwp_display( 'facet', 'brand' ) !!}
              {!! facetwp_display( 'template', 'stockists' ) !!}
            </div>
            <div class="map">
              {!! facetwp_display( 'facet', 'map' ) !!}
            </div>
          </div>
        </div>
      </div>

    </div>
    
    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp
    <a href="#close" title="Close" class="close-product">Close</a>

  </body>
</html>
