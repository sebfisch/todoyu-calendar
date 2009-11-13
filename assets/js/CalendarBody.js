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

Todoyu.Ext.calendar.CalendarBody = {
	
	ext: Todoyu.Ext.calendar,
	
	idArea: 'calendararea',
	
	calendarBody: null,



	/**
	 *	Init calendar body
	 *
	 *	@param	Integer	fullHeight
	 */
	init: function(fullHeight) {
		this.calendarBody = $(this.idArea);
		
		this.installContextMenu();
		if( this.ext.getActiveTab() !== 'month' ) {
			this.setFullHeight(fullHeight, false);
		}
	},



	/**
	 *	Reinitialize calendar boy
	 */
	reInit: function() {
		this.init(this.isFullHeight());
	},



	/**
	 *	Install calendar body context menu
	 */
	installContextMenu: function() {
		this.ext.ContextMenuCalendarBody.reattach();
	},



	/**
	 *	Toggle full day view mode
	 */
	toggleFullDayView: function() {
		this.setFullHeight(!this.isFullHeight(), true);
	},



	/**
	 *	Check whether calendar body is set to full height
	 */
	isFullHeight: function() {
		return this.calendarBody.hasClassName('full');
	},



	/**
	 *	Get calendar body height
	 *
	 *	@return	Integer
	 */
	getHeight: function() {
		return this.calendarBody.getHeight();
	},



	/**
	 *	Set calendar body display mode to full day height
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
	 *	Get time of mouse coordinates
	 *
	 *	@param	Integer	x
	 *	@param	Integer	y
	 *	@return	Integer
	 */
	getTimeOfMouseCoordinates: function(x, y) {
		var height	= 1010; //this.getHeight();
		var top		= y - this.calendarBody.cumulativeOffset().top;
		var left	= x - this.calendarBody.cumulativeOffset().left;

			// If view is minimized, add invisible part
		if( ! this.isFullHeight() ) {
			top += (8 * 42);
		}
		
		var percent	= top/height;
		var seconds	= percent * Todoyu.Time.seconds.day;
			// Round to quarter hours
		var rounded		= Math.round(seconds/900)*900;
		var timeInfo	= Todoyu.Time.getTimeParts(rounded);
		var dayOffset	= timeInfo.hours * Todoyu.Time.seconds.hour + timeInfo.minutes * Todoyu.Time.seconds.minute;
		
		if( this.ext.getActiveTab() === 'day' ) {
			var dayTime = this.ext.getDayStart();
		} else {
			var numDays	= Math.floor((left - 40)/89);
			var dayTime	= this.ext.getWeekStart() + numDays * Todoyu.Time.seconds.day;
		}
		
		var time = dayTime + dayOffset;
		
		return time;
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
	 *	Handle event creation in day or week viewing mode
	 *
	 * 	@param	unknown	event
	 */
	onEventCreateDayWeek: function(event) {
		if( event.findElement('td.tg-col') ) {
			var time	= this.getTimeOfMouseCoordinates(event.pointerX(), event.pointerY());

			this.ext.addEvent(time);
		}		
	},



	/**
	 *	Handle event creation in month viewing mode
	 *
	 *	@param	unknown	event
	 */
	onEventCreateMonth: function(event) {
		var cell	= event.findElement('td');
		
		if( cell ) {
			var time	= cell.id.split('-').last();
			this.ext.addEvent(time);
		}
	}
	
};