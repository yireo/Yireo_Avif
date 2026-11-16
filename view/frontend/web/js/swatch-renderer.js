define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            updateBaseImage: function (images, context, isInProductView) {
                this._super(images, context, isInProductView);

                if (isInProductView) {
                    return;
                }

                const image = images[0];
                if (!image || !image.img) {
                    return;
                }

                const avifSources = context.find('picture source[type="image/avif"]');
                if (image.img_avif) {
                    avifSources.attr('srcset', image.img_avif);
                    return;
                }

                avifSources.remove();
            }
        });

        return $.mage.SwatchRenderer;
    };
});
