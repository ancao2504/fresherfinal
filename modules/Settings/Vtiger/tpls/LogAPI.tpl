{* Added by Tung Nguyen on 2022-06-23 *}

{strip}
	<div style="width: 100%;" class="contents-bottomscroll">
		<div id="filter-container">
			<form id=form-log-api action="{$ENTRY_URL}" id="search-form" method="POST" novalidate="novalidate">
				<div class="form-actions text-center">
					<div class="row">
						<div class="form-group col-md-4">
							<label class="col-md-4 control-label">Logger:</label>
							<div class="col-md-8 input-group">
								<select class="select2 form-control" name="logger">
									{html_options options=$SQLITE_LOGGER selected=$LOGGER}
								</select>
							</div>
						</div>
						<div class="form-group col-md-4">
							<label class="col-md-4 control-label">{vtranslate('LBL_API', 'CPLogAPI')}:</label>
							<div class="col-md-8 input-group">
								<input class="form-control" id="api" name="api" type="text" value="{$API}">
							</div>
						</div>
						<div class="form-group col-md-4">
							<label class="col-md-4 control-label">{vtranslate('LBL_KEYWORD', 'CPLogAPI')}:</label>
							<div class="col-md-8 input-group">
								<div class="input-group inputElement" style="margin-bottom: 3px">
									<input class="form-control" id="keyword" name="keyword" type="text" value="{$KEYWORD}">

									<button id="searchable-field-help-text" type="button" style="background-color: #fff" class="input-group-addon btn btn-default custom-popover-wrapper custom-popover">
										<i class="far fa-question-circle">
											<div class="custom-popover-content" style="display: none">
												{foreach from=$SEARCHABLE_FIELDS key=SEARCHABLE_FIELD_LOGGER item=SEARCHABLE_FIELD}
													<div class="searchable-field-logger" id="{$SEARCHABLE_FIELD_LOGGER}">
														{foreach from=$SEARCHABLE_FIELD key=DIRECTION item=KEYWORDS}
															<div class="searchable-field-direction {$DIRECTION}">
																<label style="font-weight: bold">{$DIRECTION}</label>

																<ul>
																	{foreach from=$KEYWORDS.key_search key=API item=KEYWORD}
																		<li id="{$API}"><b style="font-weight: bold">{$API}</b>: {implode(", ", $KEYWORD)}</li>
																	{/foreach}
																</ul>
															</div>
														{/foreach}
													</div>
												{/foreach}
											</div>
										</i>
									</button>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-4">
							<label class="col-md-4 control-label" for="FromDate">{vtranslate('LBL_FROMDATE', 'CPLogAPI')}:</label>
							<div class="col-md-8 input-group">
								<div class="input-group inputElement" style="margin-bottom: 3px">
									<input type="text" class="datePicker form-control" data-fieldname="from_date" data-fieldtype="date" name="from_date" data-date-format="dd-mm-yyyy" value="{$FROM_DATE}">
									<span class="input-group-addon"><i class="far fa-calendar "></i></span>
								</div>
							</div>
						</div>
						<div class="form-group col-md-4">
							<label class="col-md-4 control-label" for="ToDate">{vtranslate('LBL_TODATE', 'CPLogAPI')}:</label>
							<div class="col-md-8 input-group">
								<div class="input-group inputElement" style="margin-bottom: 3px">
									<input type="text" class="datePicker form-control" data-fieldname="to_date" data-fieldtype="date" name="to_date" data-date-format="dd-mm-yyyy" value="{$TO_DATE}">
									<span class="input-group-addon"><i class="far fa-calendar "></i></span>
								</div>
							</div>
						</div>
						<div class="form-group col-md-4">
							<label class="col-md-4 control-label">{vtranslate('LBL_ACTOR', 'CPLogAPI')}:</label>
							<div class="col-md-8 input-group">
								<select class="select2 form-control" name="actor">
									<option value="">All</option>
									{html_options options=$USER_LIST selected=$ACTOR}
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<button id="search" class="btn btn-primary" type="submit">{vtranslate('LBL_SEARCH', 'CPLogAPI')}</button>
						</div>
					</div>
				</div>
			</form>
		</div>

		<div class="bottomscroll-div">
			<table class="table table-bordered log-api">
				<thead>
					<tr class="blockHeader">
						<th width="3%">No.</th>
						<th width="10%">{vtranslate('LBL_DIRECTION', 'CPLogAPI')}</th>
						<th width="10%">{vtranslate('LBL_OBJECT_CLASS', 'CPLogAPI')}</th>
						<th width="10%">{vtranslate('LBL_ACTION', 'CPLogAPI')}</th>
						<th width="10%">{vtranslate('LBL_CPCALLAPILOG_STATUS', 'CPLogAPI')}</th>
						<th width="13%">{vtranslate('LBL_CREATED_TIME', 'CPLogAPI')}</th>
						<th width="13%">{vtranslate('LBL_END_TIME', 'CPLogAPI')}</th>
						<th width="10%">{vtranslate('IP', 'CPLogAPI')}</th>
						<th width="10%">{vtranslate('LBL_HTTP_CODE', 'CPLogAPI')}</th>
						<th width="10%">{vtranslate('LBL_ACTOR', 'CPLogAPI')}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$DATA key=KEY item=ITEM}
						<tr class="main-row cursorPointer" data-id="{$ITEM.cpcallapilogid}">
							<td align="center">{$ITEM.num}</td>
							<td>{$ITEM.direction}</td>
							<td>{$ITEM.object_class}</td>
							<td>{$ITEM.api}</td>
							<td align="center">
								<span class="value" data-field-type="picklist">
									<span class="picklist-color log-status {$ITEM.cpcallapilog_status}">
										{vtranslate($ITEM.cpcallapilog_status, 'CPLogAPI')}
									</span>
								</span>
							</td>
							<td align="center">{Vtiger_Datetime_UIType::getDisplayDateTimeValue($ITEM.created_time)}</td>
							<td align="center">{if $ITEM.end_time} {Vtiger_Datetime_UIType::getDisplayDateTimeValue($ITEM.end_time)}{/if}</td>
							<td align="center">{$ITEM.ip}</td>
							<td align="center">{$ITEM.response_code}</td>
							<td>{$ITEM.actor_name}</td>
						</tr>
						<tr class="collapse" id="{$ITEM.cpcallapilogid}">
							<td colspan="10">
								<div style="overflow: hidden">
									<table style="width: 100%;" class="table">
										<thead>
											<tr class="blockHeader">
												<th width="50%">{vtranslate('LBL_REQUEST', 'CPLogAPI')}</th>
												<th width="50%">{vtranslate('LBL_RESPONSE', 'CPLogAPI')}</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td colspan="2" style="padding: 0">
													<div style="display: flex;">
														<div style="width: 50%;">
															<pre class="json-display" id="content{$ITEM.cpcallapilogid}" aria-readonly="true">{$ITEM.content}</pre>
														</div>
														<div style="width: 50%;">
															<pre class="json-display" id="response{$ITEM.cpcallapilogid}" aria-readonly="true">{$ITEM.response}</pre>
														</div>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>

		<div class="logapi-footer">
			<div>Hiển thị {$MIN} đến {$MAX} của {$TOTAL} kết quả</div>

			<div>
				<ul class="pagination">
					{if $CURRENT_PAGE > 1 && $TOTAL_PAGE > 1}                    
						<li class="prev"><a href="{$ENTRY_URL}&page={$CURRENT_PAGE-1}&{$SEARCH_PARAMS}"><i class="fa fa-angle-left"></i></a></li>
					{/if}
		
					{for $foo=$START_PAGE to $END_PAGE}
						{if $foo eq $CURRENT_PAGE}
							<li class="active"><a>{$foo}</a></li>
						{else}
							<li><a href="{$ENTRY_URL}&page={$foo}&{$SEARCH_PARAMS}">{$foo}</a></li>
						{/if}
					{/for}
		
					{if $CURRENT_PAGE < $TOTAL_PAGE && $TOTAL_PAGE > 1}                    
						<li class="next "><a href="{$ENTRY_URL}&page={$CURRENT_PAGE+1}&{$SEARCH_PARAMS}"><i class="fa fa-angle-right"></i></a></li>
					{/if}
				</ul>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="{vresource_url('resources/libraries/JSONViewer/jquery.json-editor.min.js')}"></script>
{/strip}