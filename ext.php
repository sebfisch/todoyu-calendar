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
 * Extension main file for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

	// Declare ext ID, path
define('EXTID_CALENDAR', 104);
define('PATH_EXT_CALENDAR', PATH_EXT . '/calendar');

	// Register module locales
TodoyuLanguage::register('calendar', PATH_EXT_CALENDAR . '/locale/ext.xml');
TodoyuLanguage::register('event', PATH_EXT_CALENDAR . '/locale/event.xml');
TodoyuLanguage::register('panelwidget-calendar', PATH_EXT_CALENDAR . '/locale/panelwidget-calendar.xml');
TodoyuLanguage::register('panelwidget-eventtypeselector', PATH_EXT_CALENDAR . '/locale/panelwidget-eventtypeselector.xml');
TodoyuLanguage::register('panelwidget-holidaysetselector', PATH_EXT_CALENDAR . '/locale/panelwidget-holidaysetselector.xml');

	// Request configurations
	// @notice	Auto-loaded configs if available: admin, assets, create, contextmenu, extinfo, filters, form, page, panelwidgets, rights, search
require_once(PATH_EXT_CALENDAR . '/config/constants.php');
require_once(PATH_EXT_CALENDAR . '/config/extension.php');
require_once(PATH_EXT_CALENDAR . '/config/hooks.php');
require_once(PATH_EXT_CALENDAR . '/dwoo/plugins.php');

?>