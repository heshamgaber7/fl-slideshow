jQuery(function($) {
	'use strict';

    
    function fl_carousel(element) {

        // RTL Carousel
        var rtlVal = false;
        if ( $('body').hasClass('rtl') ) {
            rtlVal = true;
        }
    	var OWLsettings = {
            items: 1,
            loop: true,
            nav: true,
            dots: true,
            rtl: rtlVal,
            autoHeight: true,
            margin: 0,
        };

        // Animation
        if ( element.hasClass('fade-out') ) { OWLsettings.animateOut = 'fadeOut'; }
        if ( element.hasClass('slide-up') ) { OWLsettings.animateOut = 'slideUp'; }
        if ( element.hasClass('slide-down') ) { OWLsettings.animateOut = 'slideDown'; }

	    element.owlCarousel(OWLsettings);
    }

	// Apply Carousel
    if ( $('.fl-slideshow').length ) {
    	$.each( $('.fl-slideshow'), function() {
			fl_carousel($(this));
    	});		
	}
});