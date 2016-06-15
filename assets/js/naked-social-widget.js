jQuery(document).ready(function ($) {

    var Naked_Social_Widget_Update_Expiry = false;

    var Naked_Social_Widget = {

        /**
         * Get stuff started.
         */
        init: function () {
            this.check_updates();
        },

        /**
         * Check each social site to see if it needs updating.
         */
        check_updates: function () {

            var widgetID = $('.naked-social-widget-profile').data('id');

            $('.naked-social-widget-profile > li').each(function () {
                if ($(this).hasClass('nsw-ajax-update')) {
                    Naked_Social_Widget_Update_Expiry = true;
                    Naked_Social_Widget.update_followers(widgetID, $(this));
                }
            });

            $(document).ajaxStop(function () {
                if (Naked_Social_Widget_Update_Expiry === true) {
                    Naked_Social_Widget.update_expiry(widgetID);
                }
            });

        },

        /**
         * Update Followers
         *
         * Ajax request to update the follower number for a given site.
         *
         * @param widgetID
         * @param listElement
         */
        update_followers: function (widgetID, listElement) {

            var data = {
                action: 'naked_social_widget_update_number',
                widget_id: widgetID,
                site_name: listElement.data('site'),
                site_key: listElement.data('key'),
                username: listElement.data('username'),
                nonce: NAKED_SOCIAL_WIDGET.nonce
            };

            $.ajax({
                type: 'POST',
                data: data,
                url: NAKED_SOCIAL_WIDGET.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {

                    console.log(response);

                    if (response.success == true) {

                        var followerNumber = response.data;

                        // Update number.
                        listElement.find('.nsw-follower-number').text(followerNumber);

                        // Remove ajax class.
                        listElement.removeClass('nsw-ajax-update');

                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });
        },

        /**
         * Update Expiry Time
         *
         * Update the cache expiry time to reflect that new follower numbers
         * have been fetched recently.
         *
         * @param widgetID
         */
        update_expiry: function (widgetID) {

            var data = {
                action: 'naked_social_widget_update_expiry',
                widget_id: widgetID,
                nonce: NAKED_SOCIAL_WIDGET.nonce
            };

            $.ajax({
                type: 'POST',
                data: data,
                url: NAKED_SOCIAL_WIDGET.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {

                    console.log(response);
                    Naked_Social_Widget_Update_Expiry = false;

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });

        }

    };

    Naked_Social_Widget.init();

});