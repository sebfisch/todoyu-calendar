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
 * Calendar navigation
 *
 * @namespace	Todoyu.Ext.calendar.Navi
 */
Todoyu.Ext.calendar.Navi = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,



	/**
	 * Set calendar title
	 *
	 * @method	setTitle
	 * @param	{String}		title
	 */
	setTitle: function(title) {
		$('calendar-title').update(title);
	},



	/**
	 * Get current active calendar tab
	 *
	 * @method	getActiveTab
	 */
	getActiveTab: function() {
		return this.ext.Tabs.getActive();
	},



	/**
	 * Get up-/ down-shifted date
	 *
	 * @method	getDirectionDate
	 * @param	{String}		direction		'up' / 'down'
	 * @return	{Number}
	 */
	getDirectionDate: function(up) {
		var tab		= this.getActiveTab();
		var time	= this.ext.getTime();

		var newTime	= Todoyu.Time.getShiftedTime(time, tab, up);

		return newTime * 1000;
	},



	/**
	 * Get down-shifted date
	 *
	 * @method	getBackwardDate
	 * @return	{Number}
	 */
	getBackwardDate: function() {
		return this.getDirectionDate(false);
	},



	/**
	 * Go backward in time
	 *
	 * @method	goBackward
	 */
	goBackward: function() {
		var date= this.getBackwardDate();

		this.ext.show(null, date);
	},



	/**
	 * Get up-shifted date
	 *
	 * @method	getForwardDate
	 * @return	{Number}
	 */
	getForwardDate: function() {
		return this.getDirectionDate(true);
	},



	/**
	 * Go forward in time
	 *
	 * @method	goForward
	 */
	goForward: function() {
		var date= this.getForwardDate();

		this.ext.show(null, date);
	},



	/**
	 * Get today date
	 *
	 * @method	getTodayDate
	 * @return	{Number}
	 */
	getTodayDate: function() {
		return Todoyu.Time.getTodayDate();
	},



	/**
	 * Go to date of current today
	 *
	 * @method	goToday
	 */
	goToday: function() {
		var date= this.getTodayDate();

		// this.ext.PanelWidget.Calendar.Calendar.setCurrentDate('monthdown', true);

		this.ext.show(null, date);
	},



	/**
	 * Toggle day viewing mode (among full day / working hours)
	 *
	 * @method	toggleFullDayView
	 */
	toggleFullDayView: function() {
		var toggler	= $('toggleDayView');

		toggler.toggleClassName('full');

		this.ext.CalendarBody.toggleFullDayView();
	}

};