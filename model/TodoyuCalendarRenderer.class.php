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
 * Calendar Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarRenderer {

	/**
	 * Extension key
	 *
	 * @var	String
	 */
	const EXTKEY = 'calendar';



	/**
	 * Render the whole calendar (header, tabs and the actual calendar grid)
	 *
	 * @param	String	$activeTab		Displayed tab
	 * @param	Array	$params			Request parameters sub functions
	 * @return	String	Code of the calendar
	 */
	public static function render($activeTab = '', array $params = array()) {
		$timestamp	= TodoyuCalendarPanelWidgetCalendar::getDate();

			// Get tab from preferences if not set
		if( empty($activeTab) ) {
			$activeTab	= TodoyuCalendarPreferences::getActiveTab();
		}

		$tmpl	= 'ext/calendar/view/main.tmpl';
		$data	= array(
			'active'		=> $activeTab,
			'content'		=> self::renderContent($timestamp, $activeTab, $params),
			'showCalendar'	=> in_array($activeTab, array('day', 'week', 'month'))
		);

			// If event view is selected, set date and add it to data array
		if( $activeTab === 'view' ) {
			$event	= TodoyuCalendarEventManager::getEvent($params['event']);
			TodoyuCalendarPanelWidgetCalendar::saveDate($event->getStartDate());
			$data['date']	= $event->getStartDate();
		}

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render calendar area content: calendar of day/week/month, view/edit event
	 *
	 * @param	Integer		$timestamp
	 * @param	String		$activeTab
	 * @param	Array		$params
	 * @return	String
	 */
	public static function renderContent($timestamp, $activeTab, array $params = array()) {
		$timestamp	= intval($timestamp);

		switch( $activeTab ) {
				// Calendar views
			case 'day':
				return TodoyuCalendarCalendarRenderer::renderCalendarDay($timestamp);
				break;
			case 'week':
				return TodoyuCalendarCalendarRenderer::renderCalendarWeek($timestamp);
				break;
			case 'month':
				return TodoyuCalendarCalendarRenderer::renderCalendarMonth($timestamp);
				break;

				// Event view/edit
			case 'view':
				$idEvent	= intval($params['event']);
				return TodoyuCalendarEventRenderer::renderEventView($idEvent);
				break;
			case 'edit':
				$idEvent	= intval($params['event']);
				return TodoyuCalendarEventEditRenderer::renderEventForm($idEvent);
				break;

			default:
				return 'Invalid type';
		}
	}



	/**
	 * Render calendar panel widgets
	 *
	 * @return	String	HTML
	 */
	public static function renderPanelWidgets() {
		return TodoyuPanelWidgetRenderer::renderPanelWidgets(self::EXTKEY);
	}



	/**
	 * Renders the calendar tabs (day, week, month)
	 *
	 * @param	String	$activeTab
	 * @return	String	HTML
	 */
	public static function renderTabs($activeTab = '') {
		if( empty($activeTab) ) {
			$activeTab = TodoyuCalendarPreferences::getActiveTab();
		}

		$name		= 'calendar';
		$tabs		= TodoyuCalendarManager::getCalendarTabsConfig();
		$jsHandler	= 'Todoyu.Ext.calendar.Tabs.onSelect.bind(Todoyu.Ext.calendar.Tabs)';

		if( $activeTab === 'view' ) {
			$tabs[] = array(
				'id'	=> 'view',
				'label'	=> 'Details'
			);
		}

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $activeTab);
	}

}

?>