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
 * Context menu configuration for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

/**
 * Context menu for calendar area (not clicked on event)
 */
Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Area'] = array(
	'add'	=> array(
		'key'		=> 'add',
		'label'		=> 'calendar.event.contextmenu.addEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.addEvent(#ID#)',
		'class'		=> 'eventContextMenu eventAdd',
		'position'	=> 10
	)
);



/**
 * General event context menu
 */
Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['Event'] = array(
	'show'	=> array(
		'key'		=> 'show',
		'label'		=> 'calendar.event.contextmenu.showEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.show(#ID#)',
		'class'		=> 'eventContextMenu eventShow',
		'position'	=> 10
	),
	'edit'	=> array(
		'key'		=> 'edit',
		'label'		=> 'calendar.event.contextmenu.editEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.edit(#ID#)',
		'class'		=> 'eventContextMenu eventEdit',
		'position'	=> 20
	),
	'remove'	=> array(
		'key'		=> 'delete',
		'label'		=> 'calendar.event.contextmenu.deleteEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.remove(#ID#)',
		'class'		=> 'eventContextMenu eventRemove',
		'position'	=> 30
	),
	'add'	=> array(
		'key'		=> 'add',
		'label'		=> 'calendar.event.contextmenu.addEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.addEventOnSameTime(#ID#)',
		'class'		=> 'eventContextMenu eventAdd',
		'position'	=> 40
	),
	'reminderemail' => array(
		'key'		=> 'reminderemail',
		'label'		=> 'calendar.event.contextmenu.eventReminderEmail',
		'jsAction'	=> 'void(0)',
		'class'		=> 'eventContextMenu eventReminderEmail',
		'position'	=> 50,
		'submenu'	=> array(
			'none'	=> array(
				'key'		=> 'remindertime-none',
				'label'		=> 'calendar.event.contextmenu.reminderTime.none',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_PLANNING . ')',
				'class'		=> 'eventContextMenu reminderTimeNone'
			),
			'0'	=> array(
				'key'		=> 'remindertime-0',
				'label'		=> 'calendar.event.contextmenu.reminderTime.0',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime0'
			),
			'5m'	=> array(
				'key'		=> 'remindertime-5m',
				'label'		=> 'calendar.event.contextmenu.reminderTime.5m',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime5m'
			),
			'15m'	=> array(
				'key'		=> 'remindertime-15m',
				'label'		=> 'calendar.event.contextmenu.reminderTime.15m',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime15m'
			),
			'30m'	=> array(
				'key'		=> 'remindertime-30m',
				'label'		=> 'calendar.event.contextmenu.reminderTime.30m',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime30m'
			),
			'1h'	=> array(
				'key'		=> 'reminderemail-1h',
				'label'		=> 'calendar.event.contextmenu.reminderTime.1h',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime1h'
			),
			'2h'	=> array(
				'key'		=> 'remindertime-2h',
				'label'		=> 'calendar.event.contextmenu.reminderTime.2h',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime2h'
			),
			'12h'	=> array(
				'key'		=> 'remindertime-12h',
				'label'		=> 'calendar.event.contextmenu.reminderTime.12h',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime12h'
			),
			'1d'	=> array(
				'key'		=> 'remindertime-1d',
				'label'		=> 'calendar.event.contextmenu.reminderTime.1d',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime1d'
			),
			'2d'	=> array(
				'key'		=> 'remindertime-2d',
				'label'		=> 'calendar.event.contextmenu.reminderTime.2d',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime2d'
			),
			'1w'	=> array(
				'key'		=> 'remindertime-1w',
				'label'		=> 'calendar.event.contextmenu.reminderTime.1w',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime1w'
			)
		)
	),
	'reminderpopup' => array(
		'key'		=> 'reminderpopup',
		'label'		=> 'calendar.event.contextmenu.eventReminderPopup',
		'jsAction'	=> 'void(0)',
		'class'		=> 'eventContextMenu eventReminderPopup',
		'position'	=> 60,
		'submenu'	=> array(
			'none'	=> array(
				'key'		=> 'remindertime-none',
				'label'		=> 'calendar.event.contextmenu.reminderTime.none',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_PLANNING . ')',
				'class'		=> 'eventContextMenu reminderTimeNone'
			),
			'0'	=> array(
				'key'		=> 'remindertime-0',
				'label'		=> 'calendar.event.contextmenu.reminderTime.0',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime0'
			),
			'5m'	=> array(
				'key'		=> 'remindertime-5m',
				'label'		=> 'calendar.event.contextmenu.reminderTime.5m',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime5m'
			),
			'15m'	=> array(
				'key'		=> 'remindertime-15m',
				'label'		=> 'calendar.event.contextmenu.reminderTime.15m',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime15m'
			),
			'30m'	=> array(
				'key'		=> 'remindertime-30m',
				'label'		=> 'calendar.event.contextmenu.reminderTime.30m',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime30m'
			),
			'1h'	=> array(
				'key'		=> 'reminderemail-1h',
				'label'		=> 'calendar.event.contextmenu.reminderTime.1h',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime1h'
			),
			'2h'	=> array(
				'key'		=> 'remindertime-2h',
				'label'		=> 'calendar.event.contextmenu.reminderTime.2h',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime2h'
			),
			'12h'	=> array(
				'key'		=> 'remindertime-12h',
				'label'		=> 'calendar.event.contextmenu.reminderTime.12h',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime12h'
			),
			'1d'	=> array(
				'key'		=> 'remindertime-1d',
				'label'		=> 'calendar.event.contextmenu.reminderTime.1d',
				'jsAction'	=> '', //'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime1d'
			),
			'2d'	=> array(
				'key'		=> 'remindertime-2d',
				'label'		=> 'calendar.event.contextmenu.reminderTime.2d',
				'jsAction'	=> '', //Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime2d'
			),
			'1w'	=> array(
				'key'		=> 'remindertime-1w',
				'label'		=> 'calendar.event.contextmenu.reminderTime.1w',
				'jsAction'	=> '', //Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'eventContextMenu reminderTime1w'
			)
		)
	)
);



/**
 * Context menu for events in portal area
 */
Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['EventPortal'] = array(
	'show'	=> array(
		'key'		=> 'show',
		'label'		=> 'calendar.event.contextmenu.showEventInCalendar',
		'jsAction'	=> 'void(0)',
		'class'		=> 'eventContextMenu eventShow',
		'position'	=> 10,
		'submenu'	=> array(
			'day'	=> array(
				'key'		=> 'day',
				'label'		=> 'calendar.event.contextmenu.showEventInCalendar.day',
				'jsAction'	=> 'Todoyu.Ext.calendar.Event.goToEventInCalendar(#ID#, #DATE#, \'day\')',
				'class'		=> 'eventContextMenu showInCalendarDay',
				'position'	=> 10
			),
			'week'	=> array(
				'key'		=> 'week',
				'label'		=> 'calendar.event.contextmenu.showEventInCalendar.week',
				'jsAction'	=> 'Todoyu.Ext.calendar.Event.goToEventInCalendar(#ID#, #DATE#, \'week\')',
				'class'		=> 'eventContextMenu showInCalendarWeek',
				'position'	=> 20
			),
			'month'	=> array(
				'key'		=> 'month',
				'label'		=> 'calendar.event.contextmenu.showEventInCalendar.month',
				'jsAction'	=> 'Todoyu.Ext.calendar.Event.goToEventInCalendar(#ID#, #DATE#, \'month\')',
				'class'		=> 'eventContextMenu showInCalendarMonth',
				'position'	=> 30
			)
		)
	)
);

?>