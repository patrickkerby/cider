<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <style>
    @@page { margin: 0; size: letter portrait; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: Pitch, DejaVu Sans, sans-serif;
      color: #333;
      font-size: 9pt;
    }
    .page {
      width: 8.5in;
      height: 11in;
      page-break-after: always;
      position: relative;
      overflow: hidden;
    }
    .page.last { page-break-after: auto; }
    .inner {
      padding: 0.38in 0.48in 1.55in;
    }
    .header {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 0.18in;
    }
    .header td { vertical-align: middle; }
    .hdr-side {
      width: 36%;
      font-size: 7.5pt;
      letter-spacing: 0.12em;
      color: #4a4a4a;
      text-transform: uppercase;
    }
    .hdr-left { text-align: left; }
    .hdr-right { text-align: right; }
    .hdr-line {
      border-bottom: 1px solid #222;
      margin-top: 5px;
    }
    .hdr-logo { width: 28%; text-align: center; }
    .logo-img { height: 1.15in; width: auto; }
    .logo-fallback {
      width: 1.18in;
      height: 1.18in;
      background: #8a8c3a;
      color: #fff;
      margin: 0 auto;
    }
    .logo-fallback .word {
      font-family: Wanderlust, serif;
      font-size: 16pt;
      font-weight: 400;
      line-height: 0.95;
      padding-top: 0.22in;
      text-align: center;
    }
    .logo-fallback .co {
      font-family: Pitch, sans-serif;
      font-size: 7pt;
      letter-spacing: 0.08em;
      display: block;
      margin-top: 2px;
    }
    .grid {
      width: 100%;
      border-collapse: collapse;
    }
    .grid > tbody > tr > td {
      width: 50%;
      vertical-align: top;
      padding: 0.12in 0.08in 0.2in;
    }
    .card {
      width: 100%;
      border-collapse: collapse;
    }
    .card .can {
      width: 42%;
      text-align: center;
      vertical-align: top;
      padding-right: 0.08in;
    }
    .card .can img {
      width: 1.55in;
      height: 2.35in;
      object-fit: contain;
    }
    .card .can .missing {
      width: 1.4in;
      height: 2.2in;
      background: #f0eee8;
      margin: 0 auto;
    }
    .card .copy {
      width: 58%;
      vertical-align: top;
      padding-left: 0.04in;
    }
    .card .product-name {
      font-family: Wanderlust, serif;
      font-size: 22pt;
      font-weight: 400;
      line-height: 0.95;
      margin: 0.04in 0 0.06in;
    }
    .card .subtitle {
      font-size: 8pt;
      letter-spacing: 0.04em;
      margin-bottom: 0.06in;
      color: #666;
    }
    .card .desc {
      font-size: 8pt;
      line-height: 1.35;
      color: #3a3a3a;
      margin-bottom: 0.1in;
    }
    .meta {
      display: inline-block;
      color: #fff;
      font-size: 7.5pt;
      padding: 3px 8px;
      margin-bottom: 0.08in;
    }
    .prices {
      border-top: 1px solid #c8c8c8;
      padding-top: 5px;
      font-size: 8.5pt;
      line-height: 1.45;
      color: #222;
    }
    .oos-wrap {
      margin-top: 0.15in;
      padding-top: 0.08in;
    }
    .oos-label {
      font-size: 9pt;
      margin-bottom: 0.08in;
      color: #444;
    }
    .oos {
      width: 100%;
      border-collapse: collapse;
    }
    .oos td {
      vertical-align: middle;
      padding: 0.04in 0.1in 0.04in 0;
      width: 33%;
    }
    .oos img {
      width: 0.55in;
      height: 0.85in;
      object-fit: contain;
      vertical-align: middle;
    }
    .oos .oos-name {
      font-family: Wanderlust, serif;
      font-size: 13pt;
      font-weight: 400;
      display: inline-block;
      vertical-align: middle;
      padding-left: 0.06in;
    }
    .oos .soon {
      font-size: 8pt;
      color: #666;
      padding-left: 0.06in;
    }
    .footer {
      position: absolute;
      left: 0.48in;
      right: 0.48in;
      bottom: 0.32in;
      text-align: center;
    }
    .footnote {
      font-size: 8pt;
      color: #555;
      margin-bottom: 0.12in;
    }
    .contact-bar {
      background: #8a8c3a;
      color: #fff;
      display: inline-block;
      padding: 5px 18px;
      font-size: 8.5pt;
      letter-spacing: 0.02em;
      margin-bottom: 0.1in;
    }
    .contacts {
      font-family: Wanderlust, serif;
      font-size: 22pt;
      font-weight: 400;
      color: #5f6428;
      line-height: 1;
      margin-bottom: 0.04in;
    }
    .email {
      font-size: 9pt;
      font-style: italic;
      color: #8a8c3a;
    }
  </style>
