var toggle_div = function (div, id) {
    var display = $('#reviews_' + div + '_' + id).css('display');
    if (display == 'none') {
        $('#reviews_' + div + '_' + id).slideDown('fast');
        $('.comments-controls a').removeClass('muted');
        $('.comments-controls a.c-show').addClass('muted');
    }
    else {
        $('#reviews_' + div + '_' + id).slideUp('fast');
        $('.comments-controls a').removeClass('muted');
        $('.comments-controls a.c-hide').addClass('muted');
    }
};

$(function () {
    $('.c-show').on('click', function () {
        $(this).prop('disabled', true).next('.c-hide').removeAttr('disabled');
    });

    $('.c-hide').on('click', function () {
        $(this).prop('disabled', true).prev('.c-show').removeAttr('disabled');
    });

    $('.like, .dislike').on('click', function (e) {
        e.preventDefault();

        var itemId = $(this).data('itemid');
        if (itemId) {
            var action = $(this).hasClass('dislike') ? 'dislike' : 'like';

            $.post(intelli.config.baseurl + 'reviews.json', {action: action, review: itemId}, function (response) {
                intelli.notifFloatBox({
                    msg: response.message,
                    type: response.error ? 'info' : 'success',
                    autohide: true
                });
            });
        }
    });
});