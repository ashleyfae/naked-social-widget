jQuery(document).ready(function ($) {

    var Naked_Social_Widget = {

        /**
         * Get stuff started.
         */
        init: function() {
            this.init_color_picker();
            this.clone_repeatable();
        },

        /**
         * Initialize color picker.
         */
        init_color_picker : function() {
            $('.naked-social-widget-color-picker').wpColorPicker();
        },

        /**
         * Initialize repeater ("Social Sites" field)
         */
        clone_repeatable: function() {
            $('#naked-social-widget-add-site').relCopy();
        }

    };

    Naked_Social_Widget.init();

});