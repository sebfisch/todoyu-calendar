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
 * Constants for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

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
define('EVENTTYPE_PAPER', 		9);
define('EVENTTYPE_CARTON', 		10);
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

?>