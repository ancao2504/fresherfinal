{* Created By Vu Mai on 2022-10-24 to render telesales mainview *}

{strip}
	<div id="telesales-page" class="row-fluid">
		<input type="hidden" name="campaign_selector_purpose" value="">
		<input type="hidden" name="page" value="1">
		<input type="hidden" name="sortOrder" value="ASC">
		<input type="hidden" name="orderBy" value="">
		<input type="hidden" name="search_params" value="">

		<!-- Call Statistics -->
		<div id="call-statistics-container">
			{include file="modules/CPTelesales/tpl/TelesalesStatistics.tpl"}
		</div>

		<!-- General Filter -->
		<div id="general-filter" class="box shadowed">
			<div class="box-body">
				<div id="filter-container">
					<div class="row col-md-6">
						<div class="fieldValue col-md-3 paddingRight0 mr-2">
							<select class="modules-filter dropdown-filter">
								{foreach item=MODULE from=$MODULES_FILTER}
									<option value="{$MODULE}" {if $MODULE == 'Campaigns'}selected{/if}>{vtranslate($MODULE, $MODULE)}</option>
								{/foreach}
							</select>	
						</div>
						<div class="fieldValue col-md-8 paddingLeft0">
							<div class="entity-selector-wrapper campaign-selector-wrapper" data-module="Campaigns">
								<input type="hidden" class="entity-selector-input campaign-selector" name="campaign_selector_id" value="{$RECORD}" />
								<input class="inputElement entity-selector-display disabled" disabled {if !empty($INFO)}value="{$INFO.name}"{/if} />
								<button type="button" class="btn-entity-deselect cursorPointer"><i class="far fa-times-circle"></i></button>
								<button type="button" class="btn-entity-select cursorPointer"><i class="far fa-search"></i></button>
							</div>

							<select class="module-listview-filter dropdown-filter">
							</select>
						</div>
					</div>
					<div class="row col-md-6 text-right padding0 users-filter-wrapper">
						<div class="fieldLabel label-align-top col-md-4">{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_GENERAL_FILTER_IMPLEMENTED_EMPLOYEES', $MODULE_NAME)}</div>
						<select class="users-filter dropdown-filter col-md-8 paddingLeft0">
						</select>
						<span class="info-tooltip ml-2" data-toggle="tooltip" title="{vtranslate('LBL_TELESALES_CAMPAIGN_TELESALES_GENERAL_FILTER_IMPLEMENTED_EMPLOYEES_TOOLTIP', $MODULE_NAME)}"><i class="far fa-info-circle"></i></span>
					</div>
				</div>	
			</div>
		</div>

		<!-- Customer Status Filter -->
		<div id="customer-status-filter" class="box shadowed">
			<div class="box-body"></div>
		</div>

		<!-- List Content-->
		<div id="list-content" class="box shadowed">
		</div>
	</div>

	<script type="text/javascript" src="{vresource_url('layouts/v7/lib/jquery/floatThead/jquery.floatThead.js')}"></script>
{/strip}