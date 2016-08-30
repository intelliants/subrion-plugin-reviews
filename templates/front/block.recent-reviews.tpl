{ia_print_css files='_IA_URL_plugins/reviews/templates/front/css/jquery.reviews'}
{if isset($recentReviews)}
	{foreach $recentReviews as $review}
		<div class="info">
			{$review_rating=unserialize($review.ratings)}
			<span class="fa fa-calendar"></span> {$review.date_updated|date_format:$core.config.date_format} <span class="rgold m-l" style="width:{$review_rating[0][1]*16}px;">&nbsp;</span>
		</div>
		<a href="{$review.item_url}">{$review.item_title}</a><br>
		{if !$review@last}<hr>{/if}
	{/foreach}
{/if}