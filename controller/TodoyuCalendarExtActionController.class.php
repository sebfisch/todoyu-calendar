<?php

class TodoyuCalendarExtActionController extends TodoyuActionController {
	
	public function defaultAction(array $params) {
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
		$panelWidgets	= TodoyuCalendarRenderer::renderPanelWidgets();
		
			// Set calendar as active page
		TodoyuPage::set('calendar', $calendar);
		TodoyuPage::set('panelWidgets', $panelWidgets);
		
		
			// Add extension assets
		TodoyuPage::addExtAssets('calendar');
		
			// Get current settings
		$currentDate= TodoyuCalendarPreferences::getCalendarDate(AREA);
		$fullHeight	= TodoyuCalendarPreferences::getFullDayView() ? 'true' : 'false';
		
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.init.bind(Todoyu.Ext.calendar, ' . $fullHeight . ')');
		
			// Display calendar
		return TodoyuPage::render();
	}	
	
}

?>