{* 
	Author: Phu Vo
	Refactor: Hieu Nguyen
*}

{strip}
	{assign var=USER_NOTIFICATIONS_PREFERENCE value=CPNotifications_Data_Model::loadUserConfig('notification_config')}
	{assign var=MODULES_TRANSLATED_SINGLE_LABEL value=getModulesTranslatedSingleLabel()}

	<script>
		window._RELATED_TABS_INFO = {
			modules: JSON.parse('{$MODULES_TRANSLATED_SINGLE_LABEL|@json_encode nofilter}'),
			tabs: {
				detail: '{vtranslate("LBL_DETAILS", "Vtiger")}',
				update: '{vtranslate("LBL_UPDATES", "Vtiger")}'
			}
		}
	</script>

	{if $USER_NOTIFICATIONS_PREFERENCE && $USER_NOTIFICATIONS_PREFERENCE->receive_notifications == 1}
		<div id="notification" class="relative" style="display: inline-block; padding: 5px">
			<!-- Notification icon -->
			<a href="javascript: void(0)" id="notification-popover-trigger" class="far fa-bell relative topbar-icon" data-toggle="tooltip" data-for="#notification-popover" aria-hidden="true" title="Notifications" style="padding: 10px">
				<span id="notification-counter" class="inline counter notification-counter bg-danger badge hide"></span>
			</a>

			<!-- Notification popover -->
			<div id="notification-popover" class="popover fade bottom in" role="tooltip" style="display: none">
				<div class="arrow" style="left: auto;"></div>
				<div class="popover-content">
					<!-- Main tab navs -->
					<ul id="notification-tabs" class="nav nav-tabs" role="tablist">
						<li class="nav-item relative topbar-icon active" title="{vtranslate('LBL_TITLE_NOTIFICATION', 'CPNotifications')}" data-for="div#notification-tab-notify-pane">
							<a class="nav-link"><i class="far fa-clock" aria-hidden="true"></i></a>
							<span id="notification-counter-notify" class="inline counter notification-subtabs-counter bg-danger badge"></span>
						</li>
						{if $USER_NOTIFICATIONS_PREFERENCE->show_activity_reminders == 1}
							<li class="nav-item relative topbar-icon" title="{vtranslate('LBL_TITLE_ACTIVITY', 'CPNotifications')}" data-for="div#notification-tab-activity-pane">
								<a class="nav-link"><i class="far fa-tasks" aria-hidden="true"></i></a>
								<span id="notification-counter-task" class="inline counter notification-subtabs-counter bg-danger badge"></span>
							</li>
						{/if}
						{if $USER_NOTIFICATIONS_PREFERENCE->show_customer_birthday_reminders == 1}
							<li class="nav-item relative topbar-icon" title="{vtranslate('LBL_TITLE_BIRTHDAY', 'CPNotifications')}" data-for="div#notification-tab-birthday-pane">
								<a class="nav-link"><i class="far fa-birthday-cake" aria-hidden="true"></i></a>
								<span id="notification-counter-birthday" class="inline counter notification-subtabs-counter bg-danger badge"></span>
							</li>
						{/if}
					</ul>

					<!-- Main tab content -->
					<div class="tab-content">
						<!-- Tab content: Notify -->
						<div id="notification-tab-notify-pane" class="tab-pane active">
							<ul class="nav nav-tabs notification-subtabs">
								<li class="nav-item active" title="{vtranslate('LBL_NOTIFY_SUBTAB_UPDATES', 'CPNotifications')}" data-for="div#notification-subtab-notify-update-pane">
									<a class="nav-link">
										<span class="relative inline-flex-center">
											<span>{vtranslate('LBL_NOTIFY_SUBTAB_UPDATES', 'CPNotifications')}</span>
											<span id="notification-counter-notify-update" class="notification-inline-counter"></span>
										</span>
									</a>
								</li>
								<li class="nav-item" title="{vtranslate('LBL_NOTIFY_SUBTAB_CHECKINS', 'CPNotifications')}" data-for="div#notification-subtab-notify-checkin-pane">
									<a class="nav-link">
										<span class="relative inline-flex-center">
											<span>{vtranslate('LBL_NOTIFY_SUBTAB_CHECKINS', 'CPNotifications')}</span>
											<span id="notification-counter-notify-checkin" class="notification-inline-counter"></span>
										</span>
									</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="notification-subtab-notify-update-pane" class="tab-pane subtab-pane active">
									<div class="notification-list fancyScrollbar" data-status="empty" data-type="notify" data-sub-type="update" data-offset="0">
										<div class="notification-items"></div>
										<div class="notification-list-footer">
											<div class="notification-loader"><i class="far fa-refresh fa-spin"></i></div>
											<div class="notification-empty-list">{vtranslate('LBL_NOTIFICATION_LIST_EMPTY', 'CPNotifications')}</div>
										</div>
									</div>
									<div class="notification-footer">
										<a class="footer-item" href="javascript:Notifications.markAsRead('update')">{vtranslate('LBL_READ_ALL', 'CPNotifications')}</a>
										<a class="footer-item" target="black" href="index.php?module=Vtiger&parent=Settings&view=UserNotifications">{vtranslate('LBL_CONFIGS', 'CPNotifications')}</a>
									</div>
								</div>
								<div id="notification-subtab-notify-checkin-pane" class="tab-pane subtab-pane">
									<div class="notification-list fancyScrollbar" data-status="empty" data-type="notify" data-sub-type="checkin" data-offset="0">
										<div class="notification-items"></div>
										<div class="notification-list-footer">
											<div class="notification-loader"><i class="far fa-refresh fa-spin"></i></div>
											<div class="notification-empty-list">{vtranslate('LBL_NOTIFICATION_LIST_EMPTY', 'CPNotifications')}</div>
										</div>
									</div>
									<div class="notification-footer">
										<a class="footer-item" href="javascript:Notifications.markAsRead('checkin')">{vtranslate('LBL_READ_ALL', 'CPNotifications')}</a>
										<a class="footer-item" target="black" href="index.php?module=Vtiger&parent=Settings&view=UserNotifications">{vtranslate('LBL_CONFIGS', 'CPNotifications')}</a>
									</div>
								</div>
							</div>
						</div>

						<!-- Tab content: Task -->
						<div id="notification-tab-activity-pane" class="tab-pane">
							<ul class="nav nav-tabs notification-subtabs">
								<li class="nav-item active" title="{vtranslate('LBL_TASK_GOING_TO_OVERDUE', 'CPNotifications')}" data-for="div#notification-subtab-activity-coming" data-group="tasks-navs">
									<a class="nav-link">
										<span class="relative inline-flex-center">
											<span>{vtranslate('LBL_TASK_GOING_TO_OVERDUE', 'CPNotifications')}<span>
											<span id="notification-counter-activity-coming" class="notification-inline-counter"></span>
										</span>
									</a>
								</li>
								<li class="nav-item" title="{vtranslate('LBL_TASK_OVERDUE', 'CPNotifications')}" data-for="div#notification-subtab-activity-overdue-pane" data-group="tasks-navs">
									<a class="nav-link">
										<span class="relative inline-flex-center">
											<span>{vtranslate('LBL_TASK_OVERDUE', 'CPNotifications')}</span>
											<span id="notification-counter-activity-overdue" class="notification-inline-counter"></span>
										</span>
									</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="notification-subtab-activity-coming" class="tab-pane subtab-pane active">
									<div class="notification-list fancyScrollbar" data-status="empty" data-type="activity" data-sub-type="coming" data-offset="0">
										<div class="notification-items"></div>
										<div class="notification-list-footer">
											<div class="notification-loader"><i class="far fa-refresh fa-spin"></i></div>
											<div class="notification-empty-list">{vtranslate('LBL_REMINDER_LIST_EMPTY', 'CPNotifications')}</div>
										</div>
									</div>
									<div class="notification-footer">
									</div>
								</div>
								<div id="notification-subtab-activity-overdue-pane" class="tab-pane subtab-pane">
									<div class="notification-list fancyScrollbar" data-status="empty" data-type="activity" data-sub-type="overdue" data-offset="0">
										<div class="notification-items"></div>
										<div class="notification-list-footer">
											<div class="notification-loader"><i class="far fa-refresh fa-spin"></i></div>
											<div class="notification-empty-list">{vtranslate('LBL_REMINDER_LIST_EMPTY', 'CPNotifications')}</div>
										</div>
									</div>
									<div class="notification-footer">
									</div>
								</div>
							</div>
						</div>

						<!-- Tab content: Birthday -->
						<div id="notification-tab-birthday-pane" class="tab-pane">
							<ul class="nav nav-tabs notification-subtabs">
								<li class="nav-item active" title="{vtranslate('LBL_BIRTHDAY_TODAY', 'CPNotifications')}" data-for="div#notification-subtab-birthday-today-pane" data-group="birthday-navs" data-list="">
									<a class="nav-link">
										<span class="relative inline-flex-center">
											<span>{vtranslate('LBL_BIRTHDAY_TODAY', 'CPNotifications')}</span>
											<span id="notification-counter-birthday-today" class="notification-inline-counter"></span>
										</span>
									</a>
								</li>
								<li class="nav-item" title="{vtranslate('LBL_BIRTHDAY_COMING', 'CPNotifications')}" data-for="div#notification-subtab-birthday-coming-pane" data-group="birthday-navs">
									<a class="nav-link">
										<span class="relative inline-flex-center">
											<span>{vtranslate('LBL_BIRTHDAY_COMING', 'CPNotifications')}</span>
											<span id="notification-counter-birthday-coming" class="notification-inline-counter"></span>
										</span>
									</a>
								</li>
							</ul>
							<div class="tab-content">
								<div id="notification-subtab-birthday-today-pane" class="tab-pane subtab-pane active">
									<div class="notification-list fancyScrollbar" data-status="empty" data-type="birthday" data-sub-type="today" data-offset="0">
										<div class="notification-items"></div>
										<div class="notification-list-footer">
											<div class="notification-loader"><i class="far fa-refresh fa-spin"></i></div>
											<div class="notification-empty-list">{vtranslate('LBL_REMINDER_LIST_EMPTY', 'CPNotifications')}</div>
										</div>
									</div>
									<div class="notification-footer">
									</div>
								</div>
								<div id="notification-subtab-birthday-coming-pane" class="tab-pane subtab-pane">
									<div class="notification-list fancyScrollbar" data-status="empty" data-type="birthday" data-sub-type="coming" data-offset="0">
										<div class="notification-items"></div>
										<div class="notification-list-footer">
											<div class="notification-loader"><i class="far fa-refresh fa-spin"></i></div>
											<div class="notification-empty-list">{vtranslate('LBL_REMINDER_LIST_EMPTY', 'CPNotifications')}</div>
										</div>
									</div>
									<div class="notification-footer">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Templates -->
			<div id="notification-templates" class="hide">
				<!-- Template: notify item -->
				<div class="notify-item">
					<div class="notify-item-avatar left">
						<div class="avatar-container"></div>
					</div>
					<div class="notify-item-container right">
						<div class="notify-item-content">
							<div class="notify-item-message"></div>
						</div>
						<div class="notify-item-footer">
							<div class="notify-item-createdtime"></div>
							<div class="notify-item-actions"></div>
						</div>
					</div>
				</div>

				<!-- Template: notify popup -->
				<div class="notify-popup">
					<form name="notify-popup">
						<div class="notify-popup-title">
							<div class="notify-popup-title-wrapper">
								{* <span class="notify-popup-title-related-module"></span>: *}
								<span class="notify-popup-title-content"></span>
							</div>
						</div>

						<div class="notify-popup-content content-wrapper"></div>

						<div class="notify-popup-tips">{vtranslate('LBL_NOTIFY_POPUP_TIPS', 'CPNotifications')}</div>

						<div class="notify-popup-footer">
							<div class="center">
								<a href="javascript:void(0)" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_BTN_DISMISS', 'CPNotifications')}</a>
								<button class="btn btn-success" type="submit" name="submit"><strong>{vtranslate('LBL_BTN_VIEW', 'CPNotifications')}</strong></button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<div id="notification-container">
				<div id="notification-poup-container">
				</div>
			</div>
		</div>

		{* Global constants *}
		<script>const _FCM_SENDER_ID = '{$GOOGLE_CONFIG.firebase.fcm_sender_id}';</script>

		{* Notification sources *}
		<link type="text/css" rel="stylesheet" href="{vresource_url('modules/CPNotifications/resources/Notifications.css')}" />
		<script async defer src="{vresource_url('modules/CPNotifications/resources/Notifications.js')}"></script>

		{* Firebase push notification *}
		<script src="https://www.gstatic.com/firebasejs/4.9.1/firebase.js"></script>
		<script src="https://www.gstatic.com/firebasejs/4.9.1/firebase-messaging.js"></script>
		<script async defer src="{vresource_url('modules/CPNotifications/resources/PushClient.js')}"></script>
	{/if}
{/strip}