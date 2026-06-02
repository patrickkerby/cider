@php
  $book_id = function_exists('App\pbc_cocktail_book_product_id') ? \App\pbc_cocktail_book_product_id() : 0;
  $book = $book_id && function_exists('wc_get_product') ? wc_get_product($book_id) : null;
  $book_url = home_url('/cocktail-book/');
  $book_image = $book ? wp_get_attachment_image_url($book->get_image_id(), 'woocommerce_thumbnail') : '';
  $book_title = $book ? $book->get_name() : __('Cocktail Recipe Book', 'sage');
  $book_excerpt = $book ? wp_strip_all_tags($book->get_short_description()) : '';
@endphp

@if($book)
<section class="pbc-home-book-cta row justify-content-center">
  <div class="col-12">
    <div class="pbc-home-book-cta__inner">
      @if($book_image)
        <a class="pbc-home-book-cta__media" href="{{ esc_url($book_url) }}">
          <img src="{{ esc_url($book_image) }}" alt="{{ esc_attr($book_title) }}" width="280" height="280" loading="lazy" />
        </a>
      @endif
      <div class="pbc-home-book-cta__copy">
        <p class="pbc-home-book-cta__eyebrow">{{ __('A whole other passion project', 'sage') }}</p>
        <h2 class="pbc-home-book-cta__title">{!! $book_title !!}</h2>
        @if($book_excerpt)
          <p class="pbc-home-book-cta__text">{{ $book_excerpt }}</p>
        @else
          <p class="pbc-home-book-cta__text">
            {{ __('Recipes, stories, and pairings — explore the book on its own page.', 'sage') }}
          </p>
        @endif
        <a class="pbc-home-book-cta__button button" href="{{ esc_url($book_url) }}">
          {{ __('View the Cocktail Book', 'sage') }}
        </a>
      </div>
    </div>
  </div>
</section>
@endif
