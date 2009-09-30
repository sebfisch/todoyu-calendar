<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2, 
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Render holidays editor
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuHolidaysEditorRenderer {



	/**
	 * Renders holidaysets in admin module
	 *
	 *
	 * @return	Array
	 */
	public static function getAdminHolidaySetList()	{
		$returnArray = array();

		$holidaySets = Todoyu::db()->getArray('id, title, description', 'ext_calendar_holidayset', 'deleted = 0');

		foreach($holidaySets as $key => $holidaySet)	{
			$returnArray[] = array('id'						=> $holidaySet['id'],
			'label'					=> $holidaySet['title'],
			'additionalInformations'	=> $holidaySet['description']);
		}

		return $returnArray;
	}



	/**
	 * Renders holidaysets in admin module
	 *
	 *
	 * @return	Array
	 */
	public static function getAdminHolidayList()	{
		$returnArray = array();

		$holidays = Todoyu::db()->getArray('id, title, date', 'ext_calendar_holiday', 'deleted = 0', '', 'date DESC');

		foreach($holidays as $key => $holiday)	{
			$returnArray[] = array(
			'id'						=> $holiday['id'],
			'label'						=> $holiday['title'],
			'additionalInformations' 	=> date(TodoyuLocale::getLabel('core.dateFormat'), $holiday['date']));
		}

		return $returnArray;
	}
}

?>