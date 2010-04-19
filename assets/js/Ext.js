/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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

Todoyu.Ext.calendar = {

	/**
	 * Instantiate panel widgets
	 */
	PanelWidget: {},

	/**
	 * Headlet container
	 */
	Headlet: {},

	/**
	 * List of observed elements (to be easy able to stop observing)
	 */
	createEventObserverElements:			[],

	showEventQuickinfoObservedElements:		[],

	showHolidayQuickinfoObservedElements:	[],

	updateEventObserverElements:			[],



	/**
	 * Extend scal options (weekdaystart = monday, yearprev = symbol to go backwards, yearnext = symbol to go forwards
	 */
	calOptions: {
		weekdaystart:	1,
		yearprev:		'&laquo;&laquo;',
		yearnext:		'&raquo;&raquo;'
	},


	init: function() {

	},



	/**
	 * Initialization
	 */
	initCalendar: function(fullHeight) {
		this.CalendarBody.init(fullHeight);

		this.installGeneralObservers();
		this.installBodyObservers();

		this.addHooks();
	},



	/**
	 * Add various JS hooks
	 */
	addHooks: function() {
			// Add event save hooks
		Todoyu.Hook.add('onEventSaved', this.refresh.bind(this));

			// Add event edit hooks for event type
		Todoyu.Hook.add('eventtype', this.EventEdit.checkHideField.bind(this.EventEdit));
	},



	/**
	 * Install general calendar observer
	 */
	installGeneralObservers: function() {
		Todoyu.PanelWidget.observe('calendar', this.onDateChanged.bind(this));
		Todoyu.PanelWidget.observe('staffselector', this.onStaffSelectionChanges.bind(this));
		Todoyu.PanelWidget.observe('eventtypeselector', this.onEventTypeSelectionChanges.bind(this));
	},



	/**
	 * Install calendar body observers
	 */
	installBodyObservers: function() {
		this.QuickInfoBirthday.init();
		this.QuickInfoEvent.init();
		this.QuickInfoHoliday.init();
		this.installEventObservers();
		this.CalendarBody.installObserversCreateEvent();
	},



	/**
	 * Uninstall calendar body observers
	 */
	uninstallBodyObservers: function() {
		this.uninstallEventObservers();
	},



	/**
	 * Install observers to event entries in calendar to show / hide quickinfo to when un / hovering them
	 *
	 * @param	DOM-Element	el
	 */
	installEventObservers: function(el) {
		this.Event.installObservers();
	},



	/**
	 * Uninstall event observers
	 */
	uninstallEventObservers: function() {

	},


	/**
	 * Get selected date timestamp
	 *
	 * @return	{Integer}	timestamp	Unix-Timestamp
	 */
	getDate: function() {
		return this.PanelWidget.Calendar.getDate();
	},



	/**
	 * Set selected date timestamp
	 *
	 * @param	{Integer}	timestamp	Unix-Timestamp
	 */
	setDate: function(date) {
		this.PanelWidget.Calendar.setDate(date, true);
	},



	/**
	 * Get calendar time (timestamp)
	 */
	getTime: function() {
		return this.PanelWidget.Calendar.getTime();
	},



	/**
	 * Set calendar time (timestamp
	 *
	 * @param	{Integer}		time
	 */
	setTime: function(time, noExternalUpdate) {
		this.PanelWidget.Calendar.setTime(time, noExternalUpdate);
	},
	
	
	
	/**
	 * Get day string of selected date
	 */
	getDayString: function() {
		return Todoyu.Time.getDateString(this.getTime())
	},



	/**
	 * Get day start of (selected day in) calendar
	 *
	 * @return	{Integer}
	 */
	getDayStart: function() {
		return Todoyu.Time.getDayStart(this.getTime());
	},



	/**
	 * Get starting day of week in calendar that contains the currently selected day
	 *
	 * @return	{Integer}
	 */
	getWeekStart: function() {
		return Todoyu.Time.getWeekStart(this.getTime());
	},



	/**
	 * Get active tab in calendar
	 *
	 * @return	{String}
	 */
	getActiveTab: function() {
		return this.Tabs.getActive();
	},



	/**
	 * Set active tab in calenar (only set data, no update)
	 *
	 * @param	{Object}	tab
	 */
	setActiveTab: function(tab) {
		this.Tabs.setActive(tab);
	},



	/**
	 * Hook before calendar body update
	 */
	beforeUpdate: function() {
		this.uninstallBodyObservers();

	},



	/**
	 * Hook after calendar body update
	 */
	afterUpdate: function() {
		this.installBodyObservers();
		this.CalendarBody.reInit();
	},



	/**
	 * Event handler: onDateChanged
	 *
	 * @param	{String}	widgetName
	 * @param	{Object}	update
	 */
	onDateChanged: function(widgetName, update) {
		this.show(null, update.date);
	},



	/**
	 * Handler for staff selection changes
	 *
	 * @param	{String}		widgetName
	 * @param	{Array}		persons
	 */
	onStaffSelectionChanges: function(widgetName, persons) {
		this.refresh();
	},



	/**
	 * Handler for eventType selection changes
	 *
	 * @param	{String}		widgetName
	 * @param	{Array}		eventTypes
	 */
	onEventTypeSelectionChanges: function(widgetName, eventTypes) {
		this.refresh();
	},



	/**
	 * Event click handler
	 *
	 * @param	{Event}		event
	 */
	onEventClick: function(event) {
		var idEvent = event.findElement('div').id.split('-').last();

		this.Event.updateEvent(idEvent);
	},



	/**
	 * Callback for calendar body update
	 *
	 * @param	{Response}		response
	 */
	onCalendarBodyUpdated: function(response) {
		this.afterUpdate();
	},



	/**
	 * Update the calendar body area
	 *
	 * @param	{String}		url
	 * @param	{Hash}		options
	 */
	updateCalendarBody: function(url, options) {
		this.beforeUpdate();

		Todoyu.Ui.update('calendar-body', url, options);
	},



	/**
	 * Refresh calendar with current settings
	 */
	refresh: function() {
		this.show(null, null);
	},



	/**
	 * Update calendar body with new config
	 *
	 * @param	{String}		tab
	 * @param	{Integer}		date
	 */
	show: function(tab, date) {
			// Close special tabs (edit,view)
		this.Tabs.closeSpecialTabs();
			// Make sure calendar is visible
		this.showCalendar();
			// Hide quickinfo
		Todoyu.QuickInfo.hide();

			// Get active tab and set it
		if( ! Object.isString(tab) ) {
			tab = this.getActiveTab();
		}
		this.setActiveTab(tab);

			// Set new date if given as parameter
		if( Object.isNumber(date) ) {
			this.setDate(date);
		}		

		var url 	= Todoyu.getUrl('calendar', 'calendar');
		var options	= {
			'parameters': {
				'action':	'update',
				'tab':		this.getActiveTab(),
				'date':		this.getDayString()
			},
			'onComplete': this.onCalendarBodyUpdated.bind(this)
		};
			// Update view
		this.updateCalendarBody(url, options);
	},



	/**
	 * Set calendar title
	 *
	 * @param	{String}		title
	 */
	setTitle: function(title) {
		this.Navi.setTitle(title);
	},



	/**
	 * Add event with popup
	 *
	 * @param	{Integer}		time
	 */
	addEvent: function(time) {
		this.EventEdit.open(0, time);
	},



	/**
	 * Save preferences
	 *
	 * @param	{String}	name	Name of the preference
	 * @param	{Mixed}	value	Value to be saved
	 */
	savePref: function(action, value, idItem, onComplete) {
		Todoyu.Pref.save('calendar', action, value, idItem, onComplete);
	},



	/**
	 * Hide calendar container
	 */
	hideCalendar: function() {
		$('calendar').hide();
	},



	/**
	 * Show calendar container. Available containers: calendar, view, edit
	 */
	showCalendar: function() {
		$('calendar').show();
	}

};