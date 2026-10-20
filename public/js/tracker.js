(function() {
    // Generate UUID v4
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // Cookie utilities
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/; SameSite=Lax";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    // Initialize IDs
    var visitorId = getCookie('jhurio_vid');
    if (!visitorId) {
        visitorId = uuidv4();
        setCookie('jhurio_vid', visitorId, 365);
    }

    var sessionId = getCookie('jhurio_sid');
    if (!sessionId) {
        sessionId = uuidv4();
    }
    // Refresh session cookie expiry to 30 mins
    setCookie('jhurio_sid', sessionId, 1/48);

    // Track function
    window.jhurioTrack = function(eventType, data = {}) {
        var payload = {
            visitor_id: visitorId,
            session_id: sessionId,
            url: window.location.href,
            referrer: document.referrer,
            screen_size: window.innerWidth + 'x' + window.innerHeight,
            language: navigator.language || navigator.userLanguage,
            events: [
                {
                    event_type: eventType,
                    product_id: data.product_id || null,
                    category_id: data.category_id || null,
                    search_keyword: data.search_keyword || null,
                    metadata: data.metadata || null
                }
            ]
        };

        fetch('/api/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ payload: payload })
        }).catch(err => console.error('Tracking error:', err));
    };

    // Auto-track page view
    window.addEventListener('load', function() {
        window.jhurioTrack('page_view');
    });

})();
