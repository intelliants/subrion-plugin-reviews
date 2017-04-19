<div class="reviews__overall">
    {foreach $review.ratings as $key => $rate}
        <div class="reviews__overall__count">
            {$rate[0]} {if 1 == $rate[0]}{lang key='review'}{else}{lang key='reviews'}{/if}
        </div>
        {assign marksCount $reviewsSettings.options[$key].data|count}
        <div class="reviews__overall__stars">
            <div class="reviews__overall__stars__item clearfix">
                <span class="title">{$reviewsSettings.options[$key].title}:</span>
                <span class="rgray" style="width: {$marksCount*16}px;">
                    <span class="rgold" style="width: {$rate[1]*16}px;">&nbsp;</span>
                </span>
            </div>
        </div>
    {/foreach}
</div>