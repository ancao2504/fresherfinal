{* Added by Hieu Nguyen on 2021-01-18 *}

{strip}
    <div id="module-guide-popup" class="modal-dialog modal-lg modal-content">
        {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$POPUP_TITLE}

        <form method="POST" class="form-horizontal">
            <div class="modal-body margin10">
                {$GUIDE_CONTENT}
            </div>
            <div class="modal-footer">
                <label><input type="checkbox" name="show_next_time" {if $SHOW_NEXT_TIME}checked{/if} /> {vtranslate('LBL_MODULE_GUIDE_SHOW_NEXT_TIME', 'Vtiger')}</label>
            </div>
        </form>
    </div>

    <link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/ModuleGuidePopup.css')}">
{/strip}