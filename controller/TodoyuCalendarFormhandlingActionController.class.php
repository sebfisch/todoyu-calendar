<?php

class TodoyuCalendarFormhandlingActionController extends TodoyuActionController {

	public static function addSubformAction(array $params) {
		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);


		if( $formName === 'record' ) {
			$xmlBase	= 'ext/calendar/config/form/admin/';

			switch($fieldName) {
				case 'holidayset':
					$xmlPath = $xmlBase . 'holiday.xml';
					break;
			}

		} else {
			$xmlBase	= 'ext/calendar/config/form/';

			switch($fieldName) {
				case 'user':
					$xmlPath = $xmlBase . $formName . '.xml';
					break;
			}

		}

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