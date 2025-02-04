<?php
    /* 
    *   Config Environment
    *   Added by Hieu Nguyen on 2018-07-21
    *   Instruction: copy config.env.sample.php into config.env.php and change the config that you want
    *   Note: config.env.php should be ignore in git repository, each developer should create this file himself to prevent conflict
    */

    //ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING
    //ini_set('display_errors','on'); error_reporting(E_ALL); // STRICT DEVELOPMENT

    /* database configuration
        db_server
        db_port
        db_hostname
        db_username
        db_password
        db_name
    */

    $dbconfig['db_server'] = 'localhost';
    $dbconfig['db_port'] = ':3306';
    $dbconfig['db_username'] = 'root';
    $dbconfig['db_password'] = 'mysql';
    $dbconfig['db_name'] = 'vtiger';
    $dbconfig['db_type'] = 'mysqli';
    $dbconfig['db_status'] = 'true';

    $dbconfig['db_hostname'] = $dbconfig['db_server'].$dbconfig['db_port'];

    // log_sql default value = false
    $dbconfig['log_sql'] = false;

    // persistent default value = true
    $dbconfigoption['persistent'] = true;

    // autofree default value = false
    $dbconfigoption['autofree'] = false;

    // debug default value = 0
    $dbconfigoption['debug'] = 0;

    // seqname_format default value = '%s_seq'
    $dbconfigoption['seqname_format'] = '%s_seq';

    // portability default value = 0
    $dbconfigoption['portability'] = 0;

    // ssl default value = false
    $dbconfigoption['ssl'] = false;

    $host_name = $dbconfig['db_hostname'];

    $site_URL = 'http://localhost/vtiger';

    // root directory path
    $root_directory = '/Users/hieunguyen/Webroot/vtiger/';

    // Added by Hieu Nguyen on 2020-02-24 to prevent duplicate login session or not
    $preventDuplicateLoginSession = false;  // Set true for customer, set false for local and internal / demo links
?>