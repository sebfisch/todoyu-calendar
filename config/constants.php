<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
 * Constants for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

	// Calendar viewing modes
define('CALENDAR_MODE_DAY', 		1);
define('CALENDAR_MODE_WEEK', 		2);
define('CALENDAR_MODE_MONTH', 		3);
define('CALENDAR_MODE_EVENTVIEW', 	4);



	// Event types
	// @see	referring keys are defined in extension.php
define('EVENTTYPE_GENERAL', 	1);
define('EVENTTYPE_AWAY', 		2);
define('EVENTTYPE_BIRTHDAY', 	3);
define('EVENTTYPE_VACATION', 	4);
define('EVENTTYPE_EDUCATION', 	5);
define('EVENTTYPE_MEETING', 	6);
define('EVENTTYPE_AWAYOFFICIAL',7);
define('EVENTTYPE_HOMEOFFICE', 	8);
//define('EVENTTYPE_PAPER', 		9);
//define('EVENTTYPE_CARTON', 		10);
define('EVENTTYPE_COMPENSATION',11);
define('EVENTTYPE_MILESTONE', 	12);
define('EVENTTYPE_REMINDER', 	13);



	// Height of an hour, minute in day- and week- view of calendar
define('CALENDAR_HEIGHT_HOUR',		42);
define('CALENDAR_HEIGHT_MINUTE',	0.683);

define('CALENDAR_DAY_EVENT_WIDTH', 620);
define('CALENDAR_WEEK_EVENT_WIDTH', 86);

	// Maximal date: 2030-12-31, 23:59:59
define('CALENDAR_MAXDATE', 1924988399);

	// Minimal duration of an event for rendering
define('CALENDAR_EVENT_MIN_DURATION', 1800);

?>