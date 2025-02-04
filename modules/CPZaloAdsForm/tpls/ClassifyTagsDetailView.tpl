{*
	Name: ClassifyTagsDetailView.tpl
	Author: Phu Vo
	Date: 2021.11.08
*}

{strip}
	{assign var=TAGS_LIST value=$RECORD->getClassifyTags()}

	<span class="value" data-field-type="text">
		{foreach from=$TAGS_LIST item=TAG key=INDEX}
			{if $INDEX > 0},&nbsp;{/if}
			{$TAG.text}
		{/foreach}
	</span>
{/strip}