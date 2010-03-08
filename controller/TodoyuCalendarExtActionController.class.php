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
 * Ext action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarExtActionController extends TodoyuActionController {

	/**
	 * Render default view of calendar when full page is reloaded
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function defaultAction(array $params) {
		restrict('calendar', 'general:area');

			// Activate FE tab
		TodoyuFrontend::setActiveTab('planning');
				// Activate tab submenu entry
		TodoyuFrontend::setActiveSubmenuTab('planning', 'calendar');

			// Initialise template
		TodoyuPage::init('ext/calendar/view/ext.tmpl');

			// Set title
		TodoyuPage::setTitle('LLL:calendar.page.title');

			// Get type from parameter or preferences
		$activeTab	= $params['tab'];
		if( empty($activeTab) ) {
			$activeTab	= TodoyuCalendarPreferences::getActiveTab();
		}

			// Set date in preferences when given as parameter
		if( is_numeric($params['date']) ) {
			TodoyuPanelWidgetCalendar::saveDate($params['date']);
		}

			// Render the calendar
		$panelWidgets	= TodoyuCalendarRenderer::renderPanelWidgets();
		$calendarTabs	= TodoyuCalendarRenderer::renderTabs($activeTab);
		$calendar		= TodoyuCalendarRenderer::render($activeTab, $params);

			// Set calendar as active page
		TodoyuPage::set('calendarTabs', $calendarTabs);
		TodoyuPage::set('calendar', $calendar);
		TodoyuPage::set('panelWidgets', $panelWidgets);

			// Add extension assets
		TodoyuPage::addExtAssets('calendar');

			// Generate colors css and sprite
		TodoyuColors::generate();

			// Get current settings
		$currentDate= TodoyuCalendarPreferences::getCalendarDate(AREA);
		$fullHeight	= TodoyuCalendarPreferences::getFullDayView() ? 'true' : 'false';

		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.init.bind(Todoyu.Ext.calendar, ' . $fullHeight . ')', 100);

			// Display calendar
		return TodoyuPage::render();
	}

}

?>