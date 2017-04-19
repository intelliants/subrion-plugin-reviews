<div class="tabbable">
    <ul class="nav nav-tabs">
        {foreach $items as $name => $value}
            <li{if $itemName == $name} class="active"{/if}><a href="{$smarty.const.IA_ADMIN_URL}reviews/settings/{$name}/">{lang key=$name}</a></li>
        {/foreach}
    </ul>

    <div class="tab-content">
        <div class="tab-pane active">
            <form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
                {preventCsrf}

                <div class="wrap-list wrap-list--in-tab">
                    <div class="wrap-group">
                        <div class="wrap-group-heading">
                            <h4>{lang key='general'}</h4>
                        </div>

                        <div class="row">
                            <label class="col col-lg-2 control-label">{lang key='review_allow'}</label>
                            <div class="col col-lg-4">
                                {html_radio_switcher name='review_allowed' value=$settings.review_allowed|default:1}
                            </div>
                        </div>

                        <div class="row">
                            <label class="col col-lg-2 control-label">{lang key='review_comment_allow'}</label>
                            <div class="col col-lg-4">
                                {html_radio_switcher name='comment_allowed' value=$settings.comment_allowed|default:1}
                            </div>
                        </div>
                    </div>

                    <div class="wrap-group">
                        <div class="wrap-group-heading">
                            <h4>{lang key='review_rating'}</h4>
                        </div>

                        <div class="rw-options clearfix">
                            {foreach $settings.options as $i => $option}
                                <div class="rw-options__item">
                                    <h4>{lang key='option'}</h4>
                                    <label>{lang key='title'}</label>
                                    <input type="text" value="{$option.title|escape:'html'}" name="title[]">
                                    <label>{lang key='review_text'}</label>
                                    <textarea name="data[]" rows="6">{$smarty.const.PHP_EOL|implode:$option.data}</textarea>
                                    <a class="btn btn-xs btn-danger js-entry" href="#">{lang key='delete'}</a>
                                </div>
                            {/foreach}

                            <div class="rw-options__item" id="js-new-item" style="display: none;">
                                <h4>{lang key='option'}</h4>
                                <label>{lang key='title'}</label>
                                <input type="text" name="title[]">
                                <label>{lang key='review_text'}</label>
                                <textarea name="data[]" rows="6"></textarea>
                                <a class="btn btn-xs btn-danger js-entry" href="#">{lang key='delete'}</a>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions inline">
                        <button class="btn btn-success js-add-option" type="button">{lang key='add_new_option'}</button>
                        <button class="btn btn-primary" type="submit" name="data-settings">{lang key='save'}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{ia_add_media files='js:_IA_URL_modules/reviews/js/admin/settings,css:_IA_URL_modules/reviews/templates/admin/css/style'}
