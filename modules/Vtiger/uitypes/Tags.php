<?php
/*
    Calss Vtiger_Tags_UIType
    Author: Hieu Nguyen
    Date: 2021-01-26
    Purpose: to render tags field
*/

class Vtiger_Tags_UIType extends Vtiger_Base_UIType {

	public function getListSearchTemplateName() {
        return '../../modules/Vtiger/tpls/TagsFieldSearchView.tpl';
    }

    public function getTemplateName() {
        return parent::getTemplateName();   // Does not support displaying tags in EditView
    }

	public function getDetailViewTemplateName() {
        return parent::getDetailViewTemplateName(); // Does not support displaying tags in DetailView
	}
}