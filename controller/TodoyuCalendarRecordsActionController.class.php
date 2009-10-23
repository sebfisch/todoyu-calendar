<?php


class TodoyuCalendarRecordsActionController extends TodoyuActionController {

	public static function addSubformAction(array $params) {
		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);
		$xmlBase 	= 'ext/calendar/config/form/admin';

		switch($fieldName) {
			case 'holiday':
				$xmlPath	= $xmlBase . '/holidayset.xml';
				break;

			case 'holidayset':
				$xmlPath	= $xmlBase . '/holiday.xml';
				break;
		}

			// Load form data
		$data	= TodoyuFormHook::callLoadData($xmlPath, array(), $idRecord);

		return TodoyuFormManager::renderSubformRecord($xmlPath, $fieldName, $formName, $index, $idRecord, $data);
	}



}

?>