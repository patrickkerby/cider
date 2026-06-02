@php
  $book_id = function_exists('App\pbc_cocktail_book_product_id') ? \App\pbc_cocktail_book_product_id() : 0;
  $book = $book_id && function_exists('wc_get_product') ? wc_get_product($book_id) : null;
  $book_url = home_url('/cocktail-book/');
  $book_title = $book ? $book->get_name() : __('Cocktail Recipe Book', 'sage');
  $book_excerpt = $book ? $book->get_short_description() : '';
@endphp

@if($book)
<section class="pbc-home-book-cta row justify-content-center">
  <div class="col-12">
    <div class="pbc-home-book-cta__inner">
      <a class="pbc-home-book-cta__media" href="{{ esc_url($book_url) }}">
        @if($book->get_image_id())
          {!! wp_get_attachment_image($book->get_image_id(), 'large', false, [
            'class' => 'pbc-home-book-cta__image',
            'alt' => esc_attr(wp_strip_all_tags($book_title)),
            'loading' => 'lazy',
          ]) !!}
        @endif
      </a>
      <div class="pbc-home-book-cta__copy">
        <p class="pbc-home-book-cta__eyebrow">{{ __('A whole other passion project', 'sage') }}</p>
        <h2 class="pbc-home-book-cta__title">{!! $book_title !!}</h2>
        @if($book_excerpt)
          <div class="pbc-home-book-cta__text">{!! wp_kses_post($book_excerpt) !!}</div>
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
