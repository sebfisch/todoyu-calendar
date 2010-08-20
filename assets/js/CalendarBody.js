/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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

Todoyu.Ext.calendar.CalendarBody = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:			Todoyu.Ext.calendar,

	idArea:			'calendararea',

	calendarBody:	null,



	/**
	 * Init calendar body
	 *
	 */
	init: function() {
		this.calendarBody = $(this.idArea);

		this.installContextMenu();
		this.installObserversCreateEvent();

		this.ext.installQuickinfos();
		this.ext.Event.installObservers();

		if( this.ext.getActiveTab() !== 'month' ) {
			this.setFullHeight(this.isFullHeight(), false);
		}

			// Init drag'n'drop
		this.ext.DragDrop.init();
	},



	/**
	 * Install calendar body context menu
	 */
	installContextMenu: function() {
		this.ext.ContextMenuCalendarBody.attach();
	},



	/**
	 * Toggle full day view mode
	 */
	toggleFullDayView: function() {
		this.setFullHeight(!this.isFullHeight(), true);
	},



	/**
	 * Check whether calendar body is set to full height
	 */
	isFullHeight: function() {
		return this.calendarBody.hasClassName('full');
	},



	/**
	 * Get calendar body height
	 *
	 * @return	{Number}
	 */
	getHeight: function() {
		return this.calendarBody.getHeight();
	},



	/**
	 * Set calendar body display mode to full day height
	 *
	 * @param	{Boolean}		fullHeight
	 * @param	{Boolean}		savePref
	 */
	setFullHeight: function(fullHeight, savePref) {
		if( fullHeight ) {
			this.calendarBody.addClassName('full');
			this.calendarBody.scrollTop = 0;
		} else {
			this.calendarBody.removeClassName('full');
			this.calendarBody.scrollTop = 336;
		}

		if( savePref === true ) {
			this.saveFullDayViewPref();
		}
	},



	/**
	 * Save full day viewing mode preference
	 */
	saveFullDayViewPref: function(){
		this.ext.savePref('fulldayview', this.isFullHeight() ? 1 : 0);
	},



	/**
	 * Get resp. timestamp to mouse coordinates inside current calendar view (day / week / month)
	 *
	 * @param	{Number}		x
	 * @param	{Number}		y
	 * @return	{Number}
	 */
	getTimeOfMouseCoordinates: function(x, y) {
		var calendarMode= this.ext.getActiveTab();

			// Get top coordinate, if view is minimized, add invisible part to 'top'
		var top			= y - this.calendarBody.cumulativeOffset().top + (this.isFullHeight() ? 0 : 8 * 42);

		switch(calendarMode) {
			case 'day':
				var timestamp	= this.ext.getDayStart() + this.getDayOffset(top, 1010);
				break;

			case 'week':
				var left		= x - this.calendarBody.cumulativeOffset().left;
				var numDays		= Math.floor((left - 40) / 89);
				var timestamp	= (this.ext.getWeekStart() + numDays * Todoyu.Time.seconds.day) + this.getDayOffset(top, 1010);
				break;
		}

		return timestamp;
	},



	/**
	 * Get pixel-offset of day display, used to comprehend visual margins of hours in day / week mode
	 *
	 * @param	{Number}		top
	 * @param	{Number}		height
	 * @return	{Number}
	 */
	getDayOffset: function(top, height) {
		var seconds	= (top / height) * Todoyu.Time.seconds.day;

			// Round to quarter hours, get time parts (hours, minutes, seconds)
		seconds		= Math.round(seconds / 900) * 900;

		var timeInfo	= Todoyu.Time.getTimeParts(seconds);

		return timeInfo.hours * Todoyu.Time.seconds.hour + timeInfo.minutes * Todoyu.Time.seconds.minute;
	},



	/**
	 * Install create event observer
	 */
	installObserversCreateEvent: function() {
		var tab	= this.ext.getActiveTab();

		if( tab === 'month' ) {
			this.calendarBody.observe('dblclick', this.onEventCreateMonth.bindAsEventListener(this));
		} else {
			this.calendarBody.observe('dblclick', this.onEventCreateDayWeek.bindAsEventListener(this));
		}
	},



	/**
	 * Handle event creation in day or week viewing mode
	 *
	 * @param	{Event}	event
	 */
	onEventCreateDayWeek: function(event) {
		if( event.findElement('td.dayCol') ) {
			var time	= this.getTimeOfMouseCoordinates(event.pointerX(), event.pointerY());

			this.ext.addEvent(time);
		}
	},



	/**
	 * Handle event creation in month viewing mode
	 * Date is in string format to ignore timezone offsets
	 * (we just want the day, don't care about the local time)
	 *
	 * @param	{Event}		event
	 */
	onEventCreateMonth: function(event) {
		var cell	= event.findElement('td');

		if( cell ) {
				// Get timestamp of the date in local timezone (will be reconverted later into the same timestamp again)
			var time	= Todoyu.Time.date2Time(cell.id.split('-').slice(1).join('-'));
			
			this.ext.addEvent(time);
		}
	}

};