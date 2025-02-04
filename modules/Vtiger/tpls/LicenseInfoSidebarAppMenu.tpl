{* Added by Hieu Nguyen on 2022-10-21 to render License Info at App Menu *}

{strip}
	{assign var='LICENSE_INFO' value=parseLicense()}
	<input type="hidden" name="license_start_date" value="{$LICENSE_INFO.license.start_date}" />

	{if $LICENSE_INFO.license.lifetime_license == false || $LICENSE_INFO.license.max_storage !== -1 || $LICENSE_INFO.license.max_normal_users !== -1}
		<div id="license-info">
			<div id="package-name">{vtranslate('LBL_LICENCE_INFO_APP_MENU_PACKAGE_NAME', 'Vtiger', ['%package_name' => $LICENSE_INFO.license.package_name])}</span></div>	{* Modified by Vu Mai on 2022-12-21 to refactor label *}
			<div id="license-limit">
				{if $LICENSE_INFO.license.lifetime_license == false}
					<div class="factor">
						{assign var='REMAINING_DAYS' value=getLicenseRemainingDays()}
						{assign var='UPDATED_DATE' value=date_create($LICENSE_INFO.license.updated_date)}
						{assign var='EXPIRE_DATE' value=date_create($LICENSE_INFO.license.expire_date)}
						{assign var='DATE_DIFFS' value=date_diff($UPDATED_DATE, $EXPIRE_DATE)}
						{assign var='TOTAL_DAYS' value=$DATE_DIFFS->days}
						{assign var='DATE_PROGRESS' value=(($TOTAL_DAYS - $REMAINING_DAYS) / $TOTAL_DAYS) * 100}

						<div class="info">
							<input type="hidden" name="license_remaining_days" value="{$REMAINING_DAYS}" />
							<div class="pull-left">{vtranslate('LBL_LICENCE_INFO_APP_MENU_REMAINING_DAYS', 'Vtiger', ['%remaining_days' => $REMAINING_DAYS])}</div>	{* Modified by Vu Mai on 2022-12-21 to refactor label *}
							<div class="pull-right">{$LICENSE_INFO.license.expire_date}</div>
							<div class="clearFix"></div>
						</div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="{$DATE_PROGRESS}" aria-valuemin="0" aria-valuemax="100" style="width: {$DATE_PROGRESS}%;"></div>
						</div>
					</div>
				{/if}

				{if $LICENSE_INFO.license.max_storage !== -1}
					<div class="factor">
						{assign var='MAX_STORAGE' value=$LICENSE_INFO.license.max_storage}
						{assign var='USED_STORAGE' value=round(getUsedStorage(), 2)}
						{assign var='STORAGE_PROGRESS' value=($USED_STORAGE / $MAX_STORAGE) * 100}
						
						<div class="info">
							<div class="pull-left">{$USED_STORAGE}GB/{$MAX_STORAGE}GB</div>
							<div class="clearFix"></div>
						</div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="{$STORAGE_PROGRESS}" aria-valuemin="0" aria-valuemax="100" style="width: {$STORAGE_PROGRESS}%;"></div>
						</div>
					</div>
				{/if}

				{if $LICENSE_INFO.license.max_normal_users !== -1}
					<div class="factor">
						{assign var='MAX_USERS' value=$LICENSE_INFO.license.max_normal_users}
						{assign var='USED_USERS' value=getUsersCount('normal_user')}
						{assign var='USER_PROGRESS' value=($USED_USERS / $MAX_USERS) * 100}

						<div class="info">
							<div class="pull-left">{$USED_USERS} users/{$MAX_USERS} users</div>
							<div class="clearFix"></div>
						</div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" aria-valuenow="{$USER_PROGRESS}" aria-valuemin="0" aria-valuemax="100" style="width: {$USER_PROGRESS}%;"></div>
						</div>
					</div>
				{/if}
			</div>
		</div>
	{/if}
{/strip}