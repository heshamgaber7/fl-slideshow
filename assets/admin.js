jQuery(function($) {
	'use strict';

	// Slideshow Management
    var slideshow = $('.fl-slideshow-imgs');

    var FLSlideshowMediaControl = {

        // Init a new media manager or returns existing frame
        frame: function() {
            if ( this._frame ) {
                return this._frame;
            }

            this._frame = wp.media({
                title: FL_Slideshow.media_title,
				library: {
					type: 'image'
				},
				button: {
					text: FL_Slideshow.media_button
				},
				multiple: true
            });

            this._frame.on('open', this.updateFrame).state('library').on('select', this.select);

            return this._frame;
        },

        select: function() {
            var selection = this.get('selection');

            selection.each(function(model) {
                var thumbnail = model.attributes.url;
                if ( model.attributes.sizes !== undefined && model.attributes.sizes.thumbnail !== undefined ) {
                    thumbnail = model.attributes.sizes.thumbnail.url;
                }
                slideshow.append('<span data-id="' + model.id + '" title="' + model.attributes.title + '"><img src="' + thumbnail + '" alt="" /><span class="close"></span></span>');
                slideshow.trigger('update');
            });
        },

        updateFrame: function() {
        },

        init: function() {
            $('#wpbody').on('click', '#fl-slideshow-button', function(e){
                e.preventDefault();
                FLSlideshowMediaControl.frame().open();
            });
        }
    };
    
    FLSlideshowMediaControl.init();

    slideshow.on('update', function(){
        var ids = [];
        $(this).find('> span').each(function(){
            ids.push($(this).data('id'));
        });
        $('[name="fl_slideshow_imgs"]').val(ids.join(','));
    });

    slideshow.sortable({
        placeholder: "fl-state-highlight",
        revert: 200,
        tolerance: 'pointer',
        stop: function () {
            slideshow.trigger('update');
        }
    });

    slideshow.on('click', 'span.close', function(){
        $(this).parent().fadeOut(200, function(){
            $(this).remove();
            slideshow.trigger('update');
        });
    });

});
