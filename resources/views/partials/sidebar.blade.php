@php dynamic_sidebar('sidebar-primary') @endphp
@if (has_nav_menu('primary_navigation'))
  {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
@endif