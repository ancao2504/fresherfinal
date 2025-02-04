{* Added bu Vu Mai on 2022-09-07 to render selected tag list *}
{if count($TAG_LIST) != 0}
	<div class="tag-list">
		{foreach item=TAG key=INDEX from=$TAG_LIST_SHOW}
			<span data-type="{$TAG.type}" class="tag">{$TAG.name}</span>
		{/foreach}

		<div class="dropdown">
			{if count($TAG_LIST) > 2}
				<a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle tag tag-count">+{count($TAG_LIST) - 2}</a>
			{/if}

			<div class="dropdown-menu dropdown-menu-left full-tags-container">
				{foreach item=TAG key=INDEX from=$TAG_LIST}
					<span data-type="{$TAG.type}" class="tag">{$TAG.name}</span>
				{/foreach}
			</div>
		</div>
	</div>
{/if}
	