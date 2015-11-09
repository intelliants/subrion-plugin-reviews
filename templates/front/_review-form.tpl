<div class="reviews__item reviews__item--form">
	<div class="reviews__item__author clearfix">
		{if !$member}
			<img src="{$img}no-avatar.png" width="40" class="img-circle pull-left" alt="">
			<p class="reviews__item__author__name">{lang key='guest'}</p>
		{else}
			<a href="{ia_url type='url' item='members' data=$member}">
				{if $member.avatar}
					{assign avatar $member.avatar|unserialize}
					{printImage imgfile=$avatar.path width=40 class='img-circle pull-left' title=$member.fullname}
				{else}
					<img src="{$img}no-avatar.png" width="40" class="img-circle pull-left" alt="">
				{/if}
			</a>
			<p class="reviews__item__author__name"><a href="{ia_url type='url' item='members' data=$member}">{$member.fullname}</a></p>
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
			<textarea name="text" class="input-block-level" rows="5"></textarea>
		</div>
		<button type="submit" class="btn btn-small btn-primary" name="data-review">{lang key='post_review'}</button>
	</form>
</div>

{*
{ia_add_js}
$(function()
{
	$('#rate_td .star').rating(
	{
		focus: function(value, link)
		{
			var tip = $(link).parents('fieldset:first').find('.hover_text');
			tip[0].data = tip[0].data || tip.html();
			tip.html(link.title || 'value: ' + value);
		},
		blur: function(value, link)
		{
			var tip = $(link).parents('fieldset:first').find('.hover_text');
			tip.html(tip[0].data || '&nbsp;');
		}
	});
});
{/ia_add_js}
*}
{ia_print_js files="_IA_URL_plugins/reviews/js/frontend/jquery.MetaData,_IA_URL_plugins/reviews/js/frontend/jquery.rating"}