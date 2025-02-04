{* Refactored by Hieu Nguyen on 2021-11-15 to boost performance *}

{strip}
	<div id="config-page">
		<div class="box shadowed">
			<div class="box-header">
				<div class="header-title"> <!-- Modify by Vu Mai on 2022-07-18 -->
					{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG', $MODULE_NAME)}

					{if $CONFIG.credentials.base_url}
						<button id="btn-reconnect" class="btn btn-link" title="{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_RECONNECT', $MODULE_NAME)}"><i class="far fa-edit"></i></button>
						<button id="btn-disconnect" class="btn btn-link" title="{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_DISCONNECT', $MODULE_NAME)}"><i class="far fa-link-slash redColor"></i></button>
					{/if}
				</div>
				<div id="guide-url" class="pull-right marginleft-auto" > <!-- Modify by Vu Mai on 2022-07-18 -->
					<a target="_blank" href="{$GUIDE_URL}">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_INSTRUCTION', $MODULE_NAME)}</a>
				</div>
			</div>

			<div class="box-body">
				<div id="connection-info">
					{if $CONFIG.credentials.base_url}
						<div>
							<table class="table no-border fieldBlockContainer">
								<tr>
									<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAUTIC_INSTANCE_URL', $MODULE_NAME)}</td>
									<td class="fieldValue"><input name="base_url" class="inputElement" value="{$CONFIG.credentials.base_url}" disabled /></td>
								</tr>
								<tr>
									<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CLIENT_ID', $MODULE_NAME)}</td>
									<td class="fieldValue"><input name="client_id" class="inputElement" value="{$CONFIG.credentials.client_id}" disabled /></td>
								</tr>
								<tr>
									<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CLIENT_SECRET', $MODULE_NAME)}</td>
									<td class="fieldValue"><input name="client_secret" class="inputElement" value="{$CONFIG.credentials.client_secret}" disabled /></td>
								</tr>
							</table>

							{if $CONNECTED}
								<div class="greenColor"><i class="far fa-check"></i> {vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECTION_SUCCESS', $MODULE_NAME)}</div>
							{else}
								<div class="redColor"><i class="far fa-xmark"></i> {vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECTION_ERROR', $MODULE_NAME)}</div>
							{/if}
						</div>
					{else}
						<div><button type="button" id="btn-connect" class="btn btn-primary"><i class="far fa-plug"></i> {vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECT', $MODULE_NAME)}</button></div>
					{/if}

					<div style="display: none">
						<div id="authorize-modal" class="modal-dialog modal-md modal-content">
							{assign var=HEADER_TITLE value=vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECT_MAUTIC', $MODULE_NAME)}
							{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=$HEADER_TITLE}

							<form name="authorize-form" target="_blank" class="form-horizontal">
								<input type="hidden" name="module" value="Vtiger" />
								<input type="hidden" name="parent" value="Settings" />
								<input type="hidden" name="view" value="ConnectMautic" />

								<div class="form-content fancyScrollbar padding10">
									<table class="table no-border fieldBlockContainer">
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAUTIC_INSTANCE_URL', $MODULE_NAME)} <span class="redColor">*</span></td>
											<td class="fieldValue"><input type="text" name="base_url" data-rule-required="true" class="inputElement" /></td>
										</tr>
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CLIENT_ID', $MODULE_NAME)} <span class="redColor">*</span></td>
											<td class="fieldValue"><input type="text" name="client_id" data-rule-required="true" class="inputElement" /></td>
										</tr>
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CLIENT_SECRET', $MODULE_NAME)} <span class="redColor">*</span></td>
											<td class="fieldValue"><input type="text" name="client_secret" data-rule-required="true" class="inputElement" /></td>
										</tr>
									</table>
								</div>
								<div class="modal-footer">
									<center>
										<button class="btn btn-success" type="submit" name="submit"><strong>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECT', $MODULE_NAME)}</strong></button>
										<button class="btn btn-default" type="button" name="cancel" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE_NAME)}</strong></button>
									</center>
								</div>
							</form>
						</div>
					</div>
				</div>

				{if $CONNECTED}
					<hr style="clear:both" />

					<form id="settings" name="settings">
						<div class="contents tabbable">
							<ul class="nav nav-tabs marginBottom10px">
								<li class="common-settings active"><a data-toggle="tab" href="#common-settings"><strong>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_COMMON_SETTINGS', $MODULE_NAME)}</strong></a></li>							
								<li class="mapping-fields"><a data-toggle="tab" href="#mapping-fields"><strong>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAPPING_FIELD_SETTINGS', $MODULE_NAME)}</strong></a></li>
								{* Commented out by Hieu Nguyen on 2021-11-02 to disable unused logic *}
								{* <li class="mapping-stages"><a data-toggle="tab" href="#mapping-stages"><strong>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAPPING_STAGE_SETTINGS', $MODULE_NAME)}</strong></a></li>
								<li class="mapping-stage-segments"><a data-toggle="tab" href="#mapping-stage-segments"><strong>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAPPING_STAGE_SEGMENT_SETTINGS', $MODULE_NAME)}</strong></a></li> *}
							</ul>

							{* Common Settings *}
							<div class="contents tab-content overflowVisible">
								<div id="common-settings" class="tabcontent tab-pane active">
									<table class="table no-border fieldBlockContainer">
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_BATCH_LIMIT', $MODULE_NAME)}</td>
											<td class="fieldValue"><input type="text" class="inputElement" name="batch_limit" data-rule-required="true" value="{$CONFIG.batch_limit}" /></td>
										</tr>
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WITHIN_DAYS', $MODULE_NAME)}</td>
											<td class="fieldValue">
												<select name="sync_mautic_history_within_days" id="sync_mautic_history_within_days" value={$CONFIG.sync_mautic_history_within_days}>
													<option value="30" {if $CONFIG.sync_mautic_history_within_days == 30}selected{/if}>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WITHIN_30_DAYS', $MODULE_NAME)}</option>
													<option value="60" {if $CONFIG.sync_mautic_history_within_days == 60}selected{/if}>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WITHIN_60_DAYS', $MODULE_NAME)}</option>
													<option value="90" {if $CONFIG.sync_mautic_history_within_days == 90}selected{/if}>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WITHIN_90_DAYS', $MODULE_NAME)}</option>
												</select>
												&nbsp;
												<i class="far fa-question-circle tooltip-helper" data-toggle="tooltip" title="{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WITHIN_DAYS_TOOLTIP', $MODULE_NAME)}"></i>
											</td>
										</tr>
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WHEN_CUSTOMER_IS_CONVERTED', $MODULE_NAME)}</td>
											<td class="fieldValue">
												<select name="sync_mautic_history_when_customer_is_converted" id="sync_mautic_history_when_customer_is_converted" value={$CONFIG.sync_mautic_history_when_customer_is_converted}>
													<option value="none" {if $CONFIG.sync_mautic_history_when_customer_is_converted == 'none'}selected{/if}>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_DO_NOTHING', $MODULE_NAME)}</option>
													<option value="move" {if $CONFIG.sync_mautic_history_when_customer_is_converted == 'move'}selected{/if}>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MOVE', $MODULE_NAME)}</option>
													<option value="copy" {if $CONFIG.sync_mautic_history_when_customer_is_converted == 'copy'}selected{/if}>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_COPY', $MODULE_NAME)}</option>
												</select>
												&nbsp;
												<i class="far fa-question-circle tooltip-helper" data-toggle="tooltip" title="{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_SYNC_MAUTIC_HISTORY_WHEN_CUSTOMER_IS_CONVERTED_TOOLTIP', $MODULE_NAME)}"></i>
											</td>
										</tr>
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_DELETE_CONTACT_IN_MAUTIC_WHEN_DELETE_IN_CRM', $MODULE_NAME)}</td>
											<td class="fieldValue"><input type="checkbox" class="listViewEntriesCheckBox" {if !isset($CONFIG.delete_contact_in_mautic_when_delete_in_crm) || $CONFIG.delete_contact_in_mautic_when_delete_in_crm == 1} checked{/if} name="delete_contact_in_mautic_when_delete_in_crm" value="1" /></td>
										</tr>
										<tr>
											<td class="fieldLabel">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MIN_POINTS_TO_SYNC_CUSTOMER_INFO', $MODULE_NAME)}</td>
											<td class="fieldValue"><input type="text" class="inputElement" name="min_points_to_sync_data" data-rule-required="true" value="{$CONFIG.min_points_to_sync_data}" /></td>
										</tr>
									</table>
								</div>

								{* Mapping fields *}
								<div id="mapping-fields" class="tabcontent tab-pane">
									{foreach from=$MAPPING_FIELDS item=MODULE_FIELDS key=MAPPING_MODULE}
										{assign var=HAS_CONFIG value=$CONFIG.mapping_fields[{$MAPPING_MODULE|lower}]|count}

										<div class="box mapping-module">
											<div class="box-header toggle-mapping">
												<label class="cursorPointer">
													<input type="checkbox" value="{$MAPPING_MODULE|lower}" {if $HAS_CONFIG}checked{/if} />
													&nbsp;{vtranslate($MAPPING_MODULE, $MAPPING_MODULE)}
												</label>
											</div>
											<div class="box-body {if !$HAS_CONFIG}hide{/if}">
												<table class="table mapping-table fieldBlockContainer">
													<thead>
														<tr>
															<th>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CRM_FIELD', $MODULE_NAME)}</th>
															<th>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAUTIC_FIELD', $MODULE_NAME)}</th>
															<th></th>
														</tr>
													</thead>
													<tbody>
														{foreach from=$MAPPING_REQUIRED_FIELDS[$MAPPING_MODULE] key=MAUTIC_FIELD item=CRM_FIELD}
															<tr class="required">
																<td style="width: 50%">
																	<span>{$MODULE_FIELDS[$CRM_FIELD]}</span>
																	<select class="inputElement hide" style="width: 200px">
																		{html_options options=$MODULE_FIELDS selected=$CRM_FIELD value=$CRM_FIELD}
																	</select>
																</td>
																<td class="no-wrap" style="width: 50%">
																	<span>{$ALL_MAUTIC_FIELDS[$MAUTIC_FIELD]}</span>
																	<select class="inputElement hide" style="width: 200px">
																		{html_options options=$ALL_MAUTIC_FIELDS selected=$MAUTIC_FIELD value=$MAUTIC_FIELD}
																	</select>
																	<button type="button" class="btn btn-link remove-mapping hide"><i class="far fa-xmark redColor"></i></button>
																</td>
															</tr>        
														{/foreach}

														{foreach from=$CONFIG.mapping_fields[{$MAPPING_MODULE|lower}] key=key item=FIELD}
															{if $FIELD.required eq 0}
																<tr>
																	<td style="width: 50%">
																		<select class="inputElement" style="width: 200px">
																			{html_options options=$MODULE_FIELDS selected=$FIELD.crm value=$CRM_FIELD}
																		</select>
																	</td>
																	<td class="no-wrap" style="width: 50%">
																		<select class="inputElement" style="width: 200px">
																			{html_options options=$ALL_MAUTIC_FIELDS selected=$FIELD.mautic value=$MAUTIC_FIELD}
																		</select>
																		<button type="button" class="btn btn-link remove-mapping"><i class="far fa-xmark redColor"></i></button>
																	</td>
																</tr>
															{/if}
														{/foreach}
													</tbody>
													<tfoot>
														<tr>
															<td colspan="2" class="text-right">
																<button type="button" class="btn btn-link add-mapping"><i class="far fa-plus"></i> {vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_ADD_CONFIG', $MODULE_NAME)}</button>
															</td>
														</tr>
													</tfoot>
												</table>
											</div>
										</div>
									{/foreach}
								</div>
								
								{* Mapping Stages *}
								<div id="mapping-stages" class="tabcontent tab-pane">                        
									{foreach from=$MODULE_STAGE_FIELDS key=MODULE item=FIELD}
										{if $MODULE == 'Leads'}
											{assign var=HAS_CONFIG value=$CONFIG.mapping_fields['leads']|count}
										{/if}

										{if $MODULE != 'Leads'}
											{assign var=HAS_CONFIG value=$CONFIG.mapping_fields['contacts']|count}
										{/if}

										<div id="block-mapping-stage-{$MODULE|lower}" class="headerblock {if !$HAS_CONFIG}hide{/if}" data-toggle="collapse" data-target="#mapping-stage-{$MODULE|lower}"><i class="indicator far fa-angle-down"></i>&nbsp;{vtranslate($MODULE, $MODULE)}</div>
										<div id="mapping-stage-{$MODULE|lower}" data-module-lower="{$MODULE|lower}" class="collapse in mapping-header mapping-stage {if !$HAS_CONFIG}hide{/if}">
											<div class="form-group-header">
												<div class="control-label fieldLabel col-sm-4" style="font-weight: bold;">
													<span>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CRM_STAGE', $MODULE_NAME)}</span>
												</div>
												
												<div class="control-label fieldLabel col-sm-4" style="font-weight: bold;">
													<span>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAUTIC_STAGE', $MODULE_NAME)}</span>
												</div>
											</div>
											

											{foreach from=$FIELD['options'] key=stage item=label}
												<div class="form-group">
													<div class="control-label fieldLabel col-sm-4">
														<span>{$label}</span>
														<select style="display:none" class="inputElement">
															{html_options options=$FIELD['options'] selected=$stage value=$stage}
														</select>
													</div>
													
													<div class="control-label fieldLabel col-sm-4">
														<select class="inputElement">
															<option value="">{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_NO_CONFIG', $MODULE_NAME)}</option>
															{html_options options=$MAUTIC_STAGES selected=$CONFIG.mapping_stages[$MODULE|lower][$stage]}
														</select>
													</div>
												</div>                                
											{/foreach}
										</div>
									{/foreach}
								</div>

								{* Mapping Stages - Segments *}
								<div id="mapping-stage-segments" class="tabcontent tab-pane">  
									<div id="mapping-stage-segment-contents" class="in mapping-header mapping-stage-segment">   
										<div class="form-group-header">
											<div class="control-label fieldLabel col-sm-4" style="font-weight: bold;">
												<span>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAUTIC_STAGE', $MODULE_NAME)}</span>
											</div>
											
											<div class="control-label fieldLabel col-sm-4" style="font-weight: bold;">
												<span>{vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_MAUTIC_SEGMENT', $MODULE_NAME)}</span>
											</div>
										</div>

										<div class="form-group hide">
											<div class="control-label fieldLabel col-sm-4">
												<select class="inputElement">
													{html_options options=$MAUTIC_STAGES}
												</select>
											</div>
											
											<div class="control-label fieldLabel col-sm-4">
												<select class="inputElement">
													{html_options options=$MAUTIC_SEGMENTS}
												</select>
												<a href="javacript:void();" class="close-group">✕</a>
											</div>
										</div>      

										{foreach from=$CONFIG.mapping_stages_segments key=key item=item}
											<div class="form-group">
												<div class="control-label fieldLabel col-sm-4">
													<select class="inputElement">
														{html_options options=$MAUTIC_STAGES selected=$item.stage}
													</select>
												</div>
												
												<div class="control-label fieldLabel col-sm-4">
													<select class="inputElement">
														{html_options options=$MAUTIC_SEGMENTS selected=$item.segment}
													</select>
													<a href="javacript:void();" class="close-group">✕</a>
												</div>
											</div>
										{/foreach}

										<div class="form-group-footer">
											<div class="control-label fieldLabel no-border col-sm-4">
											</div>
											
											<div class="control-label fieldLabel no-border col-sm-4" style="text-align:right">
												<a href="javacript:void();" class="add-mapping">+ {vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_ADD_CONFIG', $MODULE_NAME)}</a>
											</div>
										</div> 
									</div>
								</div>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="modal-overlay-footer clearfix">
							<div class="row clear-fix">
								<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
									<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE')}</button>
									<a class="cancelLink" href="javascript:history.back()">{vtranslate('LBL_CANCEL')}</a>
								</div>
							</div> 
						</div>
					</form>
				{/if}
			</div>
		</div>
	</div>

	<link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}"/>
	<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
	<script src="{vresource_url('resources/UIUtils.js')}"></script>
{strip}