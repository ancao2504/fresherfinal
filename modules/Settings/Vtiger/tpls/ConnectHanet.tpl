{*
    Name: ConnectHanet.tpl
    Author: Phu Vo
    Date: 2021.04.24
*}

{strip}
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{vtranslate('LBL_AI_CONNECT_HANET', $MODULE_NAME)}</title>
        <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/lib/todc/css/bootstrap.min.css')}">
        <link type="text/css" rel="stylesheet" href="{vresource_url('layouts/v7/resources/fonts/fontawsome6/css/all.css')}">
        <link type="text/css" rel="stylesheet" href="{vresource_url('modules/Settings/Vtiger/resources/ConnectHanet.css')}" />
        <script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
    </head>
    <body>
        <div class="logos">
            <div class="logo">
                <img class="logo-image" src="resources/images/hanet.png" />
            </div>
            <div class="connect">
                <i class="far fa-exchange"></i>
            </div>
            <div class="logo">
                <img class="logo-image" src="layouts/v7/resources/Images/logo.png" />
            </div>
        </div>
        {if $MODE == 'PlaceList'}
            <h2>{vtranslate('LBL_AI_CAMERA_SELECT_PLACE', $MODULE_NAME)}</h2>
            <div class="places-container">
                <div class="places">
                    {foreach from=$PLACES item=item key=key name=name}
                    <div class="place-container">
                        <div class="place" data-href="index.php?module=Vtiger&parent=Settings&view=ConnectHanet&targetView=CameraList&placeId={$item['id']}&placeName={$item['name']}&placeAddress={$item['address']}">
                            <div class="place-title">
                                <i class="far fa-map-marker-alt"></i>
                                <span>{$item['name']}</span>
                            </div>
                            <div class="place-description">
                                <p>{$item['address']}</p>
                            </div>
                        </div>
                    </div>
                    {/foreach}
                </div>
            </div>
        {else if $MODE == 'CameraList'}
            <form name="connect">
                <input type="hidden" name="module" value="Vtiger" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="view" value="ConnectHanet" />
                <input type="hidden" name="targetView" value="Complete" />
                <input type="hidden" name="place_data[id]" value="{$PLACE_ID}" />
                <input type="hidden" name="place_data[name]" value="{$PLACE_NAME}" />
                <input type="hidden" name="place_data[address]" value="{$PLACE_ADDRESS}" />

                <h2>{vtranslate('LBL_AI_CAMERA_SELECT_CAMERA_LIST', $MODULE_NAME)}</h2>
                <div class="cameras-container">
                    <div class="cameras">
                        {foreach from=$CAMERAS item=item key=key name=name}
                            <input type="hidden" name="place_data[linked_cameras][{$item['deviceID']}][id]" value="{$item['deviceID']}" />
                            <input type="hidden" name="place_data[linked_cameras][{$item['deviceID']}][name]" value="{$item['deviceName']}" />
                            <div class="camera-container">
                                <div class="camera">
                                    <div class="camera-logo">
                                        <i class="far fa-cctv"></i>
                                    </div>
                                    <div class="camera-description">
                                        <div class="camera-title">
                                            <p>{$item['deviceName']}</p>
                                        </div>
                                        <div class="camera-number">
                                            <p>{$item['deviceID']}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {foreachelse}
                            <div class="no-camera">
                                <p>{vtranslate('LBL_AI_CAMERA_SELECT_NO_CAMERA_IN_PLACE', $MODULE_NAME)}</p>
                            </div>
                        {/foreach}
                    </div>
                </div>
                <div class="action-container">
                    <button type="submit" class="btn btn-primary" {if count($CAMERAS) == 0}disabled{/if}>{vtranslate('LBL_AI_CAMERA_CONNECT', $MODULE_NAME)}</button>
                </div>
            </form>
        {else if $MODE == 'Complete'}
            <div class="status text-center">
                <h2>{vtranslate('LBL_AI_CAMERA_SELECT_CONNECT_SUCCESSFUL', $MODULE_NAME)}</h2>
                <p class="description">{vtranslate('LBL_AI_CAMERA_RELOAD_INSTRUCTION', $MODULE_NAME)}</p>
                <div class="action-container">
                    <button class="btn btn-primary completeBtn">{vtranslate('LBL_AI_CAMERA_CLOSE', $MODULE_NAME)}</button>
                </div>
            </div>
        {/if}
        <script src="{vresource_url('modules/Settings/Vtiger/resources/ConnectHanet.js')}"></script>
    </body>
    </html>
{/strip}