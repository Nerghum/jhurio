@php
    $fbPixelsStr = \App\Models\Setting::get('fb_pixels', '[]');
    $fbPixels = json_decode($fbPixelsStr, true);
    if (!is_array($fbPixels)) $fbPixels = [];

    if (isset($categoryPixels) && is_array($categoryPixels)) {
        foreach ($categoryPixels as $cp) {
            $exists = false;
            foreach ($fbPixels as $fp) {
                if (isset($fp['pixel_id']) && $fp['pixel_id'] === $cp['pixel_id']) {
                    $exists = true; break;
                }
            }
            if (!$exists) {
                $fbPixels[] = $cp;
            }
        }
    }

    $gtmId = \App\Models\Setting::get('gtm_id');
    $ga4Id = \App\Models\Setting::get('ga4_id');
    $googleAdsId = \App\Models\Setting::get('google_ads_id');
    $tiktokPixelId = \App\Models\Setting::get('tiktok_pixel_id');
@endphp

<!-- Facebook Pixel Code -->
@if(count($fbPixels) > 0)
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        
        @foreach($fbPixels as $pixel)
            @if(!empty($pixel['pixel_id']))
                fbq('init', '{{ $pixel['pixel_id'] }}');
            @endif
        @endforeach
        
        fbq('track', 'PageView');
    </script>
    @foreach($fbPixels as $pixel)
        @if(!empty($pixel['pixel_id']))
            <noscript>
                <img height="1" width="1" style="display:none" 
                src="https://www.facebook.com/tr?id={{ $pixel['pixel_id'] }}&ev=PageView&noscript=1"/>
            </noscript>
        @endif
    @endforeach
@endif
<!-- End Facebook Pixel Code -->

<!-- Google Tag Manager -->
@if(!empty($gtmId))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
@endif
<!-- End Google Tag Manager -->

<!-- Google Analytics 4 -->
@if(!empty($ga4Id))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $ga4Id }}');
        @if(!empty($googleAdsId))
            gtag('config', '{{ $googleAdsId }}');
        @endif
    </script>
@endif
<!-- End Google Analytics 4 -->

<!-- TikTok Pixel -->
@if(!empty($tiktokPixelId))
    <script>
        !function (w, d, t) {
            w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
            
            ttq.load('{{ $tiktokPixelId }}');
            ttq.page();
        }(window, document, 'ttq');
    </script>
@endif
<!-- End TikTok Pixel -->

<script>
    window.trackEvent = function(eventName, properties = {}) {
        // Facebook Pixel
        if (typeof fbq === 'function') {
            fbq('track', eventName, properties);
        }
        
        // Google Analytics (GA4) / Google Ads
        if (typeof gtag === 'function') {
            let gaEventName = eventName;
            // Map FB events to GA4 standard events
            const gaMap = {
                'ViewContent': 'view_item',
                'AddToCart': 'add_to_cart',
                'InitiateCheckout': 'begin_checkout',
                'Purchase': 'purchase',
                'Search': 'search'
            };
            if (gaMap[eventName]) gaEventName = gaMap[eventName];
            gtag('event', gaEventName, properties);
        }

        // TikTok Pixel
        if (typeof ttq === 'function') {
            let ttqEventName = eventName;
            const ttqMap = {
                'ViewContent': 'ViewContent',
                'AddToCart': 'AddToCart',
                'InitiateCheckout': 'InitiateCheckout',
                'Purchase': 'CompletePayment',
                'Search': 'Search'
            };
            if (ttqMap[eventName]) ttqEventName = ttqMap[eventName];
            ttq.track(ttqEventName, properties);
        }

        // Google Tag Manager (Data Layer)
        if (typeof dataLayer !== 'undefined') {
            dataLayer.push({
                event: eventName,
                ecommerce: properties
            });
        }
        
        // Custom Jhurio Analytics Tracker
        if (typeof window.jhurioTrack === 'function') {
            const mappedEvent = {
                'ViewContent': 'product_view',
                'AddToCart': 'add_to_cart',
                'InitiateCheckout': 'checkout_start',
                'Purchase': 'purchase',
                'Search': 'search'
            }[eventName] || eventName.toLowerCase();
            
            // Extract product IDs for the backend if possible
            let productId = null;
            if (properties.content_ids && properties.content_ids.length > 0) {
                productId = properties.content_ids[0];
            }
            
            window.jhurioTrack(mappedEvent, {
                product_id: productId,
                metadata: properties
            });
        }
    };
</script>
