{*
    File RelatedListCustomRowActions.tpl
    Author: Hieu Nguyen
    Date: 2020-05-26
    Purpose: to add custom buttons in related list rows
*}

{strip}
    {if $RELATED_MODULE_NAME eq 'CPEventRegistration'}
        {assign var="IS_FORBIDDEN" value=isForbiddenFeature('EventManagement')}
        {assign var="IS_EVENT_ENDED" value=CPEventRegistration_Logic_Helper::isEventEnded($RELATED_RECORD->getId())}

        {if
            !$IS_FORBIDDEN
            && !$IS_EVENT_ENDED
            && $RELATED_RECORD->getRaw('cpeventregistration_status') != 'attended'
            && $RELATED_RECORD->getRaw('cpeventregistration_status') != 'cancelled'
        }
            <a class="cancel-registration"><i title="{vtranslate('LBL_CANCEL_REGISTRATION', $MODULE)}" data-toggle="tooltip" class="far fa-close"></i></a>&nbsp;
            <a class="resend-qr-code"><i title="{vtranslate('LBL_RESEND_QR_CODE', $MODULE)}" data-toggle="tooltip" class="far fa-share"></i></a>&nbsp;
            {if $RELATED_RECORD->getRaw('cpeventregistration_status') != 'confirmed'}
                {if $RELATED_RECORD->getRaw('cpeventregistration_status') != 'not_confirmed'}
                    <a class="mark-customer-not-confirmed"><i title="{vtranslate('LBL_MARK_AS_CUSTOMER_NOT_CONFIRMED', $MODULE)}" data-toggle="tooltip" class="far fa-times-circle"></i></a>&nbsp;
                {/if}
                <a class="mark-customer-confirmed"><i title="{vtranslate('LBL_MARK_AS_CUSTOMER_CONFIRMED', $MODULE)}" data-toggle="tooltip" class="far fa-check-circle"></i></a>&nbsp;
            {/if}
            <a class="check-in-manual"><i title="{vtranslate('LBL_CHECK_IN_MANUAL', $MODULE)}" data-toggle="tooltip" class="far fa-check-square"></i></a>&nbsp;
        {/if}
    {/if}
{/strip}