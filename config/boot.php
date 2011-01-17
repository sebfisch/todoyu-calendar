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

	// Declare ext ID, path
define('EXTID_CALENDAR', 104);
define('PATH_EXT_CALENDAR', PATH_EXT . '/calendar');

require_once(PATH_EXT_CALENDAR . '/config/constants.php');
require_once(PATH_EXT_CALENDAR . '/dwoo/plugins.php');

	// Register module locales
TodoyuLabelManager::register('calendar', 'calendar', 'ext.xml');
TodoyuLabelManager::register('event', 'calendar', 'event.xml');
TodoyuLabelManager::register('panelwidget-calendar', 'calendar', 'panelwidget-calendar.xml');
TodoyuLabelManager::register('panelwidget-eventtypeselector', 'calendar', 'panelwidget-eventtypeselector.xml');
TodoyuLabelManager::register('panelwidget-holidaysetselector', 'calendar', 'panelwidget-holidaysetselector.xml');


// Add holiday set selector to company address form
TodoyuFormHook::registerBuildForm('ext/contact/config/form/address.xml', 'TodoyuCalendarManager::modifyAddressFormfields');

TodoyuFormHook::registerSaveData('ext/calendar/config/form/event.xml', 'TodoyuEventManager::hookSaveEvent');
TodoyuFormHook::registerSaveData('ext/calendar/config/form/quickevent.xml', 'TodoyuEventManager::hookSaveEvent');

?>