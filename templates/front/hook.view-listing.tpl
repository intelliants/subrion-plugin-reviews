{if isset($reviewsSettings)}
    {ia_block title={lang key='reviews'} style='movable' id='reviews-form' collapsible=true}
        {if $review}
            {include "{$smarty.const.IA_MODULES}reviews/templates/front/_overall-rating.tpl"}
        {/if}
        {if 1 == $reviewsSettings.review_allowed && !$alreadyReviewed}
            {if (!$core.config.reviews_guests_accepted && $member) || $core.config.reviews_guests_accepted}
                {include "{$smarty.const.IA_MODULES}reviews/templates/front/_review-form.tpl"}
            {/if}
        {/if}
        {if $reviewTexts}
            {include "{$smarty.const.IA_MODULES}reviews/templates/front/_reviews-list.tpl"}
        {/if}
    {/ia_block}
    {ia_print_css files='_IA_URL_modules/reviews/templates/front/css/jquery.reviews'}
{/if}