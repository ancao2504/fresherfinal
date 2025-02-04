{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    {* Modified by Phu Vo on 2021.05.21 to style document summary widget *}
    <div class="paddingLeft5px document-summary-widget-content">
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		{assign var=DOWNLOAD_FILE_URL value=$RELATED_RECORD->getDownloadFileURL()}
		{assign var=DOWNLOAD_STATUS value=$RELATED_RECORD->get('filestatus')}
		{assign var=DOWNLOAD_LOCATION_TYPE value=$RELATED_RECORD->get('filelocationtype')}
		<div class="item-row row">
			<ul class="" style="padding-left: 0px;list-style-type: none;">
				<li>
					<div class="documentRelatedRecord">
						<span class="col-sm-5 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('notes_title')}">
								{$RELATED_RECORD->getDisplayValue('notes_title')}
							</a>
						</span>
                        <span class="col-sm-2">
                            {* Documents list view special actions "view file" and "download file" *}
                            {assign var=RECORD_ID value=$RELATED_RECORD->getId()}
                            {if isPermitted('Documents', 'DetailView', $RECORD_ID) eq 'yes'}
                                {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
                                {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                                    <a name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}"><i title="{vtranslate('LBL_DOWNLOAD_FILE', 'Documents')}" class="far fa-download alignMiddle"></i></a>&nbsp;
                                {/if}
                                {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                                    <a name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event,{$RECORD_ID})"><i title="{vtranslate('LBL_VIEW_FILE', 'Documents')}" class="far fa-eye alignMiddle"></i></a>&nbsp;
                                {/if}
                            {/if}
                        </span>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
    {* End Phu Vo *}
    </div>
    {assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
    {if $NUMBER_OF_RECORDS eq 5}
            <div class="row">
                    <div class="pull-right">
                            <a class="moreRecentDocuments cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
                    </div>
            </div>
    {/if}
{/strip}