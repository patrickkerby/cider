@php
  $cart_count = (function_exists('WC') && WC()->cart)
    ? WC()->cart->get_cart_contents_count()
    : 0;
  $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
@endphp
<a class="cart cart-icon{{ !empty($cart_icon_modifier) ? ' ' . $cart_icon_modifier : '' }}" href="{{ esc_url($cart_url) }}">
  <img src="@asset('images/cart.svg')" alt="{{ __('Cart', 'sage') }}" />
  <span class="cart-icon__count">{{ $cart_count }}</span>
</a>
