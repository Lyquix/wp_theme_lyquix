(function($) {
    window.NggPaginatedGallery = function(displayed_gallery_id, container) {
        this.displayed_gallery_id = displayed_gallery_id;
        this.container            = $(container);
        this.container_name       = container;

        this.get_displayed_gallery_obj = function() {
            var index = 'gallery_' + this.displayed_gallery_id;
            if (typeof(window.galleries[index]) == 'undefined') {
                return false;
            } else {
                return window.galleries[index];
            }
        };

        this.enable_ajax_pagination = function() {
            var self = this;
            // Attach a click event handler for each pagination link to adjust the request to be sent via XHR
            $('body').on('click', 'a.ngg-browser-prev, a.ngg-browser-next', function (event) {

                var skip = true;
                $(this).parents(container).each(function() {
                    if ($(this).data('nextgen-gallery-id') != self.displayed_gallery_id) {
                        return true;
                    }
                    skip = false;
                });

                if (!skip) {
                    event.preventDefault();
                } else {
                    return;
                }

                // Adjust the user notification
                window['ngg_ajax_operaton_count']++;
                $('body, a').css('cursor', 'wait');

                // Send the AJAX request
                $.get($(this).attr('href'), function (response) {
                    window['ngg_ajax_operaton_count']--;
                    if (window['ngg_ajax_operaton_count'] <= 0) {
                        window['ngg_ajax_operaton_count'] = 0;
                        $('body, a').css('cursor', 'auto');
                    }

                    if (response) {
                        var html = $(response);
                        var replacement = false;
                        html.find(self.container_name).each(function() {
                            if (replacement) {
                                return true;
                            }
                            if ($(this).data('nextgen-gallery-id') != self.displayed_gallery_id) {
                                return true;
                            }
                            replacement = $(this);
                        });
                        if (replacement) {
                            self.container.each(function () {
                                if ($(this).data('nextgen-gallery-id') != self.displayed_gallery_id) {
                                    return true;
                                }
                                $(this).html(replacement.html());
                                return true;
                            });

                            // Let the user know that we've refreshed the content
                            $(document).trigger('refreshed');
                        }
                    }
                });
            });
        };

        // Initialize
        var displayed_gallery = this.get_displayed_gallery_obj();
        if (displayed_gallery) {
            if (typeof(displayed_gallery.display_settings['ajax_pagination']) != 'undefined') {
                if (parseInt(displayed_gallery.display_settings['ajax_pagination'])) {
                    this.enable_ajax_pagination();
                }
            }
        }

        // We maintain a count of all the current AJAX actions initiated
        if (typeof(window['ngg_ajax_operation_count']) == 'undefined') {
            window['ngg_ajax_operaton_count'] = 0;
        }
    };

})(jQuery);