<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">

  @php wp_head() @endphp
  
  <style>
    :root { 
      --color-scheme: @php echo isset($colour_scheme['colour']) ? $colour_scheme['colour'] : 'rgba(255, 255, 255, 1)'; @endphp;
    }
  </style>

</head>