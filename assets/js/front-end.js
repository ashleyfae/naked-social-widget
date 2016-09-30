/**
 * Front-End Scripts
 *
 * @package   naked-social-widget
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

(function ($) {

    $(document).ready(function () {

        console.log('ready');

        var numberWrapper = $('.nsw-update-number');

        numberWrapper.each(function () {

            var site = $(this);
            var url = $(this).parent().attr('href');
            var widgetID = $(this).data('id');

            console.log(url);

            if (url == '' || typeof url === 'undefined') {
                return true;
            }

            site.removeClass('nsw-update-number');

            var data = {
                action: 'nsw_update_followers',
                profile_url: url,
                widget_id: widgetID
            };

            $.post(NSW.ajaxurl, data, function (response) {
                console.log(response);
                if (response.success == true) {
                    site.text(response.data);
                } else {
                    console.log(response);
                }
            });

        });

    });

})(jQuery);