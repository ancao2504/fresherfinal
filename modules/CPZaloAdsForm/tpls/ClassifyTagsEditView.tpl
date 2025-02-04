{*
	Name: ClassifyTagsEditView.tpl
	Author: Phu Vo
	Date: 2021.11.08
*}

{strip}
	<div class="classify-tags-container">
		<input type="text"
			name="classify_tags"
			class="classify-tags-input"
			placeholder="{vtranslate('LBL_TYPE_SEARCH_AND_CREATE_NEW', $MODULE)}"
			{if !empty($RECORD->get('classify_tags'))}
				data-selected-tags='{ZEND_JSON::encode($RECORD->getClassifyTags())}'
			{/if}
		/>
	</div>
{/strip}