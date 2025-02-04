{* Added by Hieu Nguyen on 2022-11-17 *}

{strip}
	<div id="config-page" class="row-fluid padding20">
		<form id="system-log" data-mode="{$MODE}">
			<div class="box shadowed">
				<div class="box-header">
					{vtranslate('LBL_SYSTEM_LOG', $MODULE_NAME)}
				</div>
				<div class="box-body">
					<div class="form-content fancyScrollbar padding20">
						<div class="row form-group">
							<div class="col-md-2 label-align-top">
								{vtranslate('LBL_SYSTEM_LOG_SELECT_LOG_FILE', $MODULE_NAME)}
							</div>
							<div class="fieldValue col-md-10 paddingleft0">
								<select name="log_file" class="inputElement select2" placeholder="{vtranslate('LBL_SYSTEM_LOG_SELECT_LOG_FILE', $MODULE_NAME)}">
									<option value=""></option>
									{foreach from=$LOG_FILES key=KEY item=FILE_NAME}
										<option value="{$FILE_NAME}">{$FILE_NAME}</option>
									{/foreach}
								</select>
								&nbsp;
								<button type="button" id="btn-reload" class="btn btn-link hide"><i class="far fa-refresh"></i></button>
							</div>
						</div>
						<div class="row form-group">
							<div class="col-md-12 label-align-top">
								{vtranslate('LBL_SYSTEM_LOG_LOG_CONTENT', $MODULE_NAME)}
							</div>
							<div class="fieldValue col-md-12">
								<textarea name="log_content" readonly></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}