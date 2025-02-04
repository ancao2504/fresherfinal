<?php

/*
    Owner Field UI Type
    Author: Hieu Nguyen
    Date: 2020-02-20
    Purpose: to make this field in Event module single selection and select User only
*/

class Events_Owner_UIType extends Vtiger_Owner_UIType {

	public function getTemplateName() {
		return 'uitypes/Owner.tpl';
	}
}