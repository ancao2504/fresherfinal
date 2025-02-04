{*
    QuickAdminLinks.tpl
    Author: Hieu Nguyen
    Date: 2018-06-28
    Purpose: to show useful quick links for admin
    Usage: add new links in the smarty style here
*}

{strip}
    <li class="dropdown">
        <div>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                <i class="far fa-cog" aria-hidden="true" data-toggle="tooltip" title="Quick Admin Links"></i>
            </a>
            <ul style="width: 250px" class="dropdown-menu" role="menu">
                <li>
                    <a href="index.php?module=Vtiger&parent=Settings&view=Index">
                        <span><i class="far fa-cog" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_SETTINGS', $MODULE)}</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?module=Users&parent=Settings&view=List&block=1&fieldid=1">
                        <span><i class="far fa-user" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_USERS_MANAGEMENT', $MODULE)}</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?module=Groups&parent=Settings&view=List&block=1&fieldid=4">
                        <span><i class="far fa-users" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_GROUPS_MANAGEMENT', $MODULE)}</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?module=Roles&parent=Settings&view=Index&block=1&fieldid=2">
                        <span><i class="far fa-network-wired" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_ROLES_MANAGEMENT', $MODULE)}</span>
                    </a>
                </li>
                {* <li>
                    <a href="index.php?module=Profiles&parent=Settings&view=List&block=1&fieldid=3">
                        <span><i class="far fa-lock" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_PERMISSIONS_MANAGEMENT', $MODULE)}</span>
                    </a>
                </li> *}
                <li>
                    <a href="index.php?module=ModuleManager&parent=Settings&view=List&block=5&fieldid=8">
                        <span><i class="far fa-cubes" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_MODULES_MANAGEMENT', $MODULE)}</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?parent=Settings&module=Picklist&view=Index&block=8&fieldid=9">
                        <span><i class="far fa-edit" aria-hidden="true"></i> {vtranslate('LBL_ADMIN_DROPDOWN_EDITOR', $MODULE)}</span>
                    </a>
                </li>
                <li>
                    <a href="entrypoint.php?name=License&mode=showLicense">
                        <span><i class="far fa-file-certificate" aria-hidden="true"></i> {vtranslate('LBL_LICENSE_BUTTON_CHECK_LICENSE', $MODULE)}</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?module=Vtiger&parent=Settings&action=QuickRepair">
                        <span><i class="far fa-refresh" aria-hidden="true" data-toggle="tooltip" title="Quick Admin Links"></i> {vtranslate('LBL_ADMIN_QUICK_REPAIR', $MODULE)}</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
{/strip}