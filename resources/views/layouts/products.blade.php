<!doctype html>
<html {!! get_language_attributes() !!}>
  @include('partials.head')
  <body @php body_class() @endphp>
    @php do_action('get_header') @endphp
      @include('partials.header')
    <div class="wrap container" role="document">
<h1>TEST TEST</h1>
      <main class="main row no-gutters justify-content-center">
        <div class="col-sm-12">
          @yield('content')
        </div>
      </main>
    </div>
    @php do_action('get_footer') @endphp
    @include('partials.footer')
    @php wp_footer() @endphp
  </body>
</html>
