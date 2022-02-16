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
    <label class="hamb" for="side-menu"><span class="hamb-line"></span></label>
    <nav class="side-nav" role="navigation" style="background-color: var(--color-scheme);">
      <ul>
        <li>About</li>
        <li>Journal</li>
        <li>Shop</li>
      </ul>
    </nav>
    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp
  </body>
</html>
