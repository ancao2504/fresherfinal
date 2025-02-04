{*
    Name : AICameraIntegrationConfig
    Author : Phu Vo
    Date : 2021.04.02
*}

<form name="configs" style="padding-bottom: 20px;">
    {if $MODE == 'ShowList'}
        <div class="editViewContents vendor-select-container">
            <div class="fieldBlockContainer">
                <h4 class="fieldBlockHeader">{vtranslate('LBL_AI_CAMERA_INTEGRATION_CONFIG', $MODULE_NAME)}</h4>
                <hr />
                <p>{vtranslate('LBL_AI_CAMERA_LIST_DESCRIPTION', $MODULE_NAME)}</p>

                <div class="fieldBlockContainer">
                    <h4>{vtranslate('LBL_AI_CAMERA_CONNECT_TO_VENDOR', $MODULE_NAME)}</h4>
                    <hr />
                    <div class="vendor-search-container">
                        <div class="vendor-search">
                            <input name="search_input" class="search-input" placeholder="{vtranslate('LBL_AI_CAMERA_SEARCH_PLACEHOLDER', $MODULE_NAME)}" />
                            <i class="far fa-search search-icon"></i>
                        </div>
                    </div>
                    <div class="vendors-container">
                        <div class="vendors">
                            {* Refactored by Hieu Nguyen on 2021-06-08 to render provider list dynamically based on config *}
                            {foreach from=$PROVIDERS key=PROVIDER_NAME item=PROVIDER_INFO}
                                {assign var="IS_CONNECTED" value=$ACTIVE_PROVIDER == $PROVIDER_NAME}
                                <div class="vendor" data-name="{$PROVIDER_NAME}" data-display-name="{$PROVIDER_INFO.display_name}" data-connected="{$IS_CONNECTED}">
                                    <div class="vendor-logo-container">
                                        <div class="vendor-logo">
                                            <img class="vendor-logo-image" src="{$PROVIDER_INFO.logo_path}" />
                                        </div>
                                    </div>
                                    <div class="vendor-body-container">
                                        <div class="vendor-title-container">
                                            <div class="vendor-title"><h5>{$PROVIDER_INFO.display_name}</h5></div>
                                            {if $IS_CONNECTED}
                                                <div class="vendor-status"><i>({vtranslate('LBL_AI_CAMERA_CONNECTED', $MODULE_NAME)})</i></div>
                                            {else}
                                                <div class="vendor-status"><i>({vtranslate('LBL_AI_CAMERA_DISCONNECTED', $MODULE_NAME)})</i></div>
                                            {/if}
                                        </div>
                                        <div class="vendor-body">
                                            <div class="description-container">
                                                <div class="description">
                                                    <p title="{$PROVIDER_INFO[$INTRO_KEY]}">{$PROVIDER_INFO[$INTRO_KEY]}</p>
                                                </div>
                                                <div class="instruction">
                                                    <a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_AI_CAMERA_CONNECT_INSTRUCTION', $MODULE_NAME)}</a>
                                                </div>
                                            </div>
                                            <div class="actions-container">
                                                <div class="actions">
                                                    {if $ACTIVE_PROVIDER == $PROVIDER_NAME}
                                                        <button class="btn btn-danger disconnect-btn">{vtranslate('LBL_AI_CAMERA_UNLINK_VENDOR', $MODULE_NAME)}</button>
                                                    {else if $ACTIVE_PROVIDER == ''}
                                                        <button class="btn btn-primary connect-btn">{vtranslate('LBL_AI_CAMERA_LINK_VENDOR', $MODULE_NAME)}</button>
                                                    {else}
                                                        <button class="btn btn-primary" disabled>{vtranslate('LBL_AI_CAMERA_LINK_VENDOR', $MODULE_NAME)}</button>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                            {* End Hieu Nguyen *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {else if $MODE == 'ShowDetail'}
        {assign var="PROVIDER_INFO" value=$PROVIDERS[$ACTIVE_PROVIDER]}
        <input type="hidden" name="active_provider" value="{$ACTIVE_PROVIDER}" />
        <input type="hidden" name="provider_display_name" value="{$PROVIDER_INFO.display_name}" />

        {if $ACTIVE_PROVIDER == 'HanetAICamera'}
            <div class="editViewContents vendor-detail-container">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_AI_CAMERA_INTEGRATION_CONFIG', $MODULE_NAME)}</h4>
                    <hr />
                    <p>{vtranslate('LBL_AI_CAMERA_PROVIDER_DESCRIPTION', $MODULE_NAME, ['%provider_name%' => $PROVIDER_INFO.display_name])}</p>
                    <div class="fieldBlockContainer">
                        <div class="block-title-container">
                            <div class="block-title">
                                <h5>{$PROVIDER_INFO.display_name}</h5>
                                <button class="btn btn-link edit-connect" title="{vtranslate('LBL_AI_CAMERA_EDIT_VENDOR_INFORMATION', $MODULE_NAME)}" data-name="{$ACTIVE_PROVIDER}" data-display-name="{$PROVIDER_INFO.display_name}" data-app_id="{$CONFIG['app_id']}" data-secret_key="{$CONFIG['secret_key']}"><i class="far fa-edit"></i></button>
                            </div>
                            <div class="block-instruction">
                                <a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_AI_CAMERA_CONNECT_INSTRUCTION', $MODULE_NAME)}</a>
                            </div>
                        </div>
                        <hr />
                        <div class="vendor-logo-container">
                            <div class="vendor-logo">
                                <img class="vendor-logo-image" src="{$PROVIDER_INFO.logo_path}" />
                            </div>
                        </div>
                        <div class="places-container">
                            <div class="places">
                                {foreach from=$CONFIG['cameras'] item=place}
                                    <div class="place-container" data-id="{$place['id']}">
                                        <div class="place">
                                            <div class="place-title-container">
                                                <div class="place-title">
                                                    <p><i class="far fa-map-marker-alt"></i> <span>{$place['name']}</span></p>
                                                </div>
                                                <div class="place-actions">
                                                    <button class="btn btn-link delete-place" title="{vtranslate('LBL_DELETE', $MODULE_NAME)}" data-id="{$place['id']}" data-name="{$place['name']}"><i class="far fa-trash-alt"></i></button>
                                                </div>
                                            </div>
                                            <div class="place-description-container">
                                                <div class="place-description">
                                                    <p><span>{$place['address']}</span></p>
                                                </div>
                                            </div>
                                            <div class="cameras-container">
                                                <div class="cameras">
                                                    {foreach from=$place['linked_cameras'] item=item}
                                                        <div class="camera-container">
                                                            <div class="camera">
                                                                <div class="camera-logo">
                                                                    <i class="far fa-cctv"></i>
                                                                </div>
                                                                <div class="camera-description">
                                                                    <div class="camera-title">
                                                                        <p>{$item['name']}</p>
                                                                    </div>
                                                                    <div class="camera-number">
                                                                        <p>{$item['id']}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {foreachelse}
                                                        <div class="camera-container text-center">
                                                            <span>{vtranslate('LBL_AI_CAMERA_NO_CAMERA', $MODULE_NAME)}</span>
                                                        </div>
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {foreachelse}
                                    <div class="place-container text-center">
                                        <span>{vtranslate('LBL_AI_CAMERA_NO_PLACE', $MODULE_NAME)}</span>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                        <div class="bottom-actions-container">
                            <div class="bottom-actions">
                                <button class="btn btn-outline add-camera"><i class="far fa-plus"></i> {vtranslate('LBL_AI_CAMERA_ADD_CAMERA', $MODULE_NAME)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {* Modified by Hieu Nguyen on 2021-06-08 to support CMC Cloud Camera *}
        {if $ACTIVE_PROVIDER == 'CMCCloudCamera'}
            <div class="editViewContents vendor-detail-container">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_AI_CAMERA_INTEGRATION_CONFIG', $MODULE_NAME)}</h4>
                    <hr />
                    <p>{vtranslate('LBL_AI_CAMERA_PROVIDER_DESCRIPTION', $MODULE_NAME, ['%provider_name%' => $PROVIDER_INFO.display_name])}</p>
                    <div class="fieldBlockContainer">
                        <div class="block-title-container">
                            <div class="block-title">
                                <h5>{$PROVIDER_INFO.display_name}</h5>
                                <button class="btn btn-link edit-connect" title="{vtranslate('LBL_AI_CAMERA_EDIT_VENDOR_INFORMATION', $MODULE_NAME)}" data-name="{$ACTIVE_PROVIDER}" data-display-name="{$PROVIDER_INFO.display_name}" data-domain="{$CONFIG.credentials.domain}" data-access_token="{$CONFIG.credentials.access_token}"><i class="far fa-edit"></i></button>
                            </div>
                            <div class="block-instruction">
                                <a target="_blank" href="{$PROVIDER_INFO.guide_url}">{vtranslate('LBL_AI_CAMERA_CONNECT_INSTRUCTION', $MODULE_NAME)}</a>
                            </div>
                        </div>
                        <hr />
                        <div class="vendor-logo-container">
                            <div class="vendor-logo">
                                <img class="vendor-logo-image" src="{$PROVIDER_INFO.logo_path}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {* End Hieu Nguyen *}
        
        <div class="modal-overlay-footer clearfix">
            <div class="row clear-fix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
                    <button class="btn btn-outline-danger disconnect-btn">{vtranslate('LBL_AI_CAMERA_UNLINK_VENDOR', $MODULE_NAME)}</button>
                    <a href="index.php?module=Vtiger&parent=Settings&view=AICameraIntegrationConfig" class="btn btn-outline">{vtranslate('LBL_AI_CAMERA_BACK', $MODULE_NAME)}</a>
                </div>
            </div> 
        </div>
    {/if}
</form>

<div style="display: none">
    <div class="editAiCameraModal modal-dialog modal-md modal-content">
        {assign var=HEADER_TITLE value=vtranslate('LBL_AI_CAMERA_SELECT_AI_CAMERA_INFO', $MODULE_NAME)}
        {include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$HEADER_TITLE}
        <form name="edit_ai_camera" class="form-horizontal">
            <input type="hidden" name="module" value="Vtiger" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" name="action" value="SaveAICameraIntegrationConfig" />
            <input type="hidden" name="mode" value="connectVendor" />
            <input type="hidden" name="config[active_provider]" />
            <input type="hidden" name="new" value="0" />
            <div class="form-content fancyScrollbar" style="padding: 15px">
                <table provider="HanetAICamera" class="table no-border fieldBlockContainer">
                    <tr>
                        <td class="fieldLabel col-lg-4">{vtranslate('LBL_AI_CAMERA_APP_ID', $MODULE_NAME)} <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input name="config[app_id]" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                    <tr>
                        <td class="fieldLabel col-lg-4">{vtranslate('LBL_AI_CAMERA_SECRET_KEY', $MODULE_NAME)} <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input type="password" name="config[secret_key]" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                </table>

                {* Added by Hieu Nguyen on 2021-06-08 to support CMC Cloud Camera *}
                <table provider="CMCCloudCamera" class="table no-border fieldBlockContainer">
                    <tr>
                        <td class="fieldLabel col-lg-4">{vtranslate('LBL_AI_CAMERA_DOMAIN', $MODULE_NAME)} <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input name="config[credentials][domain]" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                    <tr>
                        <td class="fieldLabel col-lg-4">{vtranslate('LBL_AI_CAMERA_ACCESS_TOKEN', $MODULE_NAME)} <span class="redColor">*</span></td>
                        <td class="fieldValue col-lg-8"><input type="password" name="config[credentials][access_token]" data-rule-required="true" class="inputElement" /></td>
                    </tr>
                </table>
                {* End Hieu Nguyen *}
            </div>
            <div class="modal-footer">
                <center>
                    <button class="btn btn-success new" type="submit" name="saveButton"><strong>{vtranslate('LBL_AI_CAMERA_NEXT', $MODULE_NAME)}</strong></button>
                    <button class="btn btn-success edit" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE_NAME)}</strong></button>
                </center>
            </div>
        </form>
    </div>
</div>