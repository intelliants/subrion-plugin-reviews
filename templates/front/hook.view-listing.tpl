{if isset($reviewsSettings)}
{ia_block title={lang key='reviews'} style='movable' id='reviews-form' classname='box-clear' collapsible=true}
	{if $review}
		{include "{$smarty.const.IA_PLUGINS}reviews/templates/front/_overall-rating.tpl"}
	{/if}
	{if 1 == $reviewsSettings.review_allowed && !$alreadyReviewed}
		{include "{$smarty.const.IA_PLUGINS}reviews/templates/front/_review-form.tpl"}
	{/if}
	{if $reviewTexts}
		{include "{$smarty.const.IA_PLUGINS}reviews/templates/front/_reviews-list.tpl"}
	{/if}
{/ia_block}
{ia_print_css files='_IA_URL_plugins/reviews/templates/front/css/jquery.reviews'}
{/if}