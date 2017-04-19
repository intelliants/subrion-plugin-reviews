<div class="reviews__list">
    {foreach $reviewTexts as $entry}
        {assign owned value=(($member && $entry.member_id == $member.id) || (!$member && $entry.session_id == $sessionId))}

        <div id="review_{$entry.id}" class="reviews__item{if $owned} reviews__item--owned{/if}">
            <div class="reviews__item__author clearfix">
                {if empty($entry.username)}
                    <p class="reviews__item__author__name">
                        <img src="{$img}no-avatar.png" width="40" class="img-circle pull-left" alt="">
                        {lang key='guest'}
                    </p>
                {else}
                    <p class="reviews__item__author__name">
                        <a href="{ia_url type='url' item='members' data=$entry}">
                            {if $entry.avatar}
                                {assign avatar $entry.avatar|unserialize}
                                {printImage imgfile=$avatar.path width=40 class='img-circle pull-left' title=$entry.fullname}
                            {else}
                                <img src="{$img}no-avatar.png" width="40" class="img-circle pull-left" alt="">
                            {/if}
                        </a>
                        <a href="{ia_url type='url' item='members' data=$entry}">{$entry.fullname}</a>
                    </p>
                {/if}

                <div class="reviews__item__rating">
                    {foreach $entry.review_rates|unserialize as $key => $rate}
                        <div class="reviews__item__rating__i clearfix">
                            <span class="title">{$reviewsSettings.options[$key].title|escape:'html'}:</span>
                            <div class="rgray" style="width:{$reviewsSettings.options[$key].data|count*16}px;">
                                <div class="rgold" style="width:{$rate*16}px;">&nbsp;</div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="reviews__item__text">
                {$entry.review_text|escape:'html'|nl2br}
            </div>

            <div class="reviews__item__info">
                <span class="reviews__item__date">{lang key='on'} {$entry.date_added|date_format:$core.config.date_format}</span>
                {if !$owned}
                    <span class="reviews__item__likes">
                        <a href="#" class="like" data-itemid="{$entry.id}" title="{lang key='review_like'}">
                            <span class="fa fa-thumbs-up"></span> <span>{$entry.likes}</span>
                        </a>
                        <a href="#" class="dislike" data-itemid="{$entry.id}" title="{lang key='review_dislike'}">
                            <span class="fa fa-thumbs-down"></span> <span>{$entry.dislikes}</span>
                        </a>
                    </span>
                {/if}
                {if $entry.comments > 0}
                    <span class="reviews__item__comments-link">
                        <a href="javascript:;" class="c-show-hide" onclick="toggle_div('comments',{$entry.id})" title="{lang key='close_open_comments'}">{lang key='comments'}: {$entry.comments}</a>
                    </span>
                {/if}
                {if $reviewsSettings.comment_allowed}
                    <a class="add-comment pull-right" href="javascript:;" onclick="toggle_div('form',{$entry.id})">
                        <span class="fa fa-comment"></span> {lang key='post_comment'}
                    </a>
                {/if}
            </div>
            <div class="reviews__item__comment-form" id="reviews_form_{$entry.id}" style="display: none;">
                <form method="post" class="form-subrion comment-form">
                    {preventCsrf}
                    <input type="hidden" value="{$entry.id}" name="review_id">
                    <input type="hidden" value="" name="review_type" id="review_type_{$entry.id}">
                    {if !$member}
                        <b>{lang key='author'}:</b> <input type="text" name="review_author" value="Guest" disabled="disabled">
                    {/if}
                    <div class="form-group">
                        <textarea name="review_comment" class="form-control" rows="5"></textarea>
                    </div>
                    <button type="submit" class="btn btn-info btn-xs"><span class="fa fa-comment"></span> {lang key='comment'}</button>
                    <button type="button" class="btn btn-danger btn-xs" onclick="toggle_div('form',{$entry.id})"><span class="fa fa-minus-circle"></span> {lang key='cancel'}</button>
                </form>
            </div>

            {include "{$smarty.const.IA_MODULES}reviews/templates/front/_reviews-comments.tpl"}
        </div>
    {/foreach}
</div>
{ia_print_js files='_IA_URL_modules/reviews/js/frontend/reviews'}