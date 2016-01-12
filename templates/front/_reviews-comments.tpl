{assign entryId $entry.id}
<div class="reviews__item__comments" id="reviews_comments_{$entryId}" style="display: none;">
	{if isset($reviewComments.$entryId)}
		{foreach $reviewComments.$entryId as $comment}
			<div class="comment clearfix">
				<div class="num"><span>{$comment@iteration}</span></div>
				<div class="description">
					<div class="author">
						<span>{lang key='posted_by'}</span>
						{if $comment.member_id == 0}
							{$comment.author|escape:'html'}
						{else}
							<a href="{ia_url type='url' item='members' data=$comment}">{$comment.author}</a>
						{/if}
						<span>{lang key='on'} {$comment.date|date_format:$core.config.date_format}</span>
						{if iaCore::STATUS_APPROVAL == $comment.status && ($comment.session_id == $sessionId)}
							<span class="pull-right">{lang key='message_approval'}</span>
						{/if}
					</div>
					{$comment.body|escape:'html'|nl2br}
				</div>
			</div>
		{/foreach}
	{/if}
</div>