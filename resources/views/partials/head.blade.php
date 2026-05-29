<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">

  @php wp_head() @endphp
  
  @php
    $color_scheme_default = '#a89934';
    $color_scheme_value = $color_scheme_default;
    if (isset($colour_scheme['colour']) && $colour_scheme['colour'] !== '') {
      $color_scheme_value = $colour_scheme['colour'];
    }
  @endphp
  <style>
    :root {
      --color-scheme: {{ $color_scheme_value }};
    }
  </style>

</head>