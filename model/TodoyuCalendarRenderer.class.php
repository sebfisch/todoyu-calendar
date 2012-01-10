<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
	const EXTKEY	= 'calendar';



	/**
	 * Render the whole calendar (header, tabs and the actual calendar grid)
	 *
	 * @param	String	$tab		Active tab
	 * @param	Array	$params		Request parameters sub functions
	 * @return	String	Code of the calendar
	 */
	public static function renderContent($tab, array $params = array()) {
		$date	= TodoyuCalendarPanelWidgetCalendar::getDate();

		$tmpl	= 'ext/calendar/view/main.tmpl';
		$data	= array(
			'tab'			=> $tab,
			'content'		=> self::renderCalendarBody($tab, $date, $params),
			'showCalendar'	=> in_array($tab, array('day', 'week', 'month'))
		);

			// If event view is selected, set date and add it to data array
		if( $tab === 'view' ) {
			$idEvent= intval($params['event']);
			$event	= TodoyuCalendarEventStaticManager::getEvent($idEvent);
			TodoyuCalendarPanelWidgetCalendar::saveDate($event->getDateStart());
			$data['date']	= $event->getDateStart();
		}

		return Todoyu::render($tmpl, $data);
	}



	/**
	 * Render calendar area content: calendar of day/week/month, view/edit event
	 *
	 * @param	String		$tab
	 * @param	Integer		$date
	 * @param	Array		$params
	 * @return	String
	 */
	public static function renderCalendarBody($tab, $date = 0, array $params = array()) {
		$date		= TodoyuTime::time($date);
		$idEvent	= intval($params['event']);

		if( $idEvent === 0 ) {
			$eventFilters	= TodoyuCalendarManager::getAllEventFilters();
		} else {
			$eventFilters	= array();
		}

		switch( $tab ) {
				// Calendar views
			case 'day':
				return self::renderCalendarBodyDay($date, $eventFilters);
				break;
			case 'week':
				return self::renderCalendarBodyWeek($date, $eventFilters);
				break;
			case 'month':
				return self::renderCalendarBodyMonth($date, $eventFilters);
				break;

				// Event view/edit
			case 'view':
				return self::renderCalendarBodyView($idEvent);
				break;
			case 'edit':
				return self::renderCalendarBodyEdit($idEvent);
				break;

			default:
				return 'Invalid type';
		}
	}



	/**
	 * Render calendar view for day view
	 *
	 * @param	Integer		$date
	 * @param	Array		$filters
	 * @return	String
	 */
	public static function renderCalendarBodyDay($date, array $filters) {
		$date	= intval($date);
		$view	= new TodoyuCalendarViewDay($date, $filters);

		return $view->render();
	}



	/**
	 * Render calendar view for week view
	 *
	 * @param	Integer		$date
	 * @param	Array		$filters
	 * @return	String
	 */
	public static function renderCalendarBodyWeek($date, array $filters) {
		$date	= intval($date);
		$view	= new TodoyuCalendarViewWeek($date, $filters);

		return $view->render();
	}



	/**
	 * Render calendar view for month view
	 *
	 * @param	Integer		$date
	 * @param	Array		$filters
	 * @return	String
	 */
	public static function renderCalendarBodyMonth($date, array $filters) {
		$date	= intval($date);
		$view	= new TodoyuCalendarViewMonth($date, $filters);

		return $view->render();
	}



	/**
	 * Render calendar body for event detail view
	 *
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function renderCalendarBodyView($idEvent) {
		$idEvent	= intval($idEvent);

		return TodoyuCalendarEventRenderer::renderEventView($idEvent);
	}



	/**
	 * Render calendar body for event edit
	 *
	 * @param	Integer		$idEvent
	 * @return	String
	 */
	public static function renderCalendarBodyEdit($idEvent) {
		$idEvent	= intval($idEvent);

		return TodoyuCalendarEventEditRenderer::renderEventForm($idEvent);
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
			$activeTab	= TodoyuCalendarPreferences::getActiveTab();
		}

		$name		= 'calendar';
		$tabs		= TodoyuCalendarManager::getCalendarTabsConfig();
		$jsHandler	= 'Todoyu.Ext.calendar.Tabs.onSelect.bind(Todoyu.Ext.calendar.Tabs)';

		if( $activeTab === 'view' ) {
			$tabs[]	= array(
				'id'	=> 'view',
				'label'	=> 'Details'
			);
		}

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $activeTab);
	}

}

?>