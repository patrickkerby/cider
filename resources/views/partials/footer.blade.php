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
      <form action="https://prairiebearscider.us14.list-manage.com/subscribe/post?u=e6ab7586e5e3358172da22416&amp;id=2d26c787b7" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
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
          <div class="response" id="mce-success-response" style="display:none"></div>
	</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
          <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_e6ab7586e5e3358172da22416_2d26c787b7" tabindex="-1" value=""></div>
          <div class="field submit">
            <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button">
          </div>
        </div>
      </form>
    </div>
    <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';fnames[3]='ADDRESS';ftypes[3]='address';fnames[4]='PHONE';ftypes[4]='phone';fnames[5]='BIRTHDAY';ftypes[5]='birthday';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
  </div>
</footer>
  <div class="colophon">
    <img src="@asset('images/PBCco-Logo-inline.svg')" />
  </div>
