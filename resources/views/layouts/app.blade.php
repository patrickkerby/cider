<!doctype html>
<html {!! get_language_attributes() !!}>
  @include('partials.head')
  <style>
    :root { 
      --color-scheme: @php echo $colour_scheme['colour']; @endphp;
    }
  </style>
  <body @php body_class() @endphp>

    @php do_action('get_header') @endphp
    {{-- @include('partials.header') --}}
    <div class="wrap container" role="document">
      @yield('content')
    </div>
    {{-- Hamburger Icon --}}
    <input class="side-menu" type="checkbox" id="side-menu"/>
    <label class="hamb" for="side-menu">
      <span class="hamb-line"></span>
      <span class="nav-title">Menu</span>
    </label>
    <nav class="side-nav" role="navigation" style="background-color: var(--color-scheme);">
      @if (has_nav_menu('primary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
      @endif
    </nav>
    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp
  </body>
</html>
