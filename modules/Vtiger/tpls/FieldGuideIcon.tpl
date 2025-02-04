{* Added by Hieu Nguyen on 2021-01-20 *}

{strip}
    {assign var="HELP_TEXT" value=trim(nl2br($FIELD_MODEL->get('helpinfo')))}

    {if $HELP_TEXT != ''}
        <span data-toggle="tooltip" title="{$HELP_TEXT}">
            <i class="far fa-info-circle"></i>
        </span>
    {/if}
{/strip}