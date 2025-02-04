{* Added by Hieu Nguyen on 2022-10-24 *}

{strip}
	<div id="wizard" class="breadcrumb text-center" data-step="1">
		<input type="hidden" name="wizard" value="true" />

		<ul class="crumbs">
			<li class="step active step1" data-step="1" style="z-index:9">
				<a href="javascript:void(0)">
					<span class="stepNum">1</span>
					<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_GENERAL_INFO', $MODULE_NAME)}</span>
				</a>
			</li>
			<li class="step step2" data-step="2" style="z-index:8">
				<a href="javascript:void(0)">
					<span class="stepNum">2</span>
					<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_SELECT_MARKETING_LISTS', $MODULE_NAME)}</span>
				</a>
			</li>
			<li class="step step3" data-step="3" style="z-index:6">
				<a href="javascript:void(0)">
					<span class="stepNum">3</span>
					<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_SELECT_USERS', $MODULE_NAME)}</span>
				</a>
			</li>
			<li class="step step4" data-step="4" style="z-index:5">
				<a href="javascript:void(0)">
					<span class="stepNum">4</span>
					<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_DISTRIBUTE_DATA', $MODULE_NAME)}</span>
				</a>
			</li>
			<li class="step step5" data-step="5" style="z-index:4">
				<a href="javascript:void(0)">
					<span class="stepNum">5</span>
					<span class="stepText">{vtranslate('LBL_TELESALES_CAMPAIGN_WIZARD_ESTIMATION', $MODULE_NAME)}</span>
				</a>
			</li>
		</ul>
	</div>

	{include file="modules/Campaigns/tpls/Telesales/New/Form.tpl"}
{strip}