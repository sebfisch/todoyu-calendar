<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * Calendar eventtype manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuEventTypeManager {

	/**
	 * Add a new eventtype
	 *
	 * @param	Integer		$index
	 * @param	String		$label
	 */
	public static function addEventType($index, $key, $label) {
		$index	= intval($index);

		Todoyu::$CONFIG['EXT']['calendar']['eventtypes'][$index] = array(
			'index'		=> $index,
			'key'		=> $key,
			'label'		=> $label
		);
	}



	/**
	 * Get eventtype data
	 *
	 * @param	String		$index
	 * @return	Array
	 */
	public static function getEventType($index) {
		return TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['eventtypes'][$index]);
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
			$label = TodoyuLanguage::getLabel($label);
		}

		return $label;
	}



	/**
	 * Get the key of an eventtype
	 *
	 * @param	Integer		$index
	 * @return	String
	 */
	public static function getEventTypeKey($index) {
		$eventType	= self::getEventType($index);

		return $eventType['key'];
	}



	/**
	 * Get all eventtypes
	 *
	 * @param	Bool		$parseLabels
	 * @return	Array
	 */
	public static function getEventTypes($parseLabels = false) {
		$eventTypes = TodoyuArray::assure(Todoyu::$CONFIG['EXT']['calendar']['eventtypes']);

		foreach($eventTypes as $index => $eventType) {
			$eventTypes[$index]['value'] 	= $index;

			if( $parseLabels ) {
				$eventTypes[$index]['label'] 	= Label($eventType['label']);
			}

			$eventTypes[$index]['class'] = 'eventtype_' . $eventType['key'];
		}

		return $eventTypes;
	}



	/**
	 * Get eventtypes which are allowed to be overbooked
	 *
	 * @return	Array
	 */
	public static function getOverbookableTypeIndexes() {
		return Todoyu::$CONFIG['EXT']['calendar']['EVENTTYPES_OVERBOOKABLE'];
	}



	/**
	 * Get eventtypes which are not allowed to be overbooked
	 *
	 * @return	Array
	 */
	public static function getNotOverbookableTypeIndexes() {
		$overbookableTypes	= self::getOverbookableTypeIndexes();
		$allEventTypes		= self::getEventTypeKeys();

		$nonOverbookableTypeIndexes	= array();

		foreach( $allEventTypes as $index => $typeKey ) {
			$idType	= constant('EVENTTYPE_' . strtoupper($typeKey));
			if ( ! in_array($idType, $overbookableTypes)  ) {
				$nonOverbookableTypeIndexes[]	= $idType;
			}
		}

		return $nonOverbookableTypeIndexes;
	}



	/**
	 * Get all eventtype indexed (numerical)
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