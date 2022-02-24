<footer class="content-info">
  {{-- @php dynamic_sidebar('sidebar-footer') @endphp --}}
  <div class="news-slider">
    <h2>Stuff that's <span>goin' on</span></h2>
    <div class="slider">
      <div class="fade">
        @if($acf_options->news_feed)
          @foreach ($acf_options->news_feed as $item)
            <div>
              <p>{{ $item->news_item }}</p>
            </div>
          @endforeach
        @endif                
      </div>
    </div>
  </div>

  <div class="signup">
    <h4>Keep up with new products &amp; farm updates!</h4>
    <!-- Begin Mailchimp Signup Form -->
    <div id="mc_embed_signup">
      <form action="" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
        <div id="mc_embed_signup_scroll" class="form-container">
          <div class="name-field field">
            <input type="text" value="" placeholder="First Name" name="FNAME" class="required" id="mce-FNAME">
            <input type="text" value="" placeholder="Last Name" name="LNAME" class="required" id="mce-LNAME">
          </div>
          <div class="email-field field">        
            <input type="email" value="" placeholder="Email Address" name="EMAIL" class="required email" id="mce-EMAIL">
          </div>
          <div id="mce-responses" class="clear">
            <div class="response" id="mce-error-response" style="display:none"></div>
            <div class="response" id="mce-success-response" style="display:none"></div>
          </div>
          <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
          <div style="position: absolute; left: -5000px;" aria-hidden="true">
            <input type="text" name="b_f07999ddf32b42be0af661143_9fe85973c9" tabindex="-1" value="">
          </div>
          <div class="field submit">
            <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button">
          </div>
        </div>
      </form>
    </div>
  </div>
</footer>
  <div class="colophon">
    <img src="@asset('images/PBCco-Logo-inline.svg')" />
  </div>
