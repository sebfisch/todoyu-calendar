<?php

class TodoyuEventViewHelper {


	/**
	 * Get Event types in a form-readable format
	 *
	 * @param 	TodoyuFormElement 	$field
	 * @return	Array
	 */
	public static function getEventTypeOptions(TodoyuFormElement $field) {
		$options	= array();
		$eventTypes	= TodoyuEventTypeManager::getEventTypes(true);
		$reform		= array(
			'index'	=> 'value',
			'label'	=> 'label'
		);

		return TodoyuArray::reform($eventTypes, $reform, false);
	}

}

?>