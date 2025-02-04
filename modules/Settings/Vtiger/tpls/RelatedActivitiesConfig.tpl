{*
    RelatedActivitiesConfig
    Author: Phu Vo
    Date: 2020.08.29
*}
{strip}
    <form autocomplete="off" name="configs">
        <div class="editViewBody">
            <div class="editViewContents">
                <div class="fieldBlockContainer">
                    <h4 class="fieldBlockHeader">{vtranslate('LBL_RELATED_ACTIVITIES_DATA_PERMISSION', $MODULE_NAME)}</h4>
                    <hr />
                    <div class="formCell">{vtranslate('LBL_RELATED_ACTIVITIES_ON_SUBPANEL', $MODULE_NAME)}:</div>
                    <div class="formCell flex-container">
                        <div class="flex-item" style="padding-right: 8px">
                            <input type="checkbox" name="configs[main_owner_full_access]" class="inputElement cursorPointer bootstrap-switch" data-on-text="On" data-off-text="Off" {if $CONFIGS['main_owner_full_access'] == 1}checked{/if} />
                        </div>
                        <div class="flex-item">
                            <div>{vtranslate('LBL_RELATED_ACTIVITIES_FULL_PERMISSION_DESCRIPTION', $MODULE_NAME)}</div>
                            <div class="light-description"><i>{vtranslate('LBL_RELATED_ACTIVITIES_FULL_PERMISSION_SUBDESCRIPTION', $MODULE_NAME)}</i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-overlay-footer clearfix">
            <div class="row clear-fix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
                    <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
                </div>
            </div> 
        </div>
    </form>

    <link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}"/>
    <script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
{/strip}