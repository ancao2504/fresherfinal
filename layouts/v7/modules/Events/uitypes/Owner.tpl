{* Added by Hieu Nguyen on 2020-02-20 to make this field in Event module single selection and select User only *}

{strip}
    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}

    {if $FIELD_MODEL->get('uitype') eq '53'}
        {assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
        {assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
        {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}

        {if $FIELD_NAME eq ''}
            {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
        {/if}

        {if $FIELD_VALUE eq ''}
            {assign var=FIELD_VALUE value=$CURRENT_USER_ID}
        {/if}

        {include file='modules/Events/tpls/CustomOwnerField.tpl'}
    {/if}
{/strip}
