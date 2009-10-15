<?php

class TodoyuCalendarFormhandlingActionController extends TodoyuActionController {

	public static function addSubformAction(array $params) {
		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);
		$xmlPath 	= 'ext/calendar/config/form/' . $formName . '.xml';

//
//		if( ! is_file($xmlPath) )	{
//			$xmlPath	= 'ext/calendar/config/form/admin/' . $xmlFile;
//		}

		$form 	= new TodoyuForm($xmlPath);
		$form	= TodoyuFormHook::callBuildForm($xmlPath, $form, $index);

			// Load form data
		$data	= TodoyuFormHook::callLoadData($xmlPath, array(), $idRecord);

		$form->setName($formName);
		$form->setFormData($data);

		return $form->getField($fieldName)->renderNewRecord($index);
	}

}

?>