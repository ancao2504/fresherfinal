
{*
 * RatingEditView.tpl
 * Author: Phuc
 * Date: 2020.06.26
 *}

{strip}
    <div class="div-rating hide">
        {for $STAR=1 to 5}
            <span class="edit far fa-star rating-star{if $STAR <= $FIELD_MODEL->get('fieldvalue')} checked{/if}" data-star="{$STAR}"></span>
        {/for}
    </div>

    <div class="div-rating-select" style="display:none">
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
    </div>
{/strip}