
{*
 * RatingReadView.tpl
 * Author: Phuc
 * Date: 2020.06.26
 *}

{strip}
    {if !isset($CURRENT_STAR)}
        {assign var="CURRENT_STAR" value=str_replace('rating_', '', $FIELD_MODEL->get('fieldvalue'))}
    {/if}

    {if !isset($TICKET_STATUS)}
        {assign var="TICKET_STATUS" value=$RECORD->get('ticketstatus')}
    {/if}

    <span class="div-rating">
        {for $STAR=1 to 5}
            {if $STAR <= $CURRENT_STAR}
                <span class="fas fa-star rating-star checked" data-star="{$STAR}"></span>
            {else}
                <span class="far fa-star rating-star" data-star="{$STAR}"></span>
            {/if}
        {/for}
    </span>
{/strip}