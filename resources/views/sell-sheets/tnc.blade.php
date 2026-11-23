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
      background: #f3f3f3;
    }
    .page {
      width: 8.5in;
      height: 11in;
      page-break-after: always;
      position: relative;
      overflow: hidden;
      background: #f3f3f3;
    }
    .page.last { page-break-after: auto; }
    .inner {
      padding: 0.32in 0.48in 1.45in;
    }
    .header {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 0.12in;
    }
    .header td { vertical-align: middle; }
    .hdr-side {
      width: 36%;
      font-size: 7.5pt;
      letter-spacing: 0.14em;
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
    .logo-img { height: 0.95in; width: auto; }
    .logo-fallback .mark {
      font-family: Pitch, sans-serif;
      font-weight: bold;
      font-size: 34pt;
      letter-spacing: -0.02em;
      color: #2f2f2f;
      line-height: 0.9;
    }
    .logo-fallback .sub {
      font-size: 7pt;
      letter-spacing: 0.28em;
      color: #2f2f2f;
      margin-top: 2px;
    }
    .grid {
      width: 100%;
      border-collapse: collapse;
    }
    .grid > tbody > tr > td {
      width: 50%;
      vertical-align: top;
      padding: 0.06in 0.08in 0.1in;
    }
    .card {
      width: 100%;
      border-collapse: collapse;
    }
    .card .can {
      width: 40%;
      text-align: center;
      vertical-align: top;
      padding-right: 0.06in;
    }
    .card .can img {
      width: 1.28in;
      height: 1.95in;
      object-fit: contain;
    }
    .card .can .missing {
      width: 1.15in;
      height: 1.8in;
      background: #e6e6e6;
      margin: 0 auto;
    }
    .card .copy {
      width: 60%;
      vertical-align: top;
      padding-top: 0.04in;
    }
    .card .product-name {
      font-family: Wanderlust, serif;
      font-size: 18pt;
      font-weight: 400;
      line-height: 0.95;
      margin: 0 0 0.05in;
    }
    .card .desc {
      font-size: 7.5pt;
      line-height: 1.32;
      color: #3a3a3a;
      margin-bottom: 0.08in;
    }
    .meta {
      display: inline-block;
      font-size: 7.5pt;
      color: #555;
      border-bottom: 1px solid #bdbdbd;
      padding-bottom: 2px;
      margin-bottom: 0.06in;
    }
    .prices {
      font-size: 8pt;
      line-height: 1.4;
      color: #222;
    }
    .oos-wrap {
      margin-top: 0.12in;
    }
    .oos-label {
      font-size: 9pt;
      margin-bottom: 0.06in;
    }
    .oos {
      width: 100%;
      border-collapse: collapse;
    }
    .oos td {
      vertical-align: middle;
      width: 33%;
      padding: 0.03in 0.08in 0.03in 0;
    }
    .oos img {
      width: 0.5in;
      height: 0.75in;
      object-fit: contain;
      vertical-align: middle;
    }
    .oos .oos-name {
      font-family: Wanderlust, serif;
      font-size: 12pt;
      font-weight: 400;
      padding-left: 0.05in;
    }
    .oos .soon {
      font-size: 8pt;
      color: #666;
      padding-left: 0.05in;
    }
    .footer {
      position: absolute;
      left: 0.48in;
      right: 0.48in;
      bottom: 0.28in;
      text-align: center;
    }
    .footnote {
      font-size: 8pt;
      color: #555;
      margin-bottom: 0.1in;
    }
    .contact-bar {
      font-size: 8.5pt;
      margin-bottom: 0.06in;
      color: #333;
    }
    .contacts {
      font-family: Wanderlust, serif;
      font-size: 20pt;
      font-weight: 400;
      color: #2f2f2f;
      line-height: 1;
      margin-bottom: 0.03in;
    }
    .email {
      font-size: 9pt;
      color: #333;
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
                <div class="mark">TNC.</div>
                <div class="sub">TRUE NORTH CIDER</div>
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
                      @if($product['description'])
                        <div class="desc">{{ $product['description'] }}</div>
                      @endif
                      @if($product['abv'] || $product['volume'])
                        <div class="meta">{{ trim($product['abv'].'   '.$product['volume']) }}</div>
                      @endif
                      @if($product['status'] === 'coming_soon' && empty($product['prices']))
                        <div class="prices">On its way!</div>
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
