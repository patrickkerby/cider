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
    <nav class="nav-desktop" role="navigation">
      @if (has_nav_menu('primary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav-top']) !!}
      @endif
    </nav>

    {{-- @include('partials.header') --}}
    <div class="wrap container" role="document">
      @yield('content')

      <div id="cidercart-contact" class="modalDialog">
        <div class="stockist-container">
          <a href="#close" title="Close" class="close">Close</a>
          <h2>Get a quote for the Cider Cart!</h2>
          <p>Tell us more about your event and we'll get back to you as soon as possible!</p>
          <div class="stockist-content">
            @php gravity_form( 1, false, false, false, '', true, 12 );@endphp
          </div>
        </div>
      </div>

      <div id="stockists" class="modalDialog">
        <div class="stockist-container">
          <a href="#close" title="Close" class="close">Close</a>
          <h2>Stockists</h2>
          <p>We're slowly but surely getting our cider into the best bottle shops around! If you'd like your local to carry us, let them know and ask them to get in touch!</p>
          <div class="stockist-content">
            <p>{!! $acf_options->stockists !!}</p>
          </div>
        </div>
      </div>

    </div>

    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp
    <a href="#" class="close-product" onclick="return false;">close</a>

   

  </body>
</html>
