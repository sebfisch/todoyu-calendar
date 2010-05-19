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
		'label'		=> 'event.contextmenu.addEvent',
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
		'label'		=> 'event.contextmenu.showEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.show(#ID#)',
		'class'		=> 'eventContextMenu eventShow',
		'position'	=> 10
	),
	'edit'	=> array(
		'key'		=> 'edit',
		'label'		=> 'event.contextmenu.editEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.edit(#ID#)',
		'class'		=> 'eventContextMenu eventEdit',
		'position'	=> 20
	),
	'remove'	=> array(
		'key'		=> 'delete',
		'label'		=> 'event.contextmenu.deleteEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.remove(#ID#)',
		'class'		=> 'eventContextMenu eventRemove',
		'position'	=> 30
	),
	'add'	=> array(
		'key'		=> 'add',
		'label'		=> 'event.contextmenu.addEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.addEventOnSameTime(#ID#)',
		'class'		=> 'eventContextMenu eventAdd',
		'position'	=> 40
	)
);



/**
 * Context menu for events in portal area
 */
Todoyu::$CONFIG['EXT']['calendar']['ContextMenu']['EventPortal'] = array(
	'show'	=> array(
		'key'		=> 'show',
		'label'		=> 'event.contextmenu.showEventInCalendar',
		'jsAction'	=> 'void(0)',
		'class'		=> 'eventContextMenu eventShow',
		'position'	=> 10,
		'submenu'	=> array(
			'day'	=> array(
				'key'		=> 'day',
				'label'		=> 'event.contextmenu.showEventInCalendar.day',
				'jsAction'	=> 'Todoyu.Ext.calendar.Event.goToEventInCalendar(#ID#, #DATE#, \'day\')',
				'class'		=> 'eventContextMenu showInCalendarDay',
				'position'	=> 10
			),
			'week'	=> array(
				'key'		=> 'week',
				'label'		=> 'event.contextmenu.showEventInCalendar.week',
				'jsAction'	=> 'Todoyu.Ext.calendar.Event.goToEventInCalendar(#ID#, #DATE#, \'week\')',
				'class'		=> 'eventContextMenu showInCalendarWeek',
				'position'	=> 20
			),
			'month'	=> array(
				'key'		=> 'month',
				'label'		=> 'event.contextmenu.showEventInCalendar.month',
				'jsAction'	=> 'Todoyu.Ext.calendar.Event.goToEventInCalendar(#ID#, #DATE#, \'month\')',
				'class'		=> 'eventContextMenu showInCalendarMonth',
				'position'	=> 30
			)
		)
	)
);

?>