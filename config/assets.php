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
 * Assets (JS, CSS, SWF, etc.) requirements for calendar extension
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

if( ! defined('TODOYU') ) die('NO ACCESS');


$CONFIG['EXT']['calendar']['assets'] = array(

	// Default assets: loaded all over the installation always
	'default' => array(
		'js' => array(
			array(
				'file' => 'ext/calendar/assets/js/Ext.js',
				'position' => 100
			),
			array(
				'file' => 'ext/calendar/assets/js/Event.js',
				'position' => 105
			),
		),
		'css' => array(
			array(
				'file'		=> 'ext/calendar/assets/css/contextmenu.css',
				'position'	=> 80
			),
			array(
				'file'		=> 'ext/calendar/assets/css/metamenu.css',
				'position'	=> 90
			),
		)
	),


		// Public assets: basis assets for this extension
	'public' => array(
		'js' => array(
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
				'file' => 'ext/calendar/assets/js/Quickinfo.js',
				'position' => 106
			),
			array(
				'file' => 'ext/calendar/assets/js/Edit.js',
				'position' => 107
			),
			array(
				'file' => 'ext/calendar/assets/js/EventView.js',
				'position' => 107
			),
			array(
				'file' => 'ext/calendar/assets/js/CalendarBody.js',
				'position' => 108
			),
			array(
				'file' => 'ext/calendar/assets/js/ContextMenuCalendarBody.js',
				'position' => 109
			),
			array(
				'file' => 'ext/calendar/assets/js/ContextMenuEvent.js',
				'position' => 110
			)
		),



		'css' => array(
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
				'file'		=> 'ext/calendar/assets/css/quickinfo.css',
				'position'	=> 107
			)
		)
	),



		// Assets of panel widgets
		// Calendar
	'panelwidget-calendar' => array(
		'js' => array(
			array(
				'file' => 'lib/js/scal/javascripts/scal.js',
				'position' => 30
			),
			array(
				'file' => 'ext/calendar/assets/js/PanelWidgetCalendar.js',
				'position' => 110
			)
		),

		'css' => array(
			array(
				'file' => 'ext/calendar/assets/css/scal.css',
				'position' => 30
			),
			array(
				'file' => 'ext/calendar/assets/css/panelwidget-calendar.css',
				'position' => 110
			)
		)
	),

		// Quickevent wizard
	'panelwidget-quickevent' => array(
		'js' => array(
			array(
				'file' => 'ext/calendar/assets/js/PanelWidgetQuickEvent.js',
				'position' => 120
			)
		),
		'css' => array(
			array(
				'file' => 'ext/calendar/assets/css/panelwidget-quickevent.css',
				'position' => 120
			)
		)
	),


		// Admin widget (components selection menu)
	'TodoyuPanelWidgetCalendarAdmin' => array(
		'js' => array(),

		'css' => array(
			array(
				'file' => 'ext/calendar/assets/css/panelwidget-calendaradmin.css',
				'position' => 130
			)
		)
	),


		// Event type selector
	'panelwidget-eventtypeselector' => array(
		'js' => array(
			array(
				'file' => 'ext/calendar/assets/js/PanelWidgetEventTypeSelector.js',
				'position' => 140
			)
		),

		'css' => array(
			array(
				'file' => 'ext/calendar/assets/css/panelwidget-eventtpyeselector.css',
				'position' => 140
			)
		)
	),


		// Holidayset selector
	'PanelWidgetHolidaysetSelector' => array(
		'js' => array(
			array(
				'file' => 'ext/calendar/assets/js/PanelWidgetHolidaySetSelector.js',
				'position' => 150
			)
		),

		'css' => array(
			array(
				'file' => 'ext/calendar/assets/css/panelwidget-holidaysetselector.css',
				'position' => 150
			)
		)
	)

);



$CONFIG['EXT']['portal']['assets']['public']['css'][] = array(
	'file'		=> 'ext/calendar/assets/css/ext.css',
	'position'	=> 100
);

?>