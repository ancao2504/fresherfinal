{*
    Name: AddWidgetModal.tpl
    Author: Phu Vo
    Date: 2020.10.23
*}
	<div class="add-widget-modal add-widget-modal-template modal-dialog modal-lg modal-content">
		{include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=vtranslate('LBL_ADD_WIDGET', $MODULE)}
		<div class="editViewBody">
			<div class="container-fluid modal-body">
				<div class="row">
					<div class="col-lg-12">
						<div class="input-wraper" style="position: relative">
							<i class="far fa-search" aria-hidden="true" style="position: absolute; top: 9px; left: 9px"></i>
							<input class="inputElement" name="keyword" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" style="width: 300px; padding-left: 30px"/>
						</div>
					</div>
				</div>
				<hr class="divider"/>
				<div class="widget-list-wrapper">
					<div class="row">
						<div class="col-lg-4">
							<div class="widget-category-wrapper">
								<h5 class="category-header"><strong>{$category.name}</strong></h5>
								<div class="widgetsList">
									{for $column=0 to 1}
										{assign var=category value=$GROUPED_SELECTABLE_WIDGETS[$column]}
										{if $category['id'] == 'utilities' || $category['id'] == 'uncategories'}
											<div class='widget-category-wrapper'>
												<h5 class="category-header"><strong>{$category.name}</strong></h5>
												<ul class="widgetsList">
													{foreach from=$category.widgets item=widget}
														<li data-id="{$widget->get('linkid')}" data-keyword="{if $widget->getName() == 'ChartReportWidget'}{vtranslate($widget->get('reportname'), 'Report')}{else}{vtranslate($widget->getTitle(), $MODULE_NAME)}{/if}">
															{if $widget->getName() == 'MiniList'}
																<a onclick="Vtiger_DashBoard_Js.addMiniListWidget(this, '{$widget->getUrl()}')" href="javascript:void(0);"
																	data-linkid="{$widget->get('linkid')}" data-name="{$widget->getName()}" data-width="{$widget->getWidth()}" data-height="{$widget->getHeight()}"
																	title="{vtranslate($widget->getTitle(), $widget->get('module'))}"
																>
																	{vtranslate($widget->getTitle(), $widget->get('module'))}
																</a>
															{else if $widget->getName() == 'Notebook'}
																<a onclick="Vtiger_DashBoard_Js.addNoteBookWidget(this, '{$widget->getUrl()}')" href="javascript:void(0);"
																	data-linkid="{$widget->get('linkid')}" data-name="{$widget->getName()}" data-width="{$widget->getWidth()}" data-height="{$widget->getHeight()}"
																	title="{vtranslate($widget->getTitle(), $widget->get('module'))}"
																>
																	{vtranslate($widget->getTitle(), $widget->get('module'))}
																</a>
															{else if $widget->getName() == 'ChartReportWidget'}
																<a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$widget->getUrl()}')" href="javascript:void(0);"
																	data-linkid="{$widget->get('reportid')}" data-name="{$widget->getName()}" data-width="{$widget->getWidth()}" data-height="{$widget->getHeight()}"
																	title="{vtranslate($widget->get('reportname'), 'Report')}"
																>
																	{vtranslate($widget->get('reportname'), 'Report')}
																</a>
															{else}
																<a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$widget->getUrl()}')" href="javascript:void(0);"
																	data-linkid="{$widget->get('linkid')}" data-name="{$widget->getName()}" data-width="{$widget->getWidth()}" data-height="{$widget->getHeight()}"
																	title="{vtranslate($widget->getTitle(), $widget->get('module'))}"
																>
																	{vtranslate($widget->getTitle(), $widget->get('module'))}
																</a>
															{/if}
														</li>
													{/foreach}
												</ul>
											</div>
										{/if}
									{/for}
								</div>
							</div>
						</div>
						{for $column=1 to 2}
							<div class="col-lg-4">
								{foreach from=$GROUPED_SELECTABLE_WIDGETS key=index item=category}
									{if
										$category['id'] != 'utilities'
										&& $category['id'] != 'uncategories'
										&& (($column == 1 && ($index) % 2 == 0) || ($column == 2 && ($index + 1) % 2 == 0))
									}
										<div class='widget-category-wrapper'>
											<h5 class="category-header"><strong>{$category.name}</strong></h5>
											<ul class="widgetsList">
												{foreach from=$category.widgets item=widget}
													<li data-id="{$widget->get('linkid')}" data-keyword="{if $widget->getName() == 'ChartReportWidget'}{vtranslate($widget->get('reportname'), 'Report')}{else}{vtranslate($widget->getTitle(), $MODULE_NAME)}{/if}">
														{if $widget->getName() == 'ChartReportWidget'}
															<a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$widget->getUrl()}')" href="javascript:void(0);"
																data-linkid="{$widget->get('reportid')}" data-name="{$widget->getName()}" data-width="{$widget->getWidth()}" data-height="{$widget->getHeight()}"
																title="{vtranslate($widget->get('reportname'), 'Report')}"
															>
																{vtranslate($widget->get('reportname'), 'Report')}
															</a>
														{else}
															<a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$widget->getUrl()}')" href="javascript:void(0);"
																data-linkid="{$widget->get('linkid')}" data-name="{$widget->getName()}" data-width="{$widget->getWidth()}" data-height="{$widget->getHeight()}"
																title="{vtranslate($widget->getTitle(), $widget->get('module'))}"
															>
																{vtranslate($widget->getTitle(), $widget->get('module'))}
															</a>
														{/if}
													</li>
												{/foreach}
											</ul>
										</div>
									{/if}
								{/foreach}
							</div>
						{/for}
					</div>
				</div>
			</div>
		</div>
	</div>