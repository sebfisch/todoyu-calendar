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
 * Extension main file for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

	// Declare ext ID, path
define('EXTID_CALENDAR', 104);
define('PATH_EXT_CALENDAR', PATH_EXT . '/calendar');

	// Register module locales
TodoyuLocale::register('calendar', PATH_EXT_CALENDAR . '/locale/ext.xml');
TodoyuLocale::register('event', PATH_EXT_CALENDAR . '/locale/event.xml');
TodoyuLocale::register('panelwidget-calendar', PATH_EXT_CALENDAR . '/locale/panelwidget-calendar.xml');
TodoyuLocale::register('panelwidget-quickevent', PATH_EXT_CALENDAR . '/locale/panelwidget-quickevent.xml');
TodoyuLocale::register('panelwidget-eventtypeselector', PATH_EXT_CALENDAR . '/locale/panelwidget-eventtypeselector.xml');
TodoyuLocale::register('panelwidget-holidaysetselector', PATH_EXT_CALENDAR . '/locale/panelwidget-holidaysetselector.xml');

	// Request configurations
require_once( PATH_EXT_CALENDAR . '/config/constants.php' );
require_once( PATH_EXT_CALENDAR . '/config/extension.php' );
require_once( PATH_EXT_CALENDAR . '/config/panelwidgets.php' );
require_once( PATH_EXT_CALENDAR . '/config/admin.php' );
require_once( PATH_EXT_CALENDAR . '/config/hooks.php' );
require_once( PATH_EXT_CALENDAR . '/dwoo/plugins.php');

?>