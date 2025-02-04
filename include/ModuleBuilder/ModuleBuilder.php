<?php
    /**
     * Author: Kelvin Thang
     * Company: OnlineCRM
     * Date: 2018-08-02
     * Class Module builder
     */
    class ModuleBuilder {
        var $src;
        var $dst;
        var $module;
        var $app;
        var $displayEn;
        var $displayVn;
        
        //--Begin: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new
        var $hasActivities;
        var $isExtension;
        var $isPerson;
        //--End: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new

        var $createdBy; // Added by Hieu Nguyen on 2021-08-05 to determine this module is crated by R&D or Dev team

        var $printResult;

        function __construct($module, $printResult) {
            $this->src = 'include/ModuleBuilder/ModuleTemplate';
            $this->dst = './modules/' . $module['moduleName'];;
            $this->module = $module['moduleName'];
            $this->app = $module['menu'];
            $this->displayEn = $module['displayNameEn'];
            $this->displayVn = $module['displayNameVn'];

            //--Begin: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new
            $this->hasActivities = $module['hasActivities'];
            $this->isExtension = $module['isExtension'];
            $this->isPerson = $module['isPerson'];
            //--End: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new

            // Added by Hieu Nguyen on 2021-08-05 to determine this module is crated by R&D or Dev team
            $this->createdBy = ($module['createdBy'] == 'base') ? 'base' : 'dev';
            // End Hieu Nguyen

            $this->printResult = $printResult;

            // Added by Hieu Nguyen on 2019-01-02 to support extension module
            $this->templateFolder = 'ModuleTemplate';

            if($module['isExtension'] === true) {
                $this->isExtension = true;
                $this->templateFolder = 'ExtensionTemplate';
                $this->src = 'include/ModuleBuilder/ExtensionTemplate';
            }
            // End Hieu Nguyen

            // BEGIN-- Added by Phu Vo on 2020.08.11 to support person module
            if ($module['isPerson'] === true) {
                $this->isPerson = true;
                $this->templateFolder = 'PersonTemplate';
                $this->src = 'include/ModuleBuilder/PersonTemplate';
            }
            // END-- Added by Phu Vo on 2020.08.11 to support person module

            $this->initModuleBuilder();

            //-- Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new
            $this->addRelatedActivity();
        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function static build for module builder
         * @param string $moduleName
         * @param bool $printResult
         * @return result
         */
        static function build($moduleName = '', $printResult = true) {
            global $customModules;

            require_once('include/Extensions/CustomModules.php');

            foreach($customModules as $module) {
                if(!empty($moduleName) ){
                    if($module['moduleName'] == $moduleName) {
                        $moduleID = new ModuleBuilder($module, $printResult);

                        return array(
                            'success' => (!empty($moduleID)) ? 1 : 0,
                            'message' => ((!empty($moduleID)) ? vtranslate('LBL_MODULE_BUILDER_SUCCESSFULLY') : vtranslate('MODULE_BUILDER_FAILED')) . $moduleName,
                        );
                    }
                } else {
                    new ModuleBuilder($module, $printResult);
                }
            }
        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function init For module builder
         * @return bool
         */
        function initModuleBuilder() {

            require_once 'vtlib/Vtiger/Module.php';

            $success = 0;
            $message = '';
            $moduleName = $this->module;

            if (Vtiger_Module::getInstance($moduleName)) {
                if($this->printResult){
                    echo vtranslate('LBL_MODULE_BUILDER_ALREADY') . $moduleName.'<br/>';
                }

            } else {

                if($this->printResult){
                    echo vtranslate('LBL_MODULE_BUILDER') . $moduleName.'<br/>';
                }

                $moduleInstance = new Vtiger_Module();
                $moduleInstance->name = $moduleName;
                $moduleInstance->parent = $this->app;

                // Added by Hieu Nguyen on 2019-01-02 to support extension module
                if($this->isExtension) {
                    $moduleInstance->isentitytype = 0;
                }
                // End Hieu Nguyen

                $moduleID = $moduleInstance->save();

                // Modified by Hieu Nguyen on 2019-01-02 to support extension module
                if (!file_exists('modules/' . $moduleName)) {
                    $this->copyFolder($this->src, $this->dst, '', 'BASE');
                    $this->replaceModuleBack();

                    $langApply = array('en_us', 'vn_vn');
                    foreach ($langApply as $folder) {
                        $this->replaceModuleLang($folder);
                    }

                }else{

                    if($this->printResult){
                        echo vtranslate('LBL_MODULE_BUILDER_ALREADY') . $moduleName.'<br/>';
                    }

                }
                if($this->printResult){
                    echo vtranslate('LBL_MODULE_BUILDER_SUCCESSFULLY') . $moduleName.'<br/>';
                }

                if($this->isExtension) {
                    return $moduleID;
                }

                // Schema Setup
                $moduleInstance->initTables();

                $this->addBlockAndFields($moduleInstance);
                $this->addRelatedModules($moduleInstance);

                // Sharing Access Setup
                $moduleInstance->setDefaultSharing();

                // Webservice Setup
                $moduleInstance->initWebservice();
                // End Hieu Nguyen

                // Enable and Disable available tools
                $moduleInstance->enableTools(Array('Import', 'Export', 'Merge'));

                //-- Copy field layout from detail view to edit view
                Settings_ModuleManager_Module_Model::initNewModule($moduleName);

                // Added by Hieu Nguyen on 2019-03-04
                $fileTypeForSaving = $this->createdBy;

                if (!file_exists(Vtiger_BlockAndField_Helper::getRegisterFile($moduleName, $fileTypeForSaving))) {
                    Vtiger_BlockAndField_Helper::syncToRegisterFile(['id' => $moduleID, 'name' => $moduleName], $fileTypeForSaving);
                }
                // End Hieu Nguyen

                // Modified by Hieu Nguyen on 2021-07-19 to add module to menu after the module is created
                if ($this->app) {
                    Settings_MenuEditor_Module_Model::addModuleToApp($moduleName, $this->app);
                }
                // End Hieu Nguyen

                // Added by Hieu Nguyen on 2021-08-05 to save audit log
                Vtiger_AdminAudit_Helper::saveLog('ModuleBuilder', "Create module {$moduleName} in menu {$this->app}");
                // End Hieu Nguyen

                return $moduleID;
            }

        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function add Blocks and fields default for module template further processing
         */
        function addBlockAndFields($moduleInstance) {
            // BEGIN-- Phu Vo on 2020.08.11 to support person module
            if ($this->isPerson == true) return $this->addPersonBlocksAndFields($moduleInstance);
            // END-- Phu Vo on 2020.08.11 to support person module

            // Field Setup
            $generalBlock = new Vtiger_Block();
            $generalBlock->label = 'LBL_GENERAL_INFORMATION';
            $generalBlock->blockTableName = 'vtiger_editview_blocks';   // Modified by Hieu Nguyen on 2021-08-05 to create block in EditView
            $moduleInstance->addBlock($generalBlock);

            $trackBlock = new Vtiger_Block();
            $trackBlock->label = 'LBL_TRACKING_INFOMATION';
            $trackBlock->blockTableName = 'vtiger_editview_blocks';     // Modified by Hieu Nguyen on 2021-08-05 to create block in EditView
            $moduleInstance->addBlock($trackBlock);

            $name  = new Vtiger_Field();
            $name->name = 'name';
            $name->label= 'LBL_NAME';
            $name->uitype= 2;
            $name->column = $name->name;
            $name->columntype = 'VARCHAR(255)';
            $name->typeofdata = 'V~M';
            $name->summaryfield = 1;
            $generalBlock->addField($name);
            $moduleInstance->setEntityIdentifier($name);

            // Added by Hieu Nguyen on 2021-07-29 to create auto id field
            $code = new Vtiger_Field();
            $code->name = strtolower($moduleInstance->name) . '_no';
            $code->label = $moduleInstance->name . ' No';
            $code->uitype = 4;
            $code->column = $code->name;
            $code->columntype = 'VARCHAR(100)';
            $code->typeofdata = 'V~O';
            $code->summaryfield = 1;
            $generalBlock->addField($code);
            // End Hieu Nguyen

            $description  = new Vtiger_Field();
            $description->name = 'description';
            $description->label= 'LBL_DESCRIPTION';
            $description->uitype= 19;
            $description->column = 'description';
            $description->summaryfield = 1;
            $description->table = 'vtiger_crmentity';
            $trackBlock->addField($description);

            // Recommended common fields every Entity module should have (linked to core table)
            $assigned = new Vtiger_Field();
            $assigned->name = 'assigned_user_id';
            $assigned->label = 'LBL_ASSIGNED_TO';
            $assigned->table = 'vtiger_crmentity';
            $assigned->column = 'smownerid';
            $assigned->uitype = 53;
            $assigned->typeofdata = 'V~M';
            $generalBlock->addField($assigned);

            // Added by Hieu Nguyen on 2019-08-22
            $mainOwnerId = new Vtiger_Field();
            $mainOwnerId->name = 'main_owner_id';
            $mainOwnerId->label = 'LBL_MAIN_OWNER_ID';
            $mainOwnerId->table = 'vtiger_crmentity';
            $mainOwnerId->column = 'main_owner_id';
            $mainOwnerId->uitype = 53;
            $mainOwnerId->typeofdata = 'V~O';
            $generalBlock->addField($mainOwnerId);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2019-01-02
            $createdBy = new Vtiger_Field();
            $createdBy->name = 'createdby';
            $createdBy->label = 'LBL_CREATED_BY';
            $createdBy->table = 'vtiger_crmentity';
            $createdBy->column = 'smcreatorid';
            $createdBy->uitype = 52;
            $createdBy->typeofdata = 'V~O';
            $generalBlock->addField($createdBy);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2020-09-28
            $userDepartment = new Vtiger_Field();
            $userDepartment->column = 'users_department';
            $userDepartment->name = 'users_department';
            $userDepartment->label = 'LBL_USERS_DEPARTMENT';
            $userDepartment->uitype = 16;
            $userDepartment->displaytype = 2;
            $userDepartment->quickcreate = 0;
            $userDepartment->masseditable = 0;
            $userDepartment->typeofdata = 'V~O';
            $userDepartment->maximumlength = 100;
            $trackBlock->addField($userDepartment);
            // End Hieu Nguyen

            $createdTime = new Vtiger_Field();
            $createdTime->name = 'createdtime';
            $createdTime->label= 'LBL_CREATED_TIME';
            $createdTime->table = 'vtiger_crmentity';
            $createdTime->column = 'createdtime';
            $createdTime->uitype = 70;
            $createdTime->typeofdata = 'DT~O';
            $createdTime->displaytype= 2;
            $generalBlock->addField($createdTime);

            $modifiedTime = new Vtiger_Field();
            $modifiedTime->name = 'modifiedtime';
            $modifiedTime->label= 'LBL_MODIFIED_TIME';
            $modifiedTime->table = 'vtiger_crmentity';
            $modifiedTime->column = 'modifiedtime';
            $modifiedTime->uitype = 70;
            $modifiedTime->typeofdata = 'DT~O';
            $modifiedTime->displaytype= 2;
            $generalBlock->addField($modifiedTime);

            // Added by Phu Vo on 2021.7.13
            $modifiedBy = new Vtiger_Field();
            $modifiedBy->name = 'modifiedby';
            $modifiedBy->label= 'Last Modified By';
            $modifiedBy->table = 'vtiger_crmentity';
            $modifiedBy->column = 'modifiedby';
            $modifiedBy->uitype = 52;
            $modifiedBy->typeofdata = 'V~O';
            $modifiedBy->displaytype= 2;
            $generalBlock->addField($modifiedBy);
            // End Phu Vo

            /* NOTE: Vtiger 7.1.0 onwards */
            $source = new Vtiger_Field();
            $source->name = 'source';
            $source->label = 'LBL_SOURCE_INPUT';
            $source->table = 'vtiger_crmentity';
            $source->displaytype = 2; // to disable field in Edit View
            $source->quickcreate = 3;
            $source->masseditable = 0;
            $generalBlock->addField($source);

            $starred = new Vtiger_Field();
            $starred->name = 'starred';
            $starred->label = 'LBL_STARRED';
            $starred->table = 'vtiger_crmentity_user_field';
            $starred->displaytype = 6;
            $starred->uitype = 56;
            $starred->typeofdata = 'C~O';
            $starred->quickcreate = 3;
            $starred->masseditable = 0;
            $generalBlock->addField($starred);

            $tags = new Vtiger_Field();
            $tags->name = 'tags';
            $tags->label = 'LBL_TAGS';
            $tags->displaytype = 6;
            $tags->columntype = 'VARCHAR(1)';
            $tags->quickcreate = 3;
            $tags->masseditable = 0;
            $generalBlock->addField($tags);
            /* End 7.1.0 */

            // Filter Setup
            $filterAll = new Vtiger_Filter();
            $filterAll->name = 'All';
            $filterAll->isdefault = true;
            $moduleInstance->addFilter($filterAll);
            $filterAll->addField($name)->addField($description, 1)->addField($assigned, 2)->addField($createdTime, 3)->addField($starred, 4)->addField($tags, 5);

            return true;
        }

        /** Implemented by Phu Vo on 2020.08.11 */
        function addPersonBlocksAndFields($moduleInstance) {
            // Block Setup
            $generalBlock = new Vtiger_Block();
            $generalBlock->label = 'LBL_GENERAL_INFORMATION';
            $generalBlock->blockTableName = 'vtiger_editview_blocks';   // Modified by Hieu Nguyen on 2021-08-05 to create block in EditView
            $moduleInstance->addBlock($generalBlock);

            $trackBlock = new Vtiger_Block();
            $trackBlock->label = 'LBL_TRACKING_INFOMATION';
            $trackBlock->blockTableName = 'vtiger_editview_blocks';     // Modified by Hieu Nguyen on 2021-08-05 to create block in EditView
            $moduleInstance->addBlock($trackBlock);

            $addressBlock = new Vtiger_Block();
            $addressBlock->label = 'LBL_ADDRESS_INFORMATION';
            $addressBlock->blockTableName = 'vtiger_editview_blocks';   // Modified by Hieu Nguyen on 2021-08-05 to create block in EditView
            $moduleInstance->addBlock($addressBlock);

            // Field Setup
            $salutation = new Vtiger_Field();
            $salutation->name = 'salutationtype';
            $salutation->label = 'Salutation';
            $salutation->uitype = 55;
            $salutation->column = $salutation->name;
            $salutation->columntype = 'VARCHAR(200)';
            $salutation->displaytype = 3;
            $salutation->typeofdata = 'V~O';
            $salutation->summaryfield = 1;
            $generalBlock->addField($salutation);

            $lastName = new Vtiger_Field();
            $lastName->name = 'lastname';
            $lastName->label = 'LBL_LASTNAME';
            $lastName->uitype = 255;
            $lastName->column = $lastName->name;
            $lastName->columntype = 'VARCHAR(100)';
            $lastName->typeofdata = 'V~O';
            $lastName->summaryfield = 2;
            $generalBlock->addField($lastName);

            $firstName = new Vtiger_Field();
            $firstName->name = 'firstname';
            $firstName->label = 'LBL_FIRSTNAME';
            $firstName->uitype = 255;
            $firstName->column = $firstName->name;
            $firstName->columntype = 'VARCHAR(100)';
            $firstName->typeofdata = 'V~M';
            $firstName->summaryfield = 1;
            $generalBlock->addField($firstName);

            $fullName = new Vtiger_Field();
            $fullName->name = 'full_name';
            $fullName->label = 'LBL_FULL_NAME';
            $fullName->uitype = 255;
            $fullName->column = 'label';
            $fullName->columntype = 'VARCHAR(255)';
            $fullName->displaytype = 3;
            $fullName->typeofdata = 'V~O';
            $fullName->summaryfield = 1;
            $fullName->table = 'vtiger_crmentity';
            $generalBlock->addField($fullName);

            $phone = new Vtiger_Field();
            $phone->name = 'phone';
            $phone->label = 'LBL_PHONE';
            $phone->uitype = 11;
            $phone->column = $phone->name;
            $phone->columntype = 'VARCHAR(100)';
            $phone->typeofdata = 'V~O';
            $phone->summaryfield = 1;
            $generalBlock->addField($phone);

            $mobile = new Vtiger_Field();
            $mobile->name = 'mobile';
            $mobile->label = 'LBL_MOBILE';
            $mobile->uitype = 11;
            $mobile->column = $mobile->name;
            $mobile->columntype = 'VARCHAR(100)';
            $mobile->typeofdata = 'V~O';
            $mobile->headerfield = 1;
            $generalBlock->addField($mobile);

            $fax = new Vtiger_Field();
            $fax->name = 'fax';
            $fax->label = 'LBL_FAX';
            $fax->uitype = 11;
            $fax->column = $fax->name;
            $fax->columntype = 'VARCHAR(100)';
            $fax->typeofdata = 'V~O';
            $fax->summaryfield = 1;
            $generalBlock->addField($fax);

            $email = new Vtiger_Field();
            $email->name = 'email';
            $email->label = 'LBL_EMAIL';
            $email->uitype = 13;
            $email->column = $email->name;
            $email->columntype = 'VARCHAR(100)';
            $email->typeofdata = 'E~O';
            $email->headerfield = 1;
            $generalBlock->addField($email);

            $otherEmail = new Vtiger_Field();
            $otherEmail->name = 'other_email';
            $otherEmail->label = 'LBL_OTHER_EMAIL';
            $otherEmail->uitype = 13;
            $otherEmail->column = $otherEmail->name;
            $otherEmail->columntype = 'VARCHAR(100)';
            $otherEmail->typeofdata = 'E~O';
            $otherEmail->summaryfield = 1;
            $generalBlock->addField($otherEmail);

            $website = new Vtiger_Field();
            $website->name = 'website';
            $website->label = 'LBL_WEBSITE';
            $website->uitype = 17;
            $website->column = $website->name;
            $website->columntype = 'VARCHAR(100)';
            $website->typeofdata = 'V~O';
            $website->summaryfield = 0;
            $generalBlock->addField($website);

            // Recommended common fields every Entity module should have (linked to core table)
            $assigned = new Vtiger_Field();
            $assigned->name = 'assigned_user_id';
            $assigned->label = 'LBL_ASSIGNED_TO';
            $assigned->table = 'vtiger_crmentity';
            $assigned->column = 'smownerid';
            $assigned->uitype = 53;
            $assigned->typeofdata = 'V~M';
            $generalBlock->addField($assigned);

            // Added by Hieu Nguyen on 2019-08-22
            $mainOwnerId = new Vtiger_Field();
            $mainOwnerId->name = 'main_owner_id';
            $mainOwnerId->label = 'LBL_MAIN_OWNER_ID';
            $mainOwnerId->table = 'vtiger_crmentity';
            $mainOwnerId->column = 'main_owner_id';
            $mainOwnerId->uitype = 53;
            $mainOwnerId->typeofdata = 'V~O';
            $generalBlock->addField($mainOwnerId);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2019-01-02
            $createdBy = new Vtiger_Field();
            $createdBy->name = 'createdby';
            $createdBy->label = 'LBL_CREATED_BY';
            $createdBy->table = 'vtiger_crmentity';
            $createdBy->column = 'smcreatorid';
            $createdBy->uitype = 52;
            $createdBy->typeofdata = 'V~O';
            $generalBlock->addField($createdBy);
            // End Hieu Nguyen

            $createdTime = new Vtiger_Field();
            $createdTime->name = 'createdtime';
            $createdTime->label= 'LBL_CREATED_TIME';
            $createdTime->table = 'vtiger_crmentity';
            $createdTime->column = 'createdtime';
            $createdTime->uitype = 70;
            $createdTime->typeofdata = 'DT~O';
            $createdTime->displaytype= 2;
            $generalBlock->addField($createdTime);

            $modifiedTime = new Vtiger_Field();
            $modifiedTime->name = 'modifiedtime';
            $modifiedTime->label= 'LBL_MODIFIED_TIME';
            $modifiedTime->table = 'vtiger_crmentity';
            $modifiedTime->column = 'modifiedtime';
            $modifiedTime->uitype = 70;
            $modifiedTime->typeofdata = 'DT~O';
            $modifiedTime->displaytype= 2;
            $generalBlock->addField($modifiedTime);

            // Added by Phu Vo on 2021.7.13
            $modifiedBy = new Vtiger_Field();
            $modifiedBy->name = 'modifiedby';
            $modifiedBy->label= 'Last Modified By';
            $modifiedBy->table = 'vtiger_crmentity';
            $modifiedBy->column = 'modifiedby';
            $modifiedBy->uitype = 52;
            $modifiedBy->typeofdata = 'V~O';
            $modifiedBy->displaytype= 2;
            $generalBlock->addField($modifiedBy);
            // End Phu Vo

            /* NOTE: Vtiger 7.1.0 onwards */
            $source = new Vtiger_Field();
            $source->name = 'source';
            $source->label = 'LBL_SOURCE_INPUT';
            $source->table = 'vtiger_crmentity';
            $source->displaytype = 2; // to disable field in Edit View
            $source->quickcreate = 3;
            $source->masseditable = 0;
            $generalBlock->addField($source);

            $starred = new Vtiger_Field();
            $starred->name = 'starred';
            $starred->label = 'LBL_STARRED';
            $starred->table = 'vtiger_crmentity_user_field';
            $starred->displaytype = 6;
            $starred->uitype = 56;
            $starred->typeofdata = 'C~O';
            $starred->quickcreate = 3;
            $starred->masseditable = 0;
            $generalBlock->addField($starred);

            $tags = new Vtiger_Field();
            $tags->name = 'tags';
            $tags->label = 'LBL_TAGS';
            $tags->displaytype = 6;
            $tags->columntype = 'VARCHAR(1)';
            $tags->quickcreate = 3;
            $tags->masseditable = 0;
            $generalBlock->addField($tags);
            /* End 7.1.0 */

            $description  = new Vtiger_Field();
            $description->name = 'description';
            $description->label= 'LBL_DESCRIPTION';
            $description->uitype= 19;
            $description->column = 'description';
            $description->summaryfield = 1;
            $description->table = 'vtiger_crmentity';
            $trackBlock->addField($description);

            // Address fields
            $lane = new Vtiger_Field();
            $lane->name = 'lane';
            $lane->label = 'LBL_LANE';
            $lane->uitype = 1;
            $lane->column = $lane->name;
            $lane->columntype = 'VARCHAR(100)';
            $lane->typeofdata = 'V~O~LE~255';
            $lane->summaryfield = 0;
            $addressBlock->addField($lane);

            $pobox = new Vtiger_Field();
            $pobox->name = 'pobox';
            $pobox->label = 'LBL_POBOX';
            $pobox->uitype = 1;
            $pobox->column = $pobox->name;
            $pobox->columntype = 'VARCHAR(100)';
            $pobox->typeofdata = 'V~O~LE~255';
            $pobox->summaryfield = 0;
            $addressBlock->addField($pobox);

            $code = new Vtiger_Field();
            $code->name = 'code';
            $code->label = 'LBL_CODE';
            $code->uitype = 1;
            $code->column = $code->name;
            $code->columntype = 'VARCHAR(100)';
            $code->typeofdata = 'V~O~LE~255';
            $code->summaryfield = 0;
            $addressBlock->addField($code);

            $city = new Vtiger_Field();
            $city->name = 'city';
            $city->label = 'LBL_CITY';
            $city->uitype = 1;
            $city->column = $city->name;
            $city->columntype = 'VARCHAR(100)';
            $city->typeofdata = 'V~O~LE~255';
            $city->summaryfield = 0;
            $addressBlock->addField($city);

            $country = new Vtiger_Field();
            $country->name = 'country';
            $country->label = 'LBL_COUNTRY';
            $country->uitype = 1;
            $country->column = $country->name;
            $country->columntype = 'VARCHAR(100)';
            $country->typeofdata = 'V~O~LE~255';
            $country->summaryfield = 0;
            $addressBlock->addField($country);

            $state = new Vtiger_Field();
            $state->name = 'state';
            $state->label = 'LBL_STATE';
            $state->uitype = 1;
            $state->column = $state->name;
            $state->columntype = 'VARCHAR(100)';
            $state->typeofdata = 'V~O~LE~255';
            $state->summaryfield = 0;
            $addressBlock->addField($state);

            // Setup entity name field
            $entityNameField = New Vtiger_Field();
            $entityNameField->name = 'lastname,firstname';
            $entityNameField->table = $moduleInstance->basetable;
            $moduleInstance->setEntityIdentifier($entityNameField);

            // Setup default filter
            $filterAll = new Vtiger_Filter();
            $filterAll->name = 'All';
            $filterAll->isdefault = true;
            $moduleInstance->addFilter($filterAll);
            $filterAll->addField($fullName);
            $filterAll->addField($phone, 1);
            $filterAll->addField($mobile, 2);
            $filterAll->addField($fax, 3);
            $filterAll->addField($email, 4);
            $filterAll->addField($otherEmail, 5);
            $filterAll->addField($description, 6);
            $filterAll->addField($assigned, 7);
            $filterAll->addField($createdTime, 8);
            $filterAll->addField($starred, 9);
            $filterAll->addField($tags, 10);

            return true;
        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function add modules relationship default for module template further processing
         * -- Modified date 2019-10-30 -- Fixed the New module not related to Activity and refactor code.
         */
        function addRelatedModules($moduleInstance) {
            global $adb;
            require_once 'vtlib/Vtiger/Module.php';
            require_once 'modules/ModComments/ModComments.php';
            require_once ('modules/ModTracker/ModTracker.php');
            require_once ('include/Webservices/WebserviceField.php');

            $moduleName = $this->module;

            //-- Init ModComments
            ModComments::addWidgetTo($moduleName);

            //-- Enable ModTracker
            ModTracker :: enableTrackingForModule($moduleInstance->id);

            //-- Init Activity
            /*
            $calendarInstance = Vtiger_Module::getInstance('Calendar');
            $fieldCalendarInstance = Vtiger_Field::getInstance('parent_id', $calendarInstance);
            $moduleInstance->setRelatedList($calendarInstance, 'Activities', array('ADD'), 'get_activities', $fieldCalendarInstance->id);

            //-- Set the Current Module reference in Activity
            $insertReferenceModule =  "INSERT INTO vtiger_ws_referencetype(fieldtypeid , type ) VALUES( ( SELECT fieldtypeid FROM vtiger_ws_fieldtype WHERE uitype = {$fieldCalendarInstance->uitype} ) , '{$moduleName}' )";
            $adb->pquery($insertReferenceModule, []);
            */

            return true;
        }

        /*
         * Author: Kelvin Thang
         * Date: 2019-10-30
         * Purpose: init add Related Activity
         * Rule:
         *  - Apply to the Module Custom (CP...)
         *  - Apply has $customModules['enableActivity']
         *  - Do not apply to the Module Extension
        */
        function addRelatedActivity() {
            $moduleInstance = Vtiger_Module_Model::getInstance($this->module);

            //-- Get UI Type field parent_id in Calendar
            $calendarInstance = Vtiger_Module::getInstance('Calendar');
            $fieldCalendarInstance = Vtiger_Field::getInstance('parent_id', $calendarInstance);

            //-- module Extension not Activities or not enable Activity
            if ($this->isExtension || !isset($this->hasActivities)) return;

            $relationModel = Vtiger_Relation_Model::getInstance($moduleInstance, $calendarInstance);

            if (!$this->hasActivities) { //-- check unset Activity
                if ($relationModel) {
                    if ($relationModel->get('relationfieldid') == $fieldCalendarInstance->id) {
                        $moduleInstance->unsetRelatedList($calendarInstance, 'Activities', 'get_activities');
                    }
                }
            }
            else { //-- check set Activity
                if (!$relationModel) {
                    $moduleInstance->setRelatedList($calendarInstance, 'Activities', array('ADD'), 'get_activities', $fieldCalendarInstance->id);
                }
            }
        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function to copy default module template folder for further processing
         */
        function copyFolder($src, $dst, $request, $base) {
            global $log;
            $log->debug("Entering copy_folder($src,$dst,request array(),$base) method....");

            $content = '';
            $dir = opendir($src);
            if ($src == 'include/ModuleBuilder/'. $this->templateFolder .'/languages') {     // Modified by Hieu Nguyen on 2019-01-03
                $dst = './languages';
            }
            if ($src == 'include/ModuleBuilder/'. $this->templateFolder .'/ModuleFile') {     // Modified by Hieu Nguyen on 2019-01-03
                $dst = './modules/' . $this->module;
            }

            // -- create folder for modulebuilder
            if (!file_exists($dst))
                @mkdir($dst);

            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->copyFolder($src . '/' . $file, $dst . '/' . $file, $request, 'PATH');
                    } else {
                        @copy($src . '/' . $file, $dst . '/' . $file);

                    }
                }

                if ($file == 'ModuleFile.php') {
                    @rename($dst . '/' . "ModuleFile.php", $dst . '/' . $this->module . ".php");
                }
                if ($file == 'languages') {
                    @rename($dst . '/languages/' . "ModuleFileLang.php", $dst . '/languages/' . $this->module . ".php");
                }
            }
            @closedir($dir);


            /*if ($base == 'BASE') {
                $this->export($request);
            }*/
            $log->debug("Exiting copy_folder($src,$dst,request array(),$base) method....");


        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function to replace module  template folder for further processing
         */
        function replaceModuleBack() {
            $moduleName = $this->module;

            //To replace modulename and tablenames
            $fileName = 'modules/' . $moduleName . '/' . $moduleName . '.php';
            if (file_exists($fileName)) {
                $fileContents = file_get_contents($fileName);

                $entityIdentifierLabel = 'Name';
                $entityIdentifierField = 'name';
                $replacedContent = array(
                    'ModuleClass' => $moduleName,
                    '%tableName' => strtolower($moduleName),
                    '%entityIdentifierLabel' => $entityIdentifierLabel,
                    '%entityIdentifierField' => $entityIdentifierField,
                    '%labelField' => $entityIdentifierField,
                    '%label' => $entityIdentifierLabel,
                );
                $finalContent = replaceKeys($fileContents, $replacedContent);

                $this->writeContentModule($fileName, $finalContent);
            }

            // Added by Hieu Nguyen on 2019-01-03 to support extension module
            $file = 'modules/' . $moduleName . '/views/List.php';

            if(file_exists($file)) {
                $fileContent = file_get_contents($file);

                $mapping = array(
                    'ModuleName' => $moduleName,
                );

                $finalContent = replaceKeys($fileContent, $mapping);

                $this->writeContentModule($file, $finalContent);
            }
            // End Hieu Nguyen
        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function to replace languages template folder for further processing
         */
        function replaceModuleLang($lang) {
            $moduleName = $this->module;
            $moduleLable = $moduleName;

            if($lang == 'en_us' && !empty($this->displayEn)){
                $moduleLable = $this->displayEn;
            }
            if($lang == 'vn_vn' && !empty($this->displayVn)){
                $moduleLable = $this->displayVn;
            }

            $fileName = 'languages/' . $lang . '/' . $moduleName . '.php';

            if (file_exists($fileName)) {
                $langFileContents = file_get_contents($fileName);

                $replacedContent = array(
                    '%ModuleName' => $moduleName,
                    '%moduleLable' => $moduleLable,
                );
                $langReplaced = replaceKeys($langFileContents, $replacedContent);


                $this->writeContentModule($fileName, $langReplaced);
            }
        }

        /**
         * Author: Kelvin Thang
         * Date: 2018-08-02
         * Function to write content for file in module.
         */
        function writeContentModule($fileName, $content) {
            $fp = @fopen($fileName, "w");
            fwrite($fp, $content);
            fclose($fp);
        }

        // Implemented by Hieu Nguyen on 2018-09-17
        static function initInventoryModule($moduleName) {
            $moduleInstance = Vtiger_Module::getInstance($moduleName);

            // Create item details block in EditView
            $detailsBlockEV = new Vtiger_Block();
            $detailsBlockEV->label = 'LBL_ITEM_DETAILS';
            $detailsBlockEV->blockTableName = 'vtiger_editview_blocks';
            $moduleInstance->addBlock($detailsBlockEV);

            // Then add inventory fields into this block
            if ($detailsBlockEV->id) {
                global $salesModuleFields, $inventoryFields;
                require_once('include/ModuleBuilder/SalesModuleFields.php');
                $fieldIds = array();

                foreach ($salesModuleFields as $field) {
                    $fieldInstance = new Vtiger_Field();
                    $fieldInstance->table = $moduleInstance->basetable;
                    $fieldInstance->name = $field['field_name'];
                    $fieldInstance->column = $field['column_name'];
                    $fieldInstance->uitype = $field['ui_type'];
                    $fieldInstance->typeofdata = $field['data_type'];
                    $fieldInstance->masseditable = 0;
                    $fieldInstance->displaytype = 5;
                    $fieldInstance->label = $field['label'];
                    $fieldInstance->presence = $field['display'] === false ? 1 : 2;

                    if ($field['details_block']) {
                        $detailsBlockEV->addField($fieldInstance);
                    }
                    else {
                        // TODO: maybe later
                    }

                    // Collect field id
                    $fieldIds[] = $fieldInstance->id;
                }

                foreach ($inventoryFields as $field) {
                    $fieldInstance = new Vtiger_Field();
                    $fieldInstance->table = $field['table_name'];
                    $fieldInstance->name = $field['field_name'];
                    $fieldInstance->column = $field['column_name'];
                    $fieldInstance->uitype = $field['ui_type'];
                    $fieldInstance->typeofdata = $field['data_type'];
                    $fieldInstance->masseditable = 0;
                    $fieldInstance->displaytype = 5;
                    $fieldInstance->label = $field['label'];
                    $fieldInstance->presence = $field['display'] === false ? 1 : 2;
                    $detailsBlockEV->addField($fieldInstance);

                    // Collect field id
                    $fieldIds[] = $fieldInstance->id;
                }
            }

            // Finally, create item details block in DetailView and then add all those fields to this block
            if (!empty($fieldIds)) {
                global $adb;

                $detailsBlockDV = new Vtiger_Block();
                $detailsBlockDV->label = 'LBL_ITEM_DETAILS';
                $detailsBlockDV->blockTableName = 'vtiger_blocks';
                $moduleInstance->addBlock($detailsBlockDV);

                // Use sql to update all fields
                $fieldIdList = "'". join("', '", $fieldIds) . "'";
                $sql = "UPDATE vtiger_field SET block = ?, sequence = editview_sequence, presence = editview_presence WHERE fieldid IN ({$fieldIdList})";
                $params = array($detailsBlockDV->id);
                $adb->pquery($sql, $params);
            }
        }
    }
