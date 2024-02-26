(function ($) {

    $('.ikreate-theme-modal-button').on('click', function (e) {
        e.preventDefault();
        $('body').addClass('ikreate-theme-modal-opened');
        var modalId = $(this).attr('href');
        $(modalId).fadeIn();

        $("html, body").animate({ scrollTop: 0 }, "slow");
    });

    $('.ikreate-theme-modal-back, .ikreate-theme-modal-cancel').on('click', function (e) {
        $('body').removeClass('ikreate-theme-modal-opened');
        $('.ikreate-theme-modal').hide();
        $("html, body").animate({ scrollTop: 0 }, "slow");
    });

    $('body').on('click', '.ikreate-theme-import-demo', function () {
        var $el = $(this);
        var demo = $(this).attr('data-demo-slug');
        var reset = $('#checkbox-reset-' + demo).is(':checked');
        var excludeImages = $('#checkbox-exclude-image-' + demo).is(':checked');
        var reset_message = '';

        if (reset) {
            reset_message = ikreate_ajax_data.reset_database;
            var confirm_message = 'Are you sure to proceed? Resetting the database will delete all your contents.';
        } else {
            var confirm_message = 'Are you sure to proceed?';
        }

        $import_true = confirm(confirm_message);
        if ($import_true == false)
            return;

        $("html, body").animate({ scrollTop: 0 }, "slow");

        $('#ikreate-theme-modal-' + demo).hide();
        $('#ikreate-theme-import-progress').show();

        $('#ikreate-theme-import-progress .ikreate-theme-import-progress-message').html(ikreate_ajax_data.prepare_importing).fadeIn();

        var info = {
            demo: demo,
            reset: reset,
            next_step: 'ikreate_themes_install_demo',
            excludeImages: excludeImages,   
            next_step_message: reset_message
        };

        setTimeout(function () {
            do_ajax(info);
        }, 2000);
    });

    function do_ajax(info) {
        if (info.next_step) {
            var data = {
                action: info.next_step,
                demo: info.demo,
                reset: info.reset,
                excludeImages: info.excludeImages,
                security: ikreate_ajax_data.nonce
            };

            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: data,
                beforeSend: function () {
                    if (info.next_step_message) {
                        $('#ikreate-theme-import-progress .ikreate-theme-import-progress-message').hide().html('').fadeIn().html(info.next_step_message);
                    }
                },
                success: function (response) {
                    var info = JSON.parse(response);

                    if (!info.error) {
                        if (info.complete_message) {
                            $('#ikreate-theme-import-progress .ikreate-theme-import-progress-message').hide().html('').fadeIn().html(info.complete_message);
                        }
                        setTimeout(function () {
                            do_ajax(info);
                        }, 4000);
                    } else {
                        $('#ikreate-theme-import-progress .ikreate-theme-import-progress-message').html(ikreate_ajax_data.import_error);

                    }
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    $('#ikreate-theme-import-progress .ikreate-theme-import-progress-message').html(ikreate_ajax_data.import_error);
                    $('#ikreate-theme-import-progress').addClass('import-error');
                }
            });
        } else {
            $('#ikreate-theme-import-progress .ikreate-theme-import-progress-message').html(ikreate_ajax_data.import_success);
            $('#ikreate-theme-import-progress').addClass('import-success');
        }
    }

    if ($('.ikreate-theme-tab-filter').length > 0) {
        
        $('.ikreate-theme-tab-group').each(function () {
            $(this).find('.ikreate-theme-tab:first').addClass('ikreate-theme-active');
        });

        var $grid = $('.ikreate-theme-demo-box-wrap').imagesLoaded(function () {
            $grid.isotope({
                layoutMode: 'fitRows',
                itemSelector: '.ikreate-theme-demo-box',
                fitRows: {
                  gutter: 16
                }
            });
        });

        var filters = {};

        $('.ikreate-theme-tab-group').on('click', '.ikreate-theme-tab', function (event) {
            var $button = $(event.currentTarget);
            var $buttonGroup = $button.parents('.ikreate-theme-tab-group');
            var filterGroup = $buttonGroup.attr('data-filter-group');
            filters[filterGroup] = $button.attr('data-filter');
            var filterValue = concatValues(filters);
            $grid.isotope({ filter: filterValue });
        });

        $('.ikreate-theme-tab-group').each(function (i, buttonGroup) {
            var $buttonGroup = $(buttonGroup);
            $buttonGroup.on('click', '.ikreate-theme-tab', function (event) {
                $buttonGroup.find('.ikreate-theme-active').removeClass('ikreate-theme-active');
                var $button = $(event.currentTarget);
                $button.addClass('ikreate-theme-active');
            });
        });

        function concatValues(obj) {
            var value = '';
            for (var prop in obj) {
                value += obj[prop];
            }
            return value;
        }
    }
})(jQuery);
