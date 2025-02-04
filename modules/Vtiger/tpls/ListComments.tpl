{* Added by vu Mai on 2002-09-09 to render parent comment list in custom comment *}

{if !empty($COMMENTS)}
	{foreach from=$COMMENTS item=COMMENT key=INDEX}
		<div class="comment-item">
			<div class="comment">
				<div class="author-avatar">
					<img src="entrypoint.php?name=GetAvatar&record={$COMMENT.user_id}&module=Users" />
				</div>
				<div class="comment-content-container align-item-center">
					<span class="comment-content mr-2" data-toggle="tooltip" title="{$COMMENT.comment_title}">{$COMMENT.comment_content}</span>
					
					{if $COMMENT.child_count > 0}
						<div class="flex">
							{$replaceParams = ['%child_count' => $COMMENT.child_count]}
							( <a class="child-count" data-toggle="tooltip" title="{vtranslate('LBL_CUSTOM_COMMENT_CHILD_COMMENT_COUNT_TITLE', 'Vtiger', $replaceParams)}" data-id="{$COMMENT.id}">{$COMMENT.child_count}</a>)
						</div>
					{/if}	
				</div>	
			</div>
			<div class="child-comment-list" style="display:none;"></div>
		</div>	
	{/foreach}
{else}
	<div class="no-comment">
		<span class="no-data">{vtranslate('LBL_NO_COMMENTS')}</span>
	</div>	
{/if}