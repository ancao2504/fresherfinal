<?php

class Accounts_ModelEntity_View extends CustomView_Base_View {

    function __construct() {
        parent::__construct($isFullView = true);
    }

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();

        // Write your own logic to check for access permission
        $allowAccess = true; // Set this to false if a user's role is not permitted

        if(!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->display('modules/Accounts/tpls/ModelEntity.tpl');
        $accounts = Accounts_Record_Model::getAccountsByType("Competitor"); 
        $accountCount = count($accounts);
  print_r($accounts);
 echo "<style>
 table {
     border-collapse: collapse;
     width: 100%;
 }
 th, td {
     border: 1px solid #ddd;
     padding: 10px; /* Thêm padding */
     text-align: left;
 }
 th {
     background-color: #f2f2f2;
 }
</style>";
echo "<h3>Total Accounts: " . htmlspecialchars($accountCount) . "</h3>";
echo "<table>";
echo "<tr>
    <th>Select</th>
    <th>Account ID</th>
    <th>Account Name</th>
    <th>Annual Revenues</th>
    <th>Lead Source</th>
    <th>Phone</th>
    <th>Email</th>
</tr>";
foreach ($accounts as $account) {
    echo "<tr>";
    echo "<td><input type='checkbox' class='recordCheckbox' data-id='" . htmlspecialchars($account['accountid']) . "'></td>";
    echo "<td>" . htmlspecialchars($account['accountid'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($account['accountname'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($account['annual_revenues'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($account['leadsource'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($account['phone'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($account['email1'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";
    }
}
?>
