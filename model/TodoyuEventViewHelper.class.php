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
		$eventTypes	= TodoyuEventManager::getEventTypes(true);

		foreach($eventTypes as $index => $eventType)  {
			$options[] = array(
				'value'		=> $index,
				'label'		=> $eventType['label']
			);
		}

		return $options;
	}

}

?>