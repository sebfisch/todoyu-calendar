<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Calendar eventType manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventTypeManager {

	/**
	 * Add a new event type
	 *
	 * @param	Integer		$index
	 * @param	String		$key
	 * @param	String		$label
	 */
	public static function addEventType($index, $key, $label) {
		$index	= intval($index);

		Todoyu::$CONFIG['EXT']['calendar']['eventtypes'][$index]	= array(
			'index'		=> $index,
			'key'		=> $key,
			'label'		=> $label
		);
	}



	/**
	 * Get event type data
	 *
	 * @param	String		$index
	 * @param	Boolean		$parseLabel
	 * @return	Array
	 */
	public static function getEventType($index, $parseLabel = false) {
		$eventType	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['eventtypes'][$index]);

		if( $parseLabel ) {
			$eventType['label']	= Todoyu::Label($eventType['label']);
		}

		return $eventType;
	}



	/**
	 * Get label of the event type
	 *
	 * @param	Integer		$index
	 * @param	Boolean		$parsed
	 * @return	String
	 */
	public static function getEventTypeLabel($index, $parsed = true) {
		$eventType	= self::getEventType($index);
		$label		= $eventType['label'];

		if( $parsed ) {
			$label	= Todoyu::Label($label);
		}

		return $label;
	}



	/**
	 * Get the key of an event type
	 *
	 * @param	Integer		$index
	 * @return	String
	 */
	public static function getTypeKey($index) {
		$eventType	= self::getEventType($index);

		return $eventType['key'];
	}



	/**
	 * Get all event types
	 *
	 * @param	Boolean		$parseLabels
	 * @return	Array
	 */
	public static function getEventTypes($parseLabels = false) {
		$eventTypes	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['eventtypes']);

		foreach($eventTypes as $index => $eventType) {
			$eventTypes[$index]['value']	= $index;

			if( $parseLabels ) {
				$eventTypes[$index]['label']= Todoyu::Label($eventType['label']);
			}

			$eventTypes[$index]['class']	= 'eventtype_' . $eventType['key'];
		}

		return $eventTypes;
	}



	/**
	 * Get options array of event types
	 *
	 * @return	Array
	 */
	public static function getEventTypeOptions() {
		$jobTypes	= self::getEventTypes(true);

		return TodoyuArray::sortByLabel($jobTypes, 'label');
	}



	/**
	 * Get event types which are allowed to be overbooked
	 *
	 * @return	Integer[]
	 */
	public static function getOverbookableTypeIndexes() {
		return Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE'];
	}



	/**
	 * Get event types which are not allowed to be overbooked
	 *
	 * @return	Array
	 */
	public static function getNotOverbookableTypeIndexes() {
		$overbookableTypes	= self::getOverbookableTypeIndexes();
		$allEventTypes		= self::getEventTypeKeys();

		$nonOverbookableTypeIndexes	= array();

		foreach( $allEventTypes as $typeKey ) {
			$idType	= constant('EVENTTYPE_' . strtoupper($typeKey));
			if( ! in_array($idType, $overbookableTypes)  ) {
				$nonOverbookableTypeIndexes[]	= $idType;
			}
		}

		return $nonOverbookableTypeIndexes;
	}



	/**
	 * Get all event type indexed (numerical)
	 *
	 * @return	Array
	 */
	public static function getEventTypeIndexes() {
		$eventTypes	= self::getEventTypes(false);

		return TodoyuArray::getColumn($eventTypes, 'index');
	}



	/**
	 * Get event type keys (textual)
	 *
	 * @return	Array
	 */
	public static function getEventTypeKeys() {
		$eventTypes	= self::getEventTypes(false);

		return TodoyuArray::getColumn($eventTypes, 'key');
	}

}

?>