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

Todoyu.Ext.calendar.Navi = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Set calendar title
	 *
	 * @param	{String}		title
	 */
	setTitle: function(title) {
		$('calendar-title').update(title);
	},



	/**
	 * Get current active calendar tab
	 */
	getActiveTab: function() {
		return this.ext.Tabs.getActive();
	},



	/**
	 * Get up-/ down-shifted date
	 *
	 * @param	{String}		direction		'up' / 'down'
	 * @return	{Integer}
	 */
	getDirectionDate: function(direction) {
		var tab		= this.getActiveTab();
		var time	= this.ext.getTime();

		var newTime	= Todoyu.Time.getShiftedTime(time, tab, direction);

		return newTime * 1000;
	},



	/**
	 * Get down-shifted date
	 *
	 * @return	{Integer}
	 */
	getBackwardDate: function() {
		return this.getDirectionDate('down');
	},



	/**
	 * Go backward in time
	 */
	goBackward: function() {
		var date= this.getBackwardDate();

		this.ext.show(null, date);
	},



	/**
	 * Get up-shifted date
	 */
	getForwardDate: function() {
		return this.getDirectionDate('up');
	},



	/**
	 * Go forward in time
	 */
	goForward: function() {
		var date= this.getForwardDate();

		this.ext.show(null, date);
	},



	/**
	 * Get today date
	 *
	 * @return Integer
	 */
	getTodayDate: function() {
		return Todoyu.Time.getTodayDate();
	},



	/**
	 * Go to date of current today
	 */
	goToday: function() {
		var date= this.getTodayDate();

		// this.ext.PanelWidget.Calendar.Calendar.setCurrentDate('monthdown', true);

		this.ext.show(null, date);
	},



	/**
	 * Toggle day viewing mode (among full day / working hours)
	 */
	toggleFullDayView: function() {
		var toggler	= $('toggleDayView');

		toggler.toggleClassName('full');

		this.ext.CalendarBody.toggleFullDayView();
	}

};