{* 
    Author: Phu Vo
    Date: 2019.20.06
    Description: Provide a template for display custom owner field as list view
*}

{strip}
    <span class="owners custom-popover-wrapper">
        {if $OWNER_COUNT == 0}
            <a class="no-owner" href="javascript: void(0)"></a>
        {elseif $OWNER_COUNT == 1}
            <span class="stand-owner" href="javascript: void(0)"><a href="index.php?module={$FIRST_OWNER['type']}&parent=Settings&view=Detail&record={$FIRST_OWNER['id']}">{$FIRST_OWNER['text']}</a></span>
        {else}
            <a class="stand-owner-plus custom-popover" title="{vtranslate('Assigned To')}" data-title="{vtranslate('Assigned To')}">
                <span class="stand-owner-plus-text">{$FIRST_OWNER['text']}</span> 
                <span class="stand-owner-plus-icon"> +{$OWNER_COUNT - 1}</span>
            </a>
            <div class="custom-popover-content" style="display: none">
                {foreach from=$OWNERS key=type item=owners}
                    {if sizeof(owners) > 0}
                        <p class="owners-detail_title">{vtranslate("SINGLE_"|cat:$type)}:</p>
                        <ul class="owners-detail_owners">
                            {foreach from=$owners item=owner}
                                <li class="owners-detail_owner">
                                    <a target="_blank" href="index.php?module={$type}&parent=Settings&view=Detail&record={$owner['id']}">{$owner['text']}</a> {* Bug #279 *}
                                    {if $owner['email'] neq null}
                                        ({$owner['email']})
                                    {/if}
                                </li>
                            {/foreach}
                        </ul>
                    {/if}
                {/foreach}
            </div>
        {/if}
    </span>
{/strip}