{*
    File SocialIntegrationConfigFBFanpageSelector.tpl
    Author: Hieu Nguyen
    Date: 2020-01-14
    Purpose: to render the fb fanpage list to connect
*}

{strip}
    {* Added by Phu Vo on 2020.02.11 to styling popup *}
    <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/font-awesome/css/font-awesome.css')}" />
    <link type="text/css" rel="stylesheet" href="{vresource_url('modules/Settings/Vtiger/tpls/SocialIntegrationConfigFBFanpageSelector.css')}" />
    <script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>

    <form action="webhook.php" method="POST">
        <div>
            <span class="hint">{vtranslate('LBL_SOCIAL_CONFIG_FB_FANPAGE_SELECTOR_HINT', 'CPSocialIntegration')}</span>
        </div>

        <input type="hidden" name="name" value="FacebookConnector" />
        <input type="hidden" name="action" value="ConnectFanpage" />

        {foreach item=FANPAGE from=$FANPAGE_LIST}
            <div class="row">
                <label class="checkbox-container">
                    <input type="checkbox" name="fanpage_ids[]" value="{$FANPAGE['id']}" />
                    <span class="checkbox-checkmark"></span>
                </label>
                <img src="{$FANPAGE.avatar}" width="50px" style="border-radius: 50%"/>
                <span>{$FANPAGE.name}</span>
            </div>
        {/foreach}

        <div class="row">
            <button type="submit" class="btn btn-primary">{vtranslate('LBL_SOCIAL_CONFIG_FB_FANPAGE_SELECTOR_CONNECT_BUTTON', 'CPSocialIntegration')}</button>
        </div>
    </form>

    <script src="{vresource_url('modules/Settings/Vtiger/resources/SocialIntegrationConfigFBFanpageSelector.js')}"></script>
{strip}