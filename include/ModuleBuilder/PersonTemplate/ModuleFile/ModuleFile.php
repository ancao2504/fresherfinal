<?php
    /***********************************************************************************************
     ** The contents of this file are subject to the Vtiger Module-Builder License Version 1.3
     * ( "License" ); You may not use this file except in compliance with the License
     * The Original Code is:  Technokrafts Labs Pvt Ltd
     * The Initial Developer of the Original Code is Technokrafts Labs Pvt Ltd.
     * Portions created by Technokrafts Labs Pvt Ltd are Copyright ( C ) Technokrafts Labs Pvt Ltd.
     * All Rights Reserved.
     **
     *************************************************************************************************/

    include_once 'modules/Vtiger/CRMEntity.php';

    class ModuleClass extends Vtiger_CRMEntity {
        var $table_name = 'vtiger_%tableName';
        var $table_index = '%tableNameid';

        var $isPerson = true;

        /**
         * Mandatory table for supporting custom fields.
         */
        var $customFieldTable = Array(
            'vtiger_%tableNamecf',
            '%tableNameid'
        );

        /**
         * Mandatory for Saving, Include tables related to this module.
         */
        var $tab_name = Array(
            'vtiger_crmentity',
            'vtiger_%tableName',
            'vtiger_%tableNamecf'
        );

        var $entity_table = "vtiger_crmentity";

        /**
         * Other Related Tables
         */
        var $related_tables = Array(
            'vtiger_%tableNamecf' => Array('%tableNameid')
        );

        /**
         * Mandatory for Saving, Include tableName and tablekey columnname here.
         */
        var $tab_name_index = Array(
            'vtiger_crmentity' => 'crmid',
            'vtiger_%tableName' => '%tableNameid',
            'vtiger_%tableNamecf' => '%tableNameid'
        );

        /**
         * Mandatory for Listing (Related listview)
         */
        var $list_fields = Array(
            'LBL_FULL_NAME' => Array('vtiger_crmentity' => 'label'),
            'Assigned To' => Array('vtiger_crmentity' => 'smownerid')
        );

        var $list_fields_name = Array(
            'LBL_FULL_NAME' => Array('vtiger_crmentity' => 'label'),
            'Assigned To' => 'assigned_user_id'
        );

        // Make the field link to detail view
        var $list_link_field = 'full_name';

        // For Popup listview and UI type support
        var $search_fields = Array(
            'LBL_FULL_NAME' => Array(
                'vtiger_crmentity',
                'label'
            ),
            'Assigned To' => Array(
                'vtiger_crmentity',
                'assigned_user_id'
            ),
        );
        var $search_fields_name = Array(
            'LBL_FULL_NAME' => 'full_name',
            'Assigned To' => 'assigned_user_id',
        );

        // For Popup window record selection
        var $popup_fields = Array('full_name');

        // For Alphabetical search
        var $def_basicsearch_col = 'full_name';

        // Column value to use on detail view record text display
        var $def_detailview_recname = 'full_name';

        // Used when enabling/disabling the mandatory fields for the module.
        // Refers to vtiger_field.fieldname values.
        var $mandatory_fields = Array('firstname', 'lastname', 'assigned_user_id', 'createdtime', 'modifiedtime');

        var $default_order_by = 'full_name';
        var $default_sort_order = 'ASC';

    }