{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

{strip}
	<div class="col-sm-12 col-xs-12 module-action-bar clearfix coloredBorderTop">
		<div class="module-action-content clearfix">
			{* Modified by Phu Vo on 2021.01.19 to add container for add report button *}
			<div class="col-lg-5 col-md-5">
				<span>
					{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($MODULE)}
					{assign var=DEFAULT_FILTER_ID value=$MODULE_MODEL->getDefaultCustomFilter()}
					{if $DEFAULT_FILTER_ID}
						{assign var=CVURL value="&viewname="|cat:$DEFAULT_FILTER_ID}
						{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrl()|cat:$CVURL}
					{else}
						{assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrlWithAllFilter()}
					{/if}
					<a title="{vtranslate($MODULE, $MODULE)}" href='{$DEFAULT_FILTER_URL}'><h4 class="module-title pull-left">&nbsp;{vtranslate($MODULE, $MODULE)}&nbsp;</h4></a>
				</span>
				<span>
					<p class="current-filter-name pull-left">
						&nbsp;<span class="far fa-angle-right" aria-hidden="true"></span>
						&nbsp;
						{if $VIEW eq 'Detail' or $VIEW eq 'ChartDetail'}
							{$REPORT_NAME}
						{else}
							{$VIEW}
						{/if}
						&nbsp;
					</p>
				</span>
				{if $VIEWNAME}
					{if $VIEWNAME neq 'All'}
						{foreach item=FOLDER from=$FOLDERS}
							{if $FOLDER->getId() eq $VIEWNAME}
								{assign var=FOLDERNAME value=$FOLDER->getName()}
								{break}
							{/if}
						{/foreach}
					{else}
						{assign var=FOLDERNAME value=vtranslate('LBL_ALL_REPORTS', $MODULE)}
					{/if}
					<span>
						<p class="current-filter-name filter-name pull-left"><span class="far fa-angle-right" aria-hidden="true"></span>&nbsp;{$FOLDERNAME}&nbsp;</p>
					</span>
				{/if}
			</div>

			<div class="col-lg-7 col-md-7 pull-right">
				<div id="appnav" class="navbar-right">
					{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
						{assign var="childLinks" value=$LISTVIEW_BASICACTION->getChildLinks()}
						{if $childLinks && $LISTVIEW_BASICACTION->get('linklabel') == 'LBL_ADD_RECORD' && !isForbiddenFeature('CreateDynamicReports')}	{* Modified by Hieu Nguyen on 2021-08-20 to check if this feature can be displayed *}
							<ul class="nav navbar-nav">
								<li>
									<div class="btn-group">
										<button class="btn btn-default dropdown-toggle module-buttons" data-toggle="dropdown" id="{$MODULE}_listView_basicAction_Add">
											<i class="far fa-plus"></i>&nbsp;&nbsp;
											{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}&nbsp;
											<i class="caret icon-white"></i>
										</button>
										<ul class="dropdown-menu">
											{foreach item="childLink" from=$childLinks}
												{if $childLink->getLabel() eq 'LBL_CHARTS'}
													{assign var="ICON_CLASS" value='far fa-pie-chart'}
												{elseif $childLink->getLabel() eq 'LBL_DETAIL_REPORT'}
													{assign var="ICON_CLASS" value='far fa-chart-bar'}
												{/if}
												<li id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($childLink->getLabel())}" data-edition-disable="{$childLink->disabled}" data-edition-message="{$childLink->message}">
													<a {if $childLink->disabled neq '1'} {if stripos($childLink->getUrl(), 'javascript:') === 0} onclick='{$childLink->getUrl()|substr:strlen("javascript:")};' {else} href='{$childLink->getUrl()}' {/if} {else} href="javascript:void(0);" {/if}><i class='{$ICON_CLASS}' style="font-size:13px;"></i>&nbsp; {vtranslate($childLink->getLabel(), $MODULE)}</a>
												</li>
											{/foreach}
										</ul>
									</div>
								</li>
							</ul>
						{/if}
					{/foreach}
				</div>
			</div>
			{* End Phu Vo *}
		</div>
		{assign var=FIELDS_INFO value=Reports_Field_Model::getListViewFieldsInfo()}
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							},
							/* Added by Phuc on 2020.04.15 to allow add custom field for validation*/
							set: function(fieldData) {
								Object.keys(fieldData).map(function (fieldName, index) {
									fieldInfo[fieldName] = fieldData[fieldName];
								});
							}
							/* Ended by Phuc*/
						}
					};
				})();
			</script>
		{/if}
		<div class="rssAddFormContainer hide">
		</div>
	</div> 
{/strip}