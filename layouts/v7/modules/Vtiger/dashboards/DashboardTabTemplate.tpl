{* Added by Hieu Nguyen on 2020-10-12 to seperate the dashboard tab HTML into a single file for common usage *}

<li class="{if $TAB_DATA['id'] eq $SELECTED_TAB}active{/if} dashboardTab" data-tabid="{$TAB_DATA['id']}">
    <a data-toggle="tab" href="">
        <span class="name textOverflowEllipsis">{$TAB_DATA['tabname']}</span>
    </a>

    {* Modified by Phu Vo on 2020.10.30 *}
    {if Home_DashboardLogic_Helper::canEditDashboard() && !isForbiddenFeature('DashboardEditor')}	{* Modified by Hieu Nguyen on 2022-05-12 to check forbidden feature *}
        <span class="edit-buttons">
            {if $CURRENT_USER->isAdminUser() || $TAB_DATA['isdefault'] eq 0}
                <i class="far fa-pen renameTab"></i>
            {/if}

            {if $CURRENT_USER->isAdminUser() || $TAB_DATA['isdefault'] eq 0}
                <i class="far fa-trash-alt deleteTab"></i>
            {/if}
        </span>
    {/if}
    {* End Phu Vo *}

    <i class="far fa-grip-lines moveTab hide"></i>
</li>