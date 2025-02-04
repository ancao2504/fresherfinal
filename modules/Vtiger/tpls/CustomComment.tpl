{* Added by Vu Mai on 2022-09-08 to render custom comment *}
<link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/CustomComment.css')}" />

<div class="custom-comment">
	<input type="hidden" class="total-count" value="{$TOTAL_COUNT}" />
	
	<div class="related-comments-container">
		<!-- Comment List -->
		<h3 class="block-header {if $TOTAL_COUNT == 0}hide{/if}" >
			<span class="comment-count"></span>&nbsp;{vtranslate('LBL_CUSTOM_COMMENT_LASTEST_COMMENTS')}
		</h3>
		<div class="comment-list">
		</div>
		<div class="actions text-right">
			<a href="index.php?module={$MODULE}&relatedModule=ModComments&view=Detail&record={$RECORD}&mode=showRelatedList&tab_label=ModComments&relationId={getCommentRelationId($MODULE)}" 
				target="_blank" class="btn btn-link view-all {if $TOTAL_COUNT == 0}hide{/if}">
				{vtranslate('LBL_CUSTOM_COMMENT_VIEW_ALL')}
			</a>
		</div>

		<!-- New comment form -->
		<div class="add-comment-container">
			<textarea name="comment_content" data-rule-required="true" style="display: none;"></textarea>
			<div id="add-comment-textarea" contenteditable="true" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE')}" class="commentTextArea fancyScrollbar" data-tribute="true"></div>
		</div>
		<div class="comment-actions-container">
			<div class="comment-actions">
				<div class="align-item-center">
					<a href="javascript:void(0)">
						<label class="cursorPointer comment-attachment-btn">
							<i aria-hidden="true" class="far fa-paperclip mr-1"></i><span>{vtranslate('LBL_ATTACHMENT')}</span>
							<input type="file" name="attachements" style="display: none;">
						</label>
					</a>
					<div class="marginleft-auto">
						<i class="far fa-spinner fa-spin" style="display: none;"></i>
						<button class="btn btn-primary save-comment">{vtranslate('LBL_SEND')}</button>
					</div>
				</div>
			</div>
		</div>
		<div class="comment-attachments-container">
		</div>

		<!-- Attachment template -->
		<div class="comment-attachment-template" style="display:none;">
			<div class="comment-attachments">
				<div class="comment-attachment-actions">
					<button class="remove-attachment-button"><i aria-hidden="true" class="far fa-times"></i></button>
				</div>
				<div class="comment-attachment-name">
					<span class="file-name"></span>
					<span class="file-extension"></span>
				</div>
			</div>
		</div>
	</div>
</div>