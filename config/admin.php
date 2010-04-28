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
 * Administration for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */



// add holiday sets to records area of sysadmin
TodoyuExtManager::addRecordConfig('calendar', 'holidayset', array(
	'label'		=> 'LLL:calendar.applicableholidayset',
	'list'		=> 'TodoyuHolidaySetManager::getRecords',
	'form'		=> 'ext/calendar/config/form/admin/holidayset.xml',
	'object'	=> 'TodoyuHolidaySet',
	'delete'	=> 'TodoyuHolidaySetManager::deleteHolidaySet',
	'save'		=> 'TodoyuHolidaySetManager::saveHolidaySet',
	'table'		=> 'ext_calendar_holidayset'
));

// add holidays to records area of sysadmin
TodoyuExtManager::addRecordConfig('calendar', 'holiday', array(
	'label'		=> 'LLL:calendar.holiday',
	'list'		=> 'TodoyuHolidayManager::getRecords',
	'form'		=> 'ext/calendar/config/form/admin/holiday.xml',
	'object'	=> 'TodoyuHoliday',
	'delete'	=> 'TodoyuHolidayManager::deleteHoliday',
	'save'		=> 'TodoyuHolidayManager::saveHoliday',
	'table'		=> 'ext_calendar_holiday'
));

?>