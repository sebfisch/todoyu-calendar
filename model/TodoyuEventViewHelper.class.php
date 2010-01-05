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

		foreach($eventTypes as $index => $eventType)  {
			$options[] = array(
				'value'		=> $eventType['key'],
				'label'		=> $eventType['label']
			);
		}

		return $options;
	}

}

?>