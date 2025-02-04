{* Added by Hieu Nguyen on 2018-08-06 *}
{strip}
	<div class="form-group">
		<label class="control-label fieldLabel col-sm-5">
			<span>{vtranslate('LBL_BLOCK_LABEL_DISPLAY_NAME_VN', $QUALIFIED_MODULE)}</span>
			<span class="redColor">*</span>
		</label>
		<div class="controls col-sm-6">
			<input type="text" name="labelDisplayVn" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%" />
			<input type="hidden" name="labelDisplayVnChanged" value="0" />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label fieldLabel col-sm-5">
			<span>{vtranslate('LBL_BLOCK_LABEL_DISPLAY_NAME_EN', $QUALIFIED_MODULE)}</span>
			<span class="redColor">*</span>
		</label>
		<div class="controls col-sm-6">
			<input type="text" name="labelDisplayEn" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%" />
			<input type="hidden" name="labelDisplayEnChanged" value="0" />
		</div>
	</div>
	<div class="form-group">
		<label class="control-label fieldLabel col-sm-5">
			<span>{vtranslate('LBL_BLOCK_LABEL_KEY_NAME', $QUALIFIED_MODULE)}</span>
			<span class="redColor">*</span>
		</label>
		<div class="controls col-sm-6">
			<input type="text" name="label" class="col-sm-3 inputElement" data-rule-required="true" style="width: 75%" />
		</div>
	</div>
{/strip}