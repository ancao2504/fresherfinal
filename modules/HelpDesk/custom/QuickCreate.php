<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.28
 * @desc Helpdesk quickcreate customize
 */

$displayParams = array(
	'scripts' => '
		<script type="text/javascript" src="{vresource_url("modules/HelpDesk/resources/Form.js")}"></script>
		<script type="text/javascript" src="{vresource_url("modules/HelpDesk/resources/QuickCreate.js")}"></script>
	',
	'form' => array(
		'hiddenFields' => '',
	),
	'fields' => array(
	),
);