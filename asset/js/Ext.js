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
 * @module	Calendar
 */

/**
 * Main calendar object
 *
 * @class		Calendar
 * @namespace	Todoyu.Ext
 */
Todoyu.Ext.calendar = {

	/**
	 * Instantiate panel widgets
	 *
	 * @property	PanelWidget
	 * @type		Object
	 */
	PanelWidget: {},

	/**
	 * Headlet container
	 *
	 * @property	Headlet
	 * @type		Object
	 */
	Headlet: {},

	/**
	 * List of observed elements (to be easy able to stop observing)
	 *
	 * @property	createEventObserverElements
	 * @type		Array
	 */
	createEventObserverElements:			[],

	/**
	 * @property	showEventQuickinfoObservedElements
	 * @type		Array
	 */
	showEventQuickinfoObservedElements:		[],

	/**
	 * @property	showHolidayQuickinfoObservedElements
	 * @type		Array
	 */
	showHolidayQuickinfoObservedElements:	[],

	/**
	 * @property	updateEventObserverElements
	 * @type		Array
	 */
	updateEventObserverElements:			[],

	/**
	 * Extend sCal options (weekdaystart = monday, yearprev = symbol to go backwards, yearnext = symbol to go forwards
	 *
	 * @property	calOptions
	 * @type		Object
	 */
	calOptions: {
		weekdaystart:	1,
		yearprev:		'&laquo;&laquo;',
		yearnext:		'&raquo;&raquo;'
	},



	/**
	 * Init calendar
	 *
	 * @method	init
	 */
	init: function() {
		this.addHooks();

			// Only initialize panelwidgets and body in calendar view
		if( Todoyu.getArea() === 'calendar' ) {
			this.addPanelWidgetObservers();
			this.CalendarBody.init();
		}
	},





	/**
	 * Add various JS hooks
	 *
	 * @method	addHooks
	 */
	addHooks: function() {
			// Add event save hook
		Todoyu.Hook.add('calendar.ext.quickevent.saved', this.refresh.bind(this));

			// Add event edit hook for event type
		Todoyu.Hook.add('calendar.event.editType', this.Event.Edit.checkHideField.bind(this.Event.Edit));
		Todoyu.Hook.add('headlet.quickcreate.event.popupOpened', this.QuickCreateEvent.onPopupOpened());

			// Add event save hook
		Todoyu.Hook.add('calendar.event.saved', this.onEventSaved.bind(this));
	},



	/**
	 * Install general calendar observer
	 *
	 * @method	addPanelWidgetObservers
	 */
	addPanelWidgetObservers: function() {
		Todoyu.PanelWidget.observe('calendar', this.onDateChanged.bind(this));
		Todoyu.PanelWidget.observe('staffselector', this.onStaffSelectionChanges.bind(this));
		Todoyu.PanelWidget.observe('eventtypeselector', this.onEventTypeSelectionChanges.bind(this));
	},



	/**
	 * Install all calendar quickinfos
	 *
	 * @method	installQuickinfos
	 */
	installQuickinfos: function() {
		this.QuickInfoBirthday.install();
		this.QuickInfoEvent.install();
		this.QuickInfoHoliday.install();
	},



	/**
	 * Get selected date timestamp
	 *
	 * @method	getDate
	 * @return	{Number}	JavaScript timestamp
	 */
	getDate: function() {
		return this.PanelWidget.Calendar.getDate();
	},



	/**
	 * Set selected date timestamp
	 *
	 * @method	setDate
	 * @param	{Number}	date	JavaScript timestamp
	 */
	setDate: function(date) {
		this.PanelWidget.Calendar.setDate(date, true);
	},



	/**
	 * Get calendar time (timestamp)
	 *
	 * @method	getTime
	 * @return	{Number}
	 */
	getTime: function() {
		return this.PanelWidget.Calendar.getTime();
	},



	/**
	 * Set calendar time (timestamp
	 *
	 * @method	setTime
	 * @param	{Number}		time
	 */
	setTime: function(time, noExternalUpdate) {
		this.PanelWidget.Calendar.setTime(time, noExternalUpdate);
	},



	/**
	 * Get day string of selected date
	 *
	 * @method	getDateString
	 * @return	{String}
	 */
	getDateString: function() {
		return Todoyu.Time.getDateString(this.getTime())
	},



	/**
	 * Get day start timestamp of (selected day in) calendar
	 *
	 * @method	getDayStart
	 * @return	{Number}
	 */
	getDayStart: function() {
		return Todoyu.Time.getDayStart(this.getTime());
	},



	/**
	 * Get starting day of week in calendar that contains the currently selected day
	 *
	 * @method	getWeekStart
	 * @return	{Number}
	 */
	getWeekStart: function() {
		return Todoyu.Time.getWeekStart(this.getTime());
	},



	/**
	 * Get active tab in calendar
	 *
	 * @method	getActiveTab
	 * @return	{String}
	 */
	getActiveTab: function() {
		return this.Tabs.getActive();
	},



	/**
	 * Set active tab in calendar (only set data, no update)
	 *
	 * @method	setActiveTab
	 * @param	{Object}	tab
	 */
	setActiveTab: function(tab) {
		this.Tabs.setActive(tab);
	},



	/**
	 * Event handler: onDateChanged
	 *
	 * @method	onDateChanged
	 * @param	{String}	widgetName
	 * @param	{Object}	update
	 */
	onDateChanged: function(widgetName, update) {
		this.show(null, update.date);
	},



	/**
	 * Handler for staff selection changes
	 *
	 * @method	onStaffSelectionChanges
	 * @param	{String}		widgetName
	 * @param	{Array}		persons
	 */
	onStaffSelectionChanges: function(widgetName, persons) {
		this.refresh();
	},



	/**
	 * Handler for eventType selection changes
	 *
	 * @method	onEventTypeSelectionChanges
	 * @param	{String}	widgetName
	 * @param	{Array}		eventTypes
	 */
	onEventTypeSelectionChanges: function(widgetName, eventTypes) {
		this.refresh();
	},



	/**
	 * Handler for hook 'onEventSaved'
	 *
	 * @method	onEventSaved
	 * @param	{Number}		idEvent
	 */
	onEventSaved: function(idEvent) {
		if( Todoyu.getArea() === 'calendar' ) {
			this.refresh();
		}
	},



	/**
	 * Event click handler
	 *
	 * @method	onEventClick
	 * @param	{Event}		event
	 */
	onEventClick: function(event) {
		var idEvent = event.findElement('div').id.split('-').last();

		this.Event.updateEvent(idEvent);
	},



	/**
	 * Callback for calendar body update
	 *
	 * @method	onCalendarBodyUpdated
	 * @param	{Ajax.Response}			response
	 */
	onCalendarBodyUpdated: function(response) {
		this.CalendarBody.init();
	},



	/**
	 * Update the calendar body area
	 *
	 * @method	updateCalendarBody
	 * @param	{String}		url
	 * @param	{Hash}		options
	 */
	updateCalendarBody: function(url, options) {
		Todoyu.Ui.update('calendar-body', url, options);
	},



	/**
	 * Refresh calendar with current settings
	 *
	 * @method	refresh
	 */
	refresh: function() {
		this.show();
	},



	/**
	 * Update calendar body with new config
	 *
	 * @method	show
	 * @param	{String}		tab
	 * @param	{Number}		date
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
			parameters: {
				action:	'update',
				tab:		this.getActiveTab(),
				date:		this.getDateString()
			},
			onComplete: this.onCalendarBodyUpdated.bind(this)
		};
			// Update view
		this.updateCalendarBody(url, options);
	},



	/**
	 * Show day by date
	 *
	 * @method	showDay
	 * @param	{String}	date		Format: Y-m-d (2010-08-15)
	 */
	showDay: function(date) {
		var parts	= date.split('-');
		var time 	= (new Date(parts[0], parts[1]-1, parts[2], 0, 0, 0)).getTime();
		this.show('day', time);
	},


	/**
	 * Show week by date
	 *
	 * @method	showWeek
	* @param	{String}	date		Format: Y-m-d (2010-08-15)
	 */
	showWeek: function(date) {
		var parts	= date.split('-');
		var time 	= (new Date(parts[0], parts[1]-1, parts[2], 0, 0, 0)).getTime();
		this.show('week', time);
	},



	/**
	 * Set calendar title
	 *
	 * @method	setTitle
	 * @param	{String}		title
	 */
	setTitle: function(title) {
		this.Navi.setTitle(title);
	},



	/**
	 * Add event with popup
	 *
	 * @method	addEvent
	 * @param	{Number}		time
	 */
	addEvent: function(time) {
		this.Event.Edit.open(0, time);
	},



	/**
	 * Save preferences
	 *
	 * @method	savePref
	 * @param	{String}	action
	 * @param	{Mixed}		value
	 * @param	{Number}	idItem
	 * @param	{String}	onComplete
	 */
	savePref: function(action, value, idItem, onComplete) {
		Todoyu.Pref.save('calendar', action, value, idItem, onComplete);
	},



	/**
	 * Hide calendar container
	 *
	 * @method	hideCalendar
	 */
	hideCalendar: function() {
		$('calendar').hide();
	},



	/**
	 * Show calendar container. Available containers: calendar, view, edit
	 *
	 * @method	showCalendar
	 */
	showCalendar: function() {
		$('calendar').show();
	}

};