<?php

/*
    Action EditViewField
    Author: Hieu Nguyen
    Date: 2018-07-30
    Purpose: to seperate logic from Action Field (which is for DetailView)
*/

class Settings_LayoutEditor_EditViewField_Action extends Settings_LayoutEditor_Field_Action {

    function __construct() {
		parent::__construct();

        $this->view = 'EditView';
        $this->exposeMethod('save');
        $this->exposeMethod('move');
        $this->exposeMethod('unHide');
    }

}