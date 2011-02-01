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
 * Assets (JS, CSS, SWF, etc.) requirements for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

Todoyu::$CONFIG['EXT']['calendar']['assets'] = array(
	'js' => array(
		array(
			'file' => 'ext/calendar/assets/js/Ext.js',
			'position' => 100
		),
		array(
			'file' => 'ext/calendar/assets/js/QuickCreateEvent.js',
			'position' => 100
		),
		array(
				'file' => 'ext/calendar/assets/js/HolidayEditor.js',
				'position' => 102
		),
		array(
			'file' => 'ext/calendar/assets/js/Tabs.js',
			'position' => 103
		),
		array(
			'file' => 'ext/calendar/assets/js/Navi.js',
			'position' => 104
		),
		array(
			'file' => 'ext/calendar/assets/js/QuickInfoBirthday.js',
			'position' => 107
		),
		array(
			'file' => 'ext/calendar/assets/js/QuickInfoEvent.js',
			'position' => 108
		),
		array(
			'file' => 'ext/calendar/assets/js/QuickInfoHoliday.js',
			'position' => 109
		),
		array(
			'file' => 'ext/calendar/assets/js/Event.js',
			'position' => 117
		),
		array(
			'file' => 'ext/calendar/assets/js/EventView.js',
			'position' => 118
		),
		array(
			'file' => 'ext/calendar/assets/js/EventEdit.js',
			'position' => 119
		),
		array(
			'file' => 'ext/calendar/assets/js/EventPortal.js',
			'position' => 119
		),
		array(
			'file' => 'ext/calendar/assets/js/CalendarBody.js',
			'position' => 120
		),
		array(
			'file' => 'ext/calendar/assets/js/DragDrop.js',
			'position' => 121
		),
		array(
			'file' => 'ext/calendar/assets/js/ContextMenuCalendarBody.js',
			'position' => 121
		),
		array(
			'file' => 'ext/calendar/assets/js/ContextMenuEvent.js',
			'position' => 122
		),
		array(
			'file' => 'ext/calendar/assets/js/ContextMenuEventPortal.js',
			'position' => 123
		),
		array(
			'file' => 'lib/js/scal/javascripts/scal.js',
			'position' => 30
		),
		array(
			'file' => 'ext/calendar/assets/js/PanelWidgetCalendar.js',
			'position' => 110
		),
		array(
			'file' => 'ext/calendar/assets/js/PanelWidgetEventTypeSelector.js',
			'position' => 140
		),
		array(
			'file' => 'ext/calendar/assets/js/PanelWidgetHolidaySetSelector.js',
			'position' => 150
		),
		array(
			'file'		=> 'ext/calendar/assets/js/Reminder.js',
			'position'	=> 200
		),
		array(
			'file'		=> 'lib/js/scriptaculous/sound.js',
			'position'	=> 200
		)
	),
	'css' => array(
		array(
			'file'		=> 'ext/calendar/assets/css/contextmenu.css',
			'position'	=> 80
		),
		array(
			'file'		=> 'ext/calendar/assets/css/quickinfo.css',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/calendar/assets/css/ext.css',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/calendar/assets/css/calendarbody.css',
			'poisition' => 101
		),
		array(
			'file'		=> 'ext/calendar/assets/css/day.css',
			'poisition' => 102
		),
		array(
			'file'		=> 'ext/calendar/assets/css/week.css',
			'poisition'	=> 103
		),
		array(
			'file'		=> 'ext/calendar/assets/css/month.css',
			'poisition' => 104
		),
		array(
			'file'		=> 'ext/calendar/assets/css/scal.css',
			'position'	=> 105
		),
		array(
			'file'		=> 'ext/calendar/assets/css/event.css',
			'position'	=> 106
		),
		array(
			'file' => 'ext/calendar/assets/css/scal.css',
			'position' => 30
		),
		array(
			'file' => 'ext/calendar/assets/css/panelwidget-calendar.css',
			'position' => 110
		),
		array(
			'file' => 'ext/calendar/assets/css/panelwidget-eventtpyeselector.css',
			'position' => 140
		),
		array(
			'file' => 'ext/calendar/assets/css/panelwidget-holidaysetselector.css',
			'position' => 150
		)
	)

);

?>