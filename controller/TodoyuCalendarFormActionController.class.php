<?php

class TodoyuCalendarFormActionController {
	
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