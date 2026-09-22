/*
 * imgPreview jQuery plugin
 * Copyright (c) 2009 James Padolsey
 * j@qd9.co.uk | http://james.padolsey.com
 * Dual licensed under MIT and GPL.
 * Updated: 09/02/09
 * @author James Padolsey
 * @version 0.22
 */
(function($){

    $.expr[':'].linkingToImage = function(elem, index, match){
        // This will return true if the specified attribute contains a valid link to an image:
        return !! ($(elem).attr(match[3]) && $(elem).attr(match[3]).match(/\.(gif|jpe?g|png|bmp)$/i));
    };

    $.fn.imgPreview = function(userDefinedSettings){

        var s = $.extend({

            /* DEFAULTS */

            // CSS to be applied to image:
            imgCSS: {},
            // Distance between cursor and preview:
            distanceFromCursor: {top:10, left:10},
            // Boolean, whether or not to preload images:
            preloadImages: true,
            // Callback: run when link is hovered: container is shown:
            onShow: function(){},
            // Callback: container is hidden:
            onHide: function(){},
            // Callback: Run when image within container has loaded:
            onLoad: function(){},
            // ID to give to container (for CSS styling):
            containerID: 'imgPreviewContainer',
            // Class to be given to container while image is loading:
            containerLoadingClass: 'loading',
            // Prefix (if using thumbnails), e.g. 'thumb_'
            thumbPrefix: '',
            // Where to retrieve the image from:
            srcAttr: 'href'

        }, userDefinedSettings),

        $container = $('<div/>').attr('id', s.containerID)
                        .append('<img/>').hide()
                        .css('position','absolute')
                        .appendTo('body'),

        $img = $('img', $container).css(s.imgCSS),

        // Detect page zoom (site uses CSS zoom on html) so the preview is
        // placed next to the cursor: pageX/Y are in rendered px but left/top
        // are in CSS px, which the browser scales by zoom again.
        zoom = (function() {
            var probe = document.createElement('div');
            probe.style.cssText = 'position:absolute;left:-9999px;top:-9999px;width:100px;height:100px;';
            document.body.appendChild(probe);
            var w = probe.getBoundingClientRect().width;
            document.body.removeChild(probe);
            return w > 0 ? w / 100 : 1;
        })(),

        // Get all valid elements (linking to images / ATTR with image link):
        $collection = this.filter(':linkingToImage(' + s.srcAttr + ')');

        // Re-usable means to add prefix (from setting):
        function addPrefix(src) {
            return src && src.replace(/(\/?)([^\/]+)$/,'$1' + s.thumbPrefix + '$2');
        }

        if (s.preloadImages) {
            (function(i){
                var tempIMG = new Image(),
                    callee = arguments.callee;
                var src = $($collection[i]).attr(s.srcAttr)
                if (src)
                {
                    tempIMG.src = addPrefix(src);
                    tempIMG.onload = function(){
                        $collection[i + 1] && callee(i + 1);
                    };
                }
            })(0);
        }

        $collection
            .mousemove(function(e){

                var vw = window.innerWidth,
                    vh = window.innerHeight,
                    sx = window.scrollX || 0,
                    sy = window.scrollY || 0,
                    // rect is in rendered px, same space as pageX/Y and
                    // innerWidth/Height, so the clamp math is consistent.
                    cw = $container[0].getBoundingClientRect().width,
                    ch = $container[0].getBoundingClientRect().height,
                    left = e.pageX + s.distanceFromCursor.left,
                    top = e.pageY + s.distanceFromCursor.top;

                if (cw) {
                    left = Math.min(Math.max(left, sx + 4), sx + vw - cw - 4);
                }
                if (ch) {
                    top = Math.min(Math.max(top, sy + 4), sy + vh - ch - 4);
                }

                $container.css({
                    top: top / zoom + 'px',
                    left: left / zoom + 'px'
                });

            })
            .hover(function(){

                var link = this;
                $container
                    .addClass(s.containerLoadingClass)
                    .show();
                $img
                    .on('load', function(){
                        $container.removeClass(s.containerLoadingClass);
                        $img.show();
                        s.onLoad.call($img[0], link);
                    })
                    .attr( 'src' , addPrefix($(link).attr(s.srcAttr)) );
                s.onShow.call($container[0], link);

            }, function(){

                $container.hide();
                $img.off('load').attr('src','').hide();
                s.onHide.call($container[0], this);

            });

        // Return full selection, not $collection!
        return this;

    };

})(jQuery);
