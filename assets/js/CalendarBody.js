/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
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
	 *	Ext shortcut
	 */
	ext:			Todoyu.Ext.calendar,
	
	idArea:			'calendararea',
	
	calendarBody:	null,



	/**
	 * Init calendar body
	 *
	 * @param	Boolean		fullHeight
	 */
	init: function(fullHeight) {
		this.calendarBody = $(this.idArea);
		
		this.installContextMenu();
		if( this.ext.getActiveTab() !== 'month' ) {
			this.setFullHeight(fullHeight, false);
		}
	},



	/**
	 * Reinitialize calendar boy
	 */
	reInit: function() {
		this.calendarBody = $(this.idArea);
		this.init(this.isFullHeight());
	},



	/**
	 * Install calendar body context menu
	 */
	installContextMenu: function() {
		this.ext.ContextMenuCalendarBody.reattach();
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
	 * @return	Integer
	 */
	getHeight: function() {
		return this.calendarBody.getHeight();
	},



	/**
	 * Set calendar body display mode to full day height
	 * 
	 * @param	Boolean		fullHeight
	 * @param	Boolean		savePref
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
	 *	Save full day viewing mode preference
	 */
	saveFullDayViewPref: function(){
		this.ext.savePref('fulldayview', this.isFullHeight() ? 1 : 0);
	},



	/**
	 *	Get resp. timestamp to mouse coordinates inside current calendar view (day / week / month) 
	 *
	 *	@param	Integer		x
	 *	@param	Integer		y
	 *	@return	Integer
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
				var timestamp	= this.ext.getWeekStart() + numDays * Todoyu.Time.seconds.day;
				timestamp		+=this.getDayOffset(top, 1010);
				break;
		}

		return timestamp;
	},



	/**
	 *	Get pixel-offset of day display, used to comprehend visual margins of hours in day / week mode
	 *
	 *	@param	Integer		top
	 *	@param	Integer		height
	 *	@return	Integer
	 */
	getDayOffset: function(top, height) {
		var seconds	= (top / height) * Todoyu.Time.seconds.day;
		
			// Round to quarter hours, get time parts (hours, minutes, seconds)
		var seconds		= Math.round(seconds / 900) * 900;

		var timeInfo	= Todoyu.Time.getTimeParts(seconds);
		
		return timeInfo.hours * Todoyu.Time.seconds.hour + timeInfo.minutes * Todoyu.Time.seconds.minute;
	},



	/**
	 *	Install Observers
	 */
	installObservers: function() {

	},



	/**
	 *	Install create event observer
	 */
	installObserversCreateEvent: function() {
		this.reInit();
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
	 * @param	Event	event
	 */
	onEventCreateDayWeek: function(event) {
		if( event.findElement('td.tg-col') ) {
			var time	= this.getTimeOfMouseCoordinates(event.pointerX(), event.pointerY());

			this.ext.addEvent(time);
		}		
	},



	/**
	 * Handle event creation in month viewing mode
	 *
	 * @param	Event	event
	 */
	onEventCreateMonth: function(event) {
		var cell	= event.findElement('td');
		
		if( cell ) {
			var time	= cell.id.split('-').last();
			this.ext.addEvent(time);
		}
	}
	
};