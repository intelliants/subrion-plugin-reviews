$(function () {
    $('.js-add-option').on('click', function () {
        $('#js-new-item').clone().insertBefore('#js-new-item').css('display', 'block').attr('id', '');
    }).trigger('click');

    $('.rw-options').on('click', '.js-entry', function (e) {
        e.preventDefault();

        var parent = $(this).closest('.rw-options__item');

        if (parent.find('textarea').val() == '') {
            parent.remove();
        }
        else {
            Ext.Msg.confirm(_t('confirm'), _t('are_you_sure_to_delete_this_item'), function (r) {
                if (r == 'yes') parent.remove();
            });
        }
    });
});