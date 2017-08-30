<div class="reviews__item reviews__item--form">
    <div class="reviews__item__author clearfix">
        {if !$member}
            <img src="{$img}no-avatar.png" width="40" class="img-circle pull-left" alt="">
            <p class="reviews__item__author__name">{lang key='guest'}</p>
        {else}
            <a href="{ia_url type='url' item='members' data=$member}">
                {if $member.avatar}
                    {assign avatar $member.avatar|unserialize}
                    {ia_image file=$avatar width=40 class='img-circle pull-left' title=$member.fullname}
                {else}
                    <img src="{$img}no-avatar.png" width="40" class="img-circle pull-left" alt="">
                {/if}
            </a>
            <p class="reviews__item__author__name"><a href="{ia_url type='url' item='members' data=$member}">{$member.fullname|escape}</a></p>
        {/if}
    </div>
    <form method="post" id="rate_item">
        {preventCsrf}
        <div class="reviews__form">
            <div id="rate_td" class="reviews__form__stars">
                {foreach $reviewsSettings.options as $optionId => $option}
                    <div class="reviews__form__stars__item clearfix">
                        <span class="title">{$option.title|escape:'html'}:</span>
                        {foreach $option.data as $key => $title}
                            <input name="rating[{$optionId}]" type="radio" class="star" value="{$key+1}" title="{$title|escape:'html'}">
                        {/foreach}
                    </div>
                {/foreach}
            </div>
            <textarea name="text" class="form-control" rows="5"></textarea>
        </div>
        <button type="submit" class="btn btn-sm btn-primary" name="data-review">{lang key='post_review'}</button>
    </form>
</div>

{ia_print_js files="_IA_URL_modules/reviews/js/frontend/jquery.MetaData,_IA_URL_modules/reviews/js/frontend/jquery.rating"}