{* Init Data *}
<script>
	var _KANBAN_META = {$KANBAN_META};
	var _MODULES_FIELDS_INFO = {$MODULES_FIELDS_INFO};
    var _USER_INFO = {$_USER_INFO};

    // Added by Hieu Nguyen on 2020-03-10 to show default kanban view
    var _SELECTED_MODULE = '{$SELECTED_MODULE}';
    var _SELECTED_PICKLIST = '{$SELECTED_PICKLIST}';
    // End Hieu Nguyen
</script>

<div id="kanbanApp" class="kanbanApp container" style="display: none">
	<div class="kanban-config">
		<div class="kan-summary active"> <!-- May make a summary panel here later -->
			<div class="head">{vtranslate('CPKanban', $MODULE)}</div>
			<div class="content form-row">
				<div class="form-col-6">
					<div class="opt-title clickable" onclick="$(this).closest('.kan-summary').toggleClass('active').closest('.kanban-config').trigger('resize')">
                        <i class="far fa-chevron-down chevron" aria-hidden="true"></i>
                        <span>{vtranslate('LBL_CONFIG', $MODULE)}</span>
                    </div>
                </div>
                <div class="form-col-6 col-right">
                    <div class="opt-title">{vtranslate('LBL_SORT_AND_FILTER', $MODULE)}</div>
                </div>
            </div>  
            <div id="FilterContent" class="form-row">  
                <div class="form-col-6 left">  
					<form name="configs">
						<div class="opts">
							<div class="inline select-wraper select-icon-filter" title="{vtranslate('LBL_CHOOSE_MODULE', $MODULE)}" >
								<chosen-select 
									name="module" class="chosen-select"
									v-model="options.module_name" onchange="kanban.optionHandler('module_name')"
								>
									<option value label="{vtranslate('LBL_CHOOSE_MODULE', $MODULE)}">{vtranslate('LBL_CHOOSE_MODULE', $MODULE)}</option>
									<option 
										v-for="moduleName in ui.modules" 
										:value="moduleName" 
										:label="meta[moduleName].label"
										v-html="meta[moduleName].label"
									></option>
								</chosen-select>
							</div>
							
							<div class="inline select-wraper select-icon-filter" title="{vtranslate('LBL_APPLY_FIELDS', $MODULE)}">
								<chosen-select 
									name="picklist_field" class="chosen-select" 
									v-model="options.picklist_name"
									onchange="kanban.optionHandler('picklist_name')"
								>
									<option value label="{vtranslate('LBL_APPLY_FIELDS', $MODULE)}">{vtranslate('LBL_APPLY_FIELDS', $MODULE)}</option>
									<option 
										v-for="(field, name) in ui.picklist_fields"
										:value="name"
										:label="field && field.label"
										v-html="field && field.label"
									></option>
								</chosen-select>
							</div>

							<div class="inline select-wraper select-icon-filter kb-dropdown" title="{vtranslate('LBL_KANBAN_COLUMN', $MODULE)}">
								<multiselect
									name="kanban_columns"
									v-model="options.kanban_columns"
									multiple
									filterable="true"
									onchange="kanban.optionHandler('kanban_columns')"
									:disabled="cache.saveSettingDefault"
									:class="{literal}{disabled: cache.saveSettingDefault}{/literal}"
								>
									<option 
										v-for="(data, name) in ui.picklist_values"
										:value="name"
										:label="data && data.label"
										v-html="data && data.label"
									></option>
								</multiselect>
							</div>

							<div class="inline select-wraper">
								<i 
									:class="{literal}{far: true, 'fa-th-large': true, 'kb-btn': true, 'btn-display-field': true, 'disabled': !isEnabled('openItemFieldsModal') || cache.saveSettingDefault}{/literal}" 
									aria-hidden="true" title="{vtranslate('LBL_CHOOSE_DISPLAY_FIELDS', $MODULE)}"
									@click="isEnabled('openItemFieldsModal') && !cache.saveSettingDefault && openItemFieldsModal()"
								></i>
							</div>
						</div>
					</form>
				</div>
				<div class="form-col-6 col-right">
					<form name="filters">
						<div class="basic">
							<div class="inline select-wraper">
								<i 
									:class="{literal}{far: true, 'fa-refresh': true, 'kb-btn': true, disabled: !isEnabled('saveSetting') }{/literal}" aria-hidden="true" 
									title="{vtranslate('LBL_REFRESH_DATA', $MODULE)}"
									@click="isEnabled('saveSetting') && reloadData()"
								></i>
							</div>

							<div class="inline select-wraper select-icon-filter sorting_field" title="{vtranslate('LBL_SORT_OPTIONS', $MODULE)}">
								<chosen-select 
									name="sorting_field" 
									placeholder="{vtranslate('LBL_SORT_OPTIONS', $MODULE)}"
									:class="{literal}{'chosen-select': true, 'sorting_field': true, 'disabled': cache.saveSettingDefault}{/literal}" 
									v-model="options.sorting_field" onchange="kanban.optionHandler('sorting_field')"
									:disabled="cache.saveSettingDefault"
									ref="sorting_field"
								>
									{* <option label="{vtranslate('LBL_SORT_OPTIONS', $MODULE)}" value="">{vtranslate('LBL_SORT_OPTIONS', $MODULE)}</option> *}
									<option label="{vtranslate('LBL_POTENTIAL_AMOUNT_DECREASE', $MODULE)}" value="opportunity_amount_desc" v-if="options.module_name === 'Potentials'">{vtranslate('LBL_POTENTIAL_AMOUNT_DECREASE', $MODULE)}</option>
									<option label="{vtranslate('LBL_CREATED_DATE', $MODULE)}" value="createdtime">{vtranslate('LBL_CREATED_DATE', $MODULE)}</option>
									<option label="{vtranslate('LBL_MODIFIED_DATE', $MODULE)}" value="modifiedtime">{vtranslate('LBL_MODIFIED_DATE', $MODULE)}</option>
								</chosen-select>
							</div>

							<div class="inline select-wraper select-icon-filter" title="{vtranslate('LBL_FILTER_OPTIONS', $MODULE)}">
								<chosen-select 
									name="filter" 
									placeholder="{vtranslate('LBL_FILTER_OPTIONS', $MODULE)}"
									:class="{literal}{'chosen-select': true, 'disabled': cache.saveSettingDefault}{/literal}" 
									v-model="options.filter" onchange="kanban.optionHandler('filter')"
									:disabled="cache.saveSettingDefault"
									ref="filter"
								>
									{* <option label="{vtranslate('LBL_FILTER_OPTIONS', $MODULE)}" value="">{vtranslate('LBL_FILTER_OPTIONS', $MODULE)}</option> *}
									<option label="{vtranslate('LBL_THIS_WEEK', $MODULE)}" value="this_week">{vtranslate('LBL_THIS_WEEK', $MODULE)}</option>
									<option label="{vtranslate('LBL_LAST_WEEK', $MODULE)}" value="last_week">{vtranslate('LBL_LAST_WEEK', $MODULE)}</option>
									<option label="{vtranslate('LBL_THIS_MONTH', $MODULE)}" value="this_month">{vtranslate('LBL_THIS_MONTH', $MODULE)}</option>
									<option label="{vtranslate('LBL_LAST_MONTH', $MODULE)}" value="last_month">{vtranslate('LBL_LAST_MONTH', $MODULE)}</option>
									<option label="{vtranslate('LBL_THIS_QUARTER', $MODULE)}" value="this_quarter">{vtranslate('LBL_THIS_QUARTER', $MODULE)}</option>
									<option label="{vtranslate('LBL_LAST_QUARTER', $MODULE)}" value="last_quarter">{vtranslate('LBL_LAST_QUARTER', $MODULE)}</option>
									<option label="{vtranslate('LBL_THIS_YEAR', $MODULE)}" value="this_year">{vtranslate('LBL_THIS_YEAR', $MODULE)}</option>
									<option label="{vtranslate('LBL_LAST_YEAR', $MODULE)}" value="last_year">{vtranslate('LBL_LAST_YEAR', $MODULE)}</option>
								</chosen-select>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

    <div class="kanban-wraper rel">
        <div class="abs empty-data" v-if="cache.emptyData">{vtranslate('LBL_NOT_FOUND_APPROPRIATE_DATA', $MODULE)}</div>
    	<div id="KanbanMain" class="kanban-main fancyScrollbar kb-popover-container">
    		<div class="column" v-for="(col, index) in options.kanban_columns" :style="{literal}{zIndex: options.kanban_columns.length - index}{/literal}">
    			 <div class="col-header">
    				<div class="col-title" :style="{literal}{backgroundColor: getColColor(col), color: global.KanbanHelper.getTextColor(getColColor(col))}{/literal}">
    					<div class="col-before"></div>
    					<div class="col-after" :style="{literal}{borderLeftColor: getColColor(col), color: global.KanbanHelper.getTextColor(getColColor(col))}{/literal}"></div>
    					<span class="col-title-value" :title="getPicklistItemLabel(col)" v-html="getPicklistItemLabel(col)"></span>
    					<sup class="col-counter">({literal}{{getColumnItemsCount(col)}}{/literal})</sup>
    				</div>

    				<div v-if="data[col] && data[col].meta.total" class="col-summary" :title="{literal}`${data[col].meta.total.unit} ${data[col].meta.total.value}`{/literal}">
    					<sup class="col-summary-unit" v-html="data[col].meta.total.unit"></sup>
    					<span class="col-summary-value" v-html="data[col].meta.total.value"></span>
    				</div>
    			</div>

    			<div class="col-search">
    				<input @input="searchHandler" :data-for="col" placeholder="{vtranslate('LBL_INPUT_SEARCH_KEYWORD', $MODULE)}"/>
    				<i class="far fa-search" aria-hidden="true"></i>
    			</div>
                
                <draggable class="items apps-container fancyScrollbar" v-model="lists[col]" :data-col="col" :options="global.KanbanVars.draggableOptions" @end="dragOnEnd">
                    <div class="item noselect" v-for="item in lists[col]" :key="item.id" :data-key="item.id">
                        <div class="title" :style="{literal}{backgroundColor: getColColor(col), color: global.KanbanHelper.getTextColor(getColColor(col))}{/literal}">
                            <div class="left" :title="item.meta.name">
                                <a class="item-link" target="_blank" v-html="item.meta.name" @click="global.KanbanHelper.openQuickPreview(options.module_name, item.id)"></a>
                            </div>
                            <div class="right">
                                <span class="item-created-time" :title="item.meta.created_time" v-html="global.KanbanHelper.formatDate(item.meta.created_time)"></span>
                                <span class="item-edit-btn" title="{vtranslate('LBL_QUICK_EDIT', $MODULE)}" @click="quickEdit(item.id, col)"><i class="far fa-pen" aria-hidden="true"></i></span>
                            </div>
                        </div>

                        <div class="content">
                            <div class="field form-row item-row" v-for="(value, field) in item.data">
                                <div class="field-label form-col-4">{literal}{{getFieldLabel(field)}}{/literal}</div>
                                <div class="field-value form-col-8">
                                    {literal}{{value}}{/literal}
                                    {if PBXManager_Logic_Helper::canMakeCall()} {* Modified condition by Hieu Nguyen on 2021-08-31 *}
                                        <a v-if="getUitype(field) === 'phone' && value" class="btn-phone">
                                            <i 
                                                class="far fa-phone-square" aria-hidden="true" 
                                                @click="global.Vtiger_PBXManager_Js.registerPBXOutboundCall($event.target, value, item.id)"
                                            ></i>
                                        </a>
                                    {/if}
                                </div>
                            </div>
                        </div>

                        <div class="meta">
                            <div class="left">
                                <span class="assigned-user-avatar">
                                    <img class="avatar-image" :src="item.meta.assinged_user_avatar || global.KanbanVars.defaultAvatar" onerror="KanbanHelper.setDefaultAvatar(this)" />
                                </span>
                                <span class="assigned-user-name">
                                    <a class="assigned-user-link" target="_blank" @click="global.KanbanHelper.goToAssignUserProfile(item.meta.assigned_user_id)" v-html="item.meta.assinged_user_name"></a>
                                </span>
                            </div>

                            <div class="right">
                                <span class="quick-create-tooltip-wraper">
                                    <span class="quick-create-tooltip" data-prevent="hide_popover" title="{vtranslate('LBL_QUICK_CREATE', $MODULE)}" @click="openQuickCreateActionSheet(item)">
                                        <i class="far fa-plus-circle" data-prevent="hide_popover" aria-hidden="true"></i>
                                    </span>
                                </span>

                                <span class="activity-counters">
                                    <span class="calls-count clickable" @click="global.KanbanHelper.openRelatedPopup('Calendar', options.module_name, item.id, item.meta.name, 'Call')">
                                        <i class="far fa-phone" aria-hidden="true" title="Calls"></i>
                                        <span class="bg-danger badge count">{literal}{{item.counters.calls_count}}{/literal}</span>
                                    </span>
                                    <span class="tasks-count clickable" @click="global.KanbanHelper.openRelatedPopup('Calendar', options.module_name, item.id, item.meta.name, 'Task')">
                                        <i class="far fa-tasks" aria-hidden="true" title="Tasks"></i>
                                        <span class="bg-danger badge count">{literal}{{item.counters.tasks_count}}{/literal}</span>
                                    </span>
                                    <span class="meetings-count clickable" @click="global.KanbanHelper.openRelatedPopup('Calendar', options.module_name, item.id, item.meta.name, 'Meeting')">
                                        <i class="far fa-users" aria-hidden="true" title="Meetings"></i>
                                        <span class="bg-danger badge count">{literal}{{item.counters.meetings_count}}{/literal}</span>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </draggable>
    		</div>
    	</div>
    </div>

    <div id="KanbanDisplayFieldSelector" class="modal kb-modal">
        <div class="modal-backdrop in"></div>
        <div class="modal-dialog modal-lg modal-content">
            {assign var=HEADER_TITLE value=vtranslate('LBL_DISPLAY_FIELD_CONFIG', $MODULE)}
            {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            
            <form name="kanban_display_form" class="kanbanApp form-horizontal modal-form" style="display: block">
                <div class="select-wraper sortable-select">
                    <div class="form-label" style="font-size: 13px">{vtranslate('LBL_CHOOSE_UP_TO_MAX_DISPLAY_FIELD', $MODULE)}:</div>
                    <br>
                    <chosen-select 
                        name="item_fields" class="chosen-select item_fields"
                        {* v-model="options.item_fields" *}
                        placeholder="{vtranslate('LBL_CHOOSE_DISPLAY_FIELDS', $MODULE)}"
                        multiple
                        ref="item_fields"
                    >
                        <option v-for="(field, name) in ui.module_fields"
                            :label="field.label" 
                            :value="name"
                            v-html="field.label"
                        ></option>
                    </chosen-select>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>

    <div id="CalendarQuickCreate" class="modal kb-modal">
        <div class="modal-backdrop in"></div>
        <div class="quick-create-panel modal-dialog modal-content tele-modal">
            <div class="modal-header">
                <div class="clearfix">
                    <div class="pull-right">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true" class='far fa-close'></span>
                        </button>
                    </div>
                    <h4 class="pull-left">
                        <div class="inline">
                            <i class="far fa-plus"></i>
                        </div>
                        <div class="inline">
                            <div class="title">{vtranslate('LBL_QUICK_CREATE', $MODULE)}</div>
                            <div class="parent-name"></div>
                        </div>
                    </h4>
                </div>
            </div>
            <div class="content-wraper">
                {* Modified by Hieu Nguyen on 2022-09-06 to display create buttons based on user permission *}
                <div class="actions">
                    {if Calendar_Module_Model::canCreateActivity('Task')}
                        <div class="create-btn task inline clickable" title="{vtranslate('LBL_CREATE_TASK', $MODULE)}" onclick="kanban.quickCreate('Task', $(this))">
                            <i class="far fa-tasks" aria-hidden="true"></i>
                            <div class="inline">{vtranslate('LBL_CREATE_TASK', $MODULE)}</div>
                        </div>
                    {/if}
                    {if Calendar_Module_Model::canCreateActivity('Call')}
                        <div class="create-btn call inline clickable" title="{vtranslate('LBL_CREATE_CALL', $MODULE)}" onclick="kanban.quickCreate('Call', $(this))">
                            <i class="far fa-phone" aria-hidden="true"></i>
                            <div class="inline">{vtranslate('LBL_CREATE_CALL', $MODULE)}</div>
                        </div>
                    {/if}
                    {if Calendar_Module_Model::canCreateActivity('Meeting')}
                        <div class="create-btn meeting inline clickable" title="{vtranslate('LBL_CREATE_MEETING', $MODULE)}" onclick="kanban.quickCreate('Meeting', $(this))">
                            <i class="far fa-users" aria-hidden="true"></i>
                            <div class="inline">{vtranslate('LBL_CREATE_MEETING', $MODULE)}</div>
                        </div>
                    {/if}
                </div>
                {* End Hieu Nguyen *}
            </div>
        </div>
    </div>
</div>