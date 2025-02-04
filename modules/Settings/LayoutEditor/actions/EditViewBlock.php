<?php

/*
    Action EditViewBlock
    Author: Hieu Nguyen
    Date: 2018-07-30
    Purpose: to seperate logic from Action Block (which is for DetailView)
*/

class Settings_LayoutEditor_EditViewBlock_Action extends Settings_LayoutEditor_Block_Action {
    
    public function __construct() {
        $this->view = 'EditView';
        $this->exposeMethod('save');
        $this->exposeMethod('updateSequenceNumber');
        $this->exposeMethod('delete');
    }
}