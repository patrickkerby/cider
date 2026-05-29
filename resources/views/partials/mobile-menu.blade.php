@php
  $mobile_menu_location = has_nav_menu('mobile_navigation') ? 'mobile_navigation' : 'primary_navigation';
@endphp

<input class="side-menu" type="checkbox" id="side-menu" aria-hidden="true" />

@if (!function_exists('is_cart') || !is_cart())
  @include('partials.cart-icon', ['cart_icon_modifier' => 'cart-icon--mobile'])
@endif

<label class="hamb" for="side-menu">
  <span class="hamb-line" aria-hidden="true"></span>
  <span class="nav-title">{{ __('Menu', 'sage') }}</span>
  <span class="screen-reader-text">{{ __('Toggle menu', 'sage') }}</span>
</label>

<div class="mobile-menu-panel" role="navigation" aria-label="{{ __('Mobile menu', 'sage') }}">
  <a class="mobile-menu__logo" href="{{ esc_url(home_url('/')) }}">
    <img src="@asset('images/logo-square-small.svg')" alt="{{ esc_attr(get_bloginfo('name')) }}" />
  </a>

  @if (has_nav_menu($mobile_menu_location))
    {!! wp_nav_menu([
      'theme_location' => $mobile_menu_location,
      'menu_class' => 'mobile-nav',
      'container' => false,
    ]) !!}
  @endif
</div>
