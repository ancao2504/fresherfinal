{*
    File RelatedListCustomRowActions.tpl
    Author: Hieu Nguyen
    Date: 2019-08-06
    Purpose: to add custom buttons in related list rows
*}

{strip}
    {if $RELATED_MODULE_NAME eq 'CPSocialArticle'}
        {assign var="ZALO_BROADCAST_ALLOWED" value=CPSocialIntegration_Config_Helper::isZaloBroadcastAllowed()}

        {if $ZALO_BROADCAST_ALLOWED eq true}
            <a class="send-broadcast"><i title="{vtranslate('LBL_SOCIAL_INTEGRATION_SEND_ARTICLE_BROADCAST', $MODULE)}" class="far fa-send"></i></a>
        {/if}
    {/if}
{/strip}