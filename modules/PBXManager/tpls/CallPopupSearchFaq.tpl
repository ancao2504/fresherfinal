{strip}
    {$replaceParams = ['%keyword' => $KEYWORD]}

    {if sizeof($DATA_ROWS) > 0}
        <h4 class="faq-result-title">{vtranslate('LBL_CALL_POPUP_SEARCH_FAQ_RESULT_TITLE', 'PBXManager', $replaceParams)}</h4>
        <div class="faq-list">
            <ul>
                {foreach from=$DATA_ROWS key=index item=row}
                    <li>
                        <a href="javascript:void(0)" class="openFaqModel" data-id='{$row['id']}' title="{$row['question']}">{$index + 1}. {$row['question']}</a>
                        <p class="short-answer">{$row['answer']}</p>
                    </li>
                {/foreach}
            </ul>
        </div>
        {if $COUNT > 5}
            <a href="javascript:void(0)" class="openSearchFaqModel">Xem thêm</a>
        {/if}
    {else}
        <div class="faq-list-no-data">
            {vtranslate('LBL_CALL_POPUP_SEARCH_FAQ_NO_DATA', 'PBXManager', $replaceParams)}
        </div>
    {/if}
{/strip}