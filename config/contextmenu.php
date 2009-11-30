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
 * Context menu configuration for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */


/**
 * Contextmenu for calendar area
 */
$CONFIG['EXT']['calendar']['ContextMenu']['Area'] = array(
	'header'	=> array(
		'key'		=> 'header',
		'label'		=> 'Manage events',
		'jsAction'	=> 'void(0)',
		'class'		=> 'contextmenuHeader',
		'position'	=> 0
	),
	'add'	=> array(
		'key'		=> 'add',
		'label'		=> 'Add event',
		'jsAction'	=> 'Todoyu.Ext.calendar.addEvent(#ID#)',
		'class'		=> 'eventContextMenu eventAdd',
		'position'	=> 10
	)
);


/**
 * Contextmenu for event
 */
$CONFIG['EXT']['calendar']['ContextMenu']['Event'] = array(
	'header'	=> array(
		'key'		=> 'header',
		'label'		=> 'TodoyuCalendarViewHelper::getContextMenuHeader',
		'jsAction'	=> 'void(0)',
		'class'		=> 'contextmenuHeader',
		'position'	=> 0
	),
	'show'	=> array(
		'key'		=> 'show',
		'label'		=> 'LLL:event.contextmenu.showEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.show(#ID#)',
		'class'		=> 'eventContextMenu eventShow',
		'position'	=> 10
	),
	'edit'	=> array(
		'key'		=> 'edit',
		'label'		=> 'LLL:event.contextmenu.editEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.edit(#ID#)',
		'class'		=> 'eventContextMenu eventEdit',
		'position'	=> 20
	),
	'remove'	=> array(
		'key'		=> 'delete',
		'label'		=> 'LLL:event.contextmenu.deleteEvent',
		'jsAction'	=> 'Todoyu.Ext.calendar.Event.remove(#ID#)',
		'class'		=> 'eventContextMenu eventRemove',
		'position'	=> 30
	)
);


?>