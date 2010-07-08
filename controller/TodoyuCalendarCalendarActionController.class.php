<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Calendar action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarCalendarActionController extends TodoyuActionController {

	/**
	 * Calendar update action method: Saves date and active tab and rerenders the calendar
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function updateAction(array $params) {
		restrict('calendar', 'general:use');



		$time	= strtotime($params['date']);
		$tab	= $params['tab'];

		$string	= strftime('%A %d', 1278280800);

//		TodoyuDebug::printInFireBug(mb_convert_encoding($string, 'UTF-8', 'UTF-16'), 'converted');


		
//		TodoyuDebug::printInFireBug(TodoyuString::isUTF8($string), 'string');
//		TodoyuDebug::printInFireBug(TodoyuString::getAsUtf8($string), 'string');
		TodoyuDebug::printInFireBug(iconv('UTF-16', 'UTF-8//TRANSLIT', $string), '$string');

//		TodoyuDebug::printInFireBug(iconv('ISO-8859-1', 'UTF-8', strftime('%A %d', time())));


//		TodoyuString::isUTF8()

//		$encodings	= 'UTF-32,UTF-16,UTF-8,ISO 8859-15,ISO 8859-1,ASCII';
//
//
//		$res	= iconv("UTF-16", "UTF-8", $string);
//		
//		$enc1	= mb_detect_encoding($string, $encodings);
//		$enc2	= mb_detect_encoding('test', $encodings);
//		$enc3	= mb_detect_encoding('testäöü', $encodings);
//
//		TodoyuDebug::printInFireBug($res, '$res');
//		TodoyuDebug::printInFireBug(TodoyuString::isUTF8('test'), 'test');
//		TodoyuDebug::printInFireBug(TodoyuString::isUTF8('testäöü'), 'testäöü');
//		TodoyuDebug::printInFireBug(TodoyuString::isUTF8($string), '$string');
		

//		TodoyuDebug::printInFireBug(strftime('%A', 1278280800));

		

		TodoyuPanelWidgetCalendar::saveDate($time);
		TodoyuCalendarPreferences::saveActiveTab($tab);

		$calendar = TodoyuCalendarRenderer::renderCalendar($time, $tab);

		return $calendar;
	}

}

?>