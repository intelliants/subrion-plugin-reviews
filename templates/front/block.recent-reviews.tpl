{if isset($recentReviews)}
	{foreach $recentReviews as $review}
		<div class="info">
			<i class="icon-calendar"></i> {$review.date_updated|date_format:$core.config.date_format}
		</div>
		<a href="{$review.item_url}">{$review.item_title}</a><br>
		{if !$review@last}<hr>{/if}
	{/foreach}
{/if}