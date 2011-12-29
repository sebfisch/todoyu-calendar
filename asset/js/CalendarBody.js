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
 * Calendar body
 *
 * @class		CalendarBody
 * @namespace	Todoyu.Ext.calendar
 */
Todoyu.Ext.calendar.CalendarBody = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:			Todoyu.Ext.calendar,

	/**
	 * @property	idArea
	 * @type		String
	 */
	idArea:			'calendararea',

	/**
	 * @property	calendarBody
	 * @type		Element
	 */
	calendarBody:	null,



	/**
	 * Init calendar body
	 *
	 * @method	init
	 */
	init: function() {
			// Ensure the calendarBody is there (it's missing when editing an event initially)
		if( $(this.idArea) !== null ) {
			this.calendarBody = $(this.idArea);

			this.installContextMenu();
			this.installObserversCreateEvent();

			this.ext.installQuickInfos();
			this.ext.Event.installObservers();

			if( this.ext.getActiveTab() !== 'month' ) {
				this.setFullHeight(this.isFullHeight(), false);
			}

				// Init drag and drop
			this.ext.DragDrop.init();
		}

			// Call hooked callbacks
		Todoyu.Hook.exec('calendarBody.init');
	},



	/**
	 * Install calendar body context menu
	 *
	 * @method	installContextMenu
	 */
	installContextMenu: function() {
		this.ext.ContextMenuCalendarBody.attach();
	},



	/**
	 * Toggle full day view mode
	 *
	 * @method toggleFullDayView
	 */
	toggleFullDayView: function() {
		this.setFullHeight(!this.isFullHeight(), true);
	},



	/**
	 * Save changed pref, reload calendar with toggled display of weekend (sat+sun)
	 *
	 * @method toggleWeekend
	 */
	toggleWeekend: function() {
		var url		= Todoyu.getUrl('calendar', 'preference');
		var options	= {
			parameters: {
				action:		'toggleDisplayWeekend'
			},
			onComplete: this.onWeekendToggled.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * After display of weekend has been toggled: refresh calendar week view
	 *
	 * @method	onWeekendToggled
	 */
	onWeekendToggled: function() {
		Todoyu.Ext.calendar.show('week', this.ext.getDate());
	},



	/**
	 * Get amount of currently displayed days (per week: 5/7)
	 *
	 * @method	getAmountDisplayedDays
	 * @return	{Number}
	 */
	getAmountDisplayedDays: function() {
		return $('tgTable').select('td.dayCol').size();
	},



	/**
	 * Check whether calendar body is set to full height
	 *
	 * @method	isFullHeight
	 * @return	{Boolean}
	 */
	isFullHeight: function() {
		return this.calendarBody.hasClassName('full');
	},



	/**
	 * Get calendar body height
	 *
	 * @method	getHeight
	 * @return	{Number}
	 */
	getHeight: function() {
		return this.calendarBody.getHeight();
	},



	/**
	 * Set calendar body display mode to full day height
	 *
	 * @method	setFullHeight
	 * @param	{Boolean}		fullHeight
	 * @param	{Boolean}		savePref
	 */
	setFullHeight: function(fullHeight, savePref) {
		if( fullHeight ) {
				// Switch to full hours view
			this.calendarBody.addClassName('full');
			Todoyu.Helper.setScrollTop(this.calendarBody, 0);
		} else {
				// Switch to restrained hours view
			this.calendarBody.removeClassName('full');
			this.calendarBody.scrollTop = 336;
		}

		if( savePref === true ) {
			this.saveFullDayViewPref();
		}
	},



	/**
	 * Save full day viewing mode preference
	 *
	 * @method	saveFullDayViewPref
	 */
	saveFullDayViewPref: function(){
		this.ext.savePref('fulldayview', this.isFullHeight() ? 1 : 0);
	},



	/**
	 * Get resp. timestamp to mouse coordinates inside current calendar view (day / week / month)
	 *
	 * @method	getTimeOfMouseCoordinates
	 * @param	{Number}		x
	 * @param	{Number}		y
	 * @return	{Number}
	 */
	getTimeOfMouseCoordinates: function(x, y) {
		var calendarMode= this.ext.getActiveTab();
		var timestamp;

			// Get top coordinate, if view is minimized, add invisible part to 'top'
		var top	= y - this.calendarBody.cumulativeOffset().top + (this.isFullHeight() ? 0 : 8 * 42);

			// Calculate timestamp from coordinate in current mode
		switch(calendarMode) {
			case 'day':
				timestamp	= this.ext.getDayStart() + this.getDayOffset(top, 1010);
				break;

			case 'week':
				var left	= x - this.calendarBody.cumulativeOffset().left;
				var numDays	= Math.floor((left - 40) / 89);	// 40px: hours column width, 89px: day column incl. right border
				numDays		= numDays < 0 ? 0 : numDays;
				timestamp	= (this.ext.getWeekStart() + numDays * Todoyu.Time.seconds.day) + this.getDayOffset(top, 1010);
				break;
		}

		return timestamp;
	},



	/**
	 * Get pixel-offset of day display, used to comprehend visual margins of hours in day / week mode
	 *
	 * @method	getDayOffset
	 * @param	{Number}		top
	 * @param	{Number}		height
	 * @return	{Number}
	 */
	getDayOffset: function(top, height) {
		var seconds	= (top / height) * Todoyu.Time.seconds.day;
			// Round to quarter hours, get time parts (hours, minutes, seconds)
		seconds	= Math.round(seconds / 900) * 900;

		var timeInfo	= Todoyu.Time.getTimeParts(seconds);

		return timeInfo.hours * Todoyu.Time.seconds.hour + timeInfo.minutes * Todoyu.Time.seconds.minute;
	},



	/**
	 * Install create event observer
	 *
	 * @method	installObserversCreateEvent
	 */
	installObserversCreateEvent: function() {
		var tab	= this.ext.getActiveTab();

		if( tab === 'month' ) {
			this.calendarBody.on('dblclick', 'td', this.onEventCreateMonth.bind(this));
		} else {
			this.calendarBody.on('dblclick', this.onEventCreateDayWeek.bind(this));
		}
	},



	/**
	 * Handle event creation in day or week viewing mode
	 *
	 * @method	inEventCreateDayWeek
	 * @param	{Event}	event
	 */
	onEventCreateDayWeek: function(event) {
		var time	= this.getTimeOfMouseCoordinates(event.pointerX(), event.pointerY());

		this.ext.addEvent(time);
	},



	/**
	 * Handle event creation in month viewing mode
	 * Date is in string format to ignore timezone offsets
	 * (we just want the day, don't care about the local time)
	 *
	 * @method	onEventCreateMonth
	 * @param	{Event}		event
	 * @param	{Element}	cell
	 */
	onEventCreateMonth: function(event, cell) {
			// Get timestamp of the date in local timezone (will be reconverted later into the same timestamp again)
		var time	= Todoyu.Time.date2Time(cell.id.split('-').slice(1).join('-'));

		this.ext.addEvent(time);
	}

};