</head>
<body>
@foreach($pages as $pageIndex => $page)
  <div class="page {{ $pageIndex === count($pages) - 1 ? 'last' : '' }}">
    <div class="inner">
      <table class="header">
        <tr>
          <td class="hdr-side hdr-left">
            {{ $settings['season'] }}
            <div class="hdr-line"></div>
          </td>
          <td class="hdr-logo">
            @if(!empty($settings['logo_uri']))
              <img class="logo-img" src="{{ $settings['logo_uri'] }}" alt="" />
            @else
              <div class="logo-fallback">
                <div class="word">Prairie<br>Bears<br>Cider<span class="co">CO</span></div>
              </div>
            @endif
          </td>
          <td class="hdr-side hdr-right">
            PRICE LIST / PRODUCT GUIDE
            <div class="hdr-line"></div>
          </td>
        </tr>
      </table>

      @php $rows = array_chunk($page['available'], 2); @endphp
      <table class="grid">
        @foreach($rows as $row)
          <tr>
            @foreach($row as $product)
              <td>
                <table class="card">
                  <tr>
                    <td class="can">
                      @if(!empty($product['image_uri']))
                        <img src="{{ $product['image_uri'] }}" alt="" />
                      @else
                        <div class="missing"></div>
                      @endif
                    </td>
                    <td class="copy">
                      <div class="product-name" style="color: {{ $product['accent'] }}; font-family: Wanderlust, serif; font-weight: 400;">{!! $product['name_html'] !!}</div>
                      @if($product['subtitle'])
                        <div class="subtitle" style="color: {{ $product['accent'] }};">{{ $product['subtitle'] }}</div>
                      @endif
                      @if($product['description'])
                        <div class="desc">{{ $product['description'] }}</div>
                      @endif
                      @if($product['abv'] || $product['volume'])
                        <div class="meta" style="background: {{ $product['accent'] }};">
                          {{ trim($product['abv'].'  '.$product['volume']) }}
                        </div>
                      @endif
                      @if($product['status'] === 'coming_soon' && empty($product['prices']))
                        <div class="prices">Back soon!</div>
                      @elseif(!empty($product['prices']))
                        <div class="prices">
                          @foreach($product['prices'] as $price)
                            <div>{{ $price['label'] }}@if($price['price']) - {{ $price['price'] }}@endif</div>
                          @endforeach
                        </div>
                      @endif
                    </td>
                  </tr>
                </table>
              </td>
            @endforeach
            @if(count($row) === 1)
              <td></td>
            @endif
          </tr>
        @endforeach
      </table>

      @if(!empty($page['oos']))
        <div class="oos-wrap">
          <div class="oos-label">Out of stock:</div>
          <table class="oos">
            @foreach(array_chunk($page['oos'], 3) as $oosRow)
              <tr>
                @foreach($oosRow as $product)
                  <td>
                    @if(!empty($product['image_uri']))
                      <img src="{{ $product['image_uri'] }}" alt="" />
                    @endif
                    <span class="oos-name" style="color: {{ $product['accent'] }};">{!! $product['name_html'] !!}</span>
                    <div class="soon">Back soon!</div>
                  </td>
                @endforeach
                @if(count($oosRow) < 3)
                  @for($i = count($oosRow); $i < 3; $i++)
                    <td></td>
                  @endfor
                @endif
              </tr>
            @endforeach
          </table>
        </div>
      @endif
    </div>

    <div class="footer">
      @if(!empty($settings['footer']))
        <div class="footnote">{{ $settings['footer'] }}</div>
      @endif
      <div class="contact-bar">Contact your brand representative:</div>
      <div class="contacts" style="font-family: Wanderlust, serif; font-weight: 400;">{{ $settings['contact'] }}</div>
      <div class="email">{{ $settings['email'] }}</div>
    </div>
  </div>
@endforeach
</body>
</html>
