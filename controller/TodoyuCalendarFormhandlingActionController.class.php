<?php

class TodoyuCalendarFormhandlingActionController extends TodoyuActionController {

	public static function addSubformAction(array $params) {
		$index		= intval($params['indexOfForeignRecord']);
		$xmlFile	= TodoyuDiv::makeCleanFilename($params['form'] . '.xml');

			// Construct form object
		$xmlPath 	= 'ext/calendar/config/form/' . $xmlFile;

		if( ! is_file($xmlPath) )	{
			$xmlPath	= 'ext/calendar/config/form/admin/' . $xmlFile;
		}

		$form 	= new TodoyuForm($xmlPath);
		$form	= TodoyuFormHook::callBuildForm($xmlPath, $form, $index);

			// Prepare, set data
		$formField		= $form->getField($params['field']);
		$form['name']	= $params['formname'];

		$formData	= TodoyuFormHook::callLoadData($xmlPath, array(), 0);
		$form->setFormData( $formData );

		if( method_exists($formField, 'addNewRecord') )	{
			return $formField->addNewRecord($index);
		} else {
			return 'sorry wrong type of field';
		}
	}

}

?>