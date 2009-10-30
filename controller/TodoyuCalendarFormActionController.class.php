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
 * Form action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuCalendarFormActionController {

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */	
	public static function defaultAction(array $params) {
			// Activate FE tab
		TodoyuFrontend::setActiveTab('planning');
		
			// Activate tab submenu entry
		TodoyuFrontend::setActiveSubmenuTab('planning', 'calendar');
		
			// Initialise template
		TodoyuPage::init('ext/calendar/view/ext.tmpl');
		
			// Set title
		TodoyuPage::setTitle('LLL:calendar.page.title');
		
			// Render the calendar
		$calendar		= TodoyuCalendarRenderer::render();
		
			// Render calendar area's panel widgets
		$panelWidgets	= TodoyuCalendarRenderer::renderPanelWidgets();
		
			// Set calendar as active page
		TodoyuPage::set('calendar', $calendar);
		TodoyuPage::set('panelWidgets', $panelWidgets);
		
			// Add extension assets
		TodoyuPage::addExtAssets('calendar');
		
		
		$currentDate = TodoyuCalendarPreferences::getCalendarDate(AREA);
		$fullHeight	= TodoyuCalendarPreferences::getFullDayView() ? 'true' : 'false';
		
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.init.bind(Todoyu.Ext.calendar, ' . $fullHeight . ')');
		
			// Display calendar
		return TodoyuPage::render();
	}	
	
}

?>