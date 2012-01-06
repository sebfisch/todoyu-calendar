/****************************************************************************
 * todoyu is published under the BSD License:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Handle week view
 */
Todoyu.Ext.calendar.Week = {

	/**
	 * Extension back ref
	 */
	ext: Todoyu.Ext.calendar,


	/**
	 * Check whether weekend is displayed
	 *
	 * @return	{Boolean}
	 */
	isWeekendDisplayed: function() {
		return this.ext.CalendarBody.getAmountDisplayedDays() === 7;
	},



	/**
	 * Get snap config for drag and drop in week view
	 *
	 * @return	{Array}
	 */
	getDragDropSnap: function() {
		var vertical	= this.ext.DragDrop.verticalSnap;
		var horizontal	= this.getDayColWidth();

		return [horizontal, vertical];
	},



	/**
	 * Get width of day column
	 *
	 * @return	{Number}
	 */
	getDayColWidth: function() {
		return $('tgTable').down('.dayCol').getWidth();
	},



	/**
	 * Calculate date for drop position
	 *
	 * @param	{Object}	dragInfo
	 * @return	{Date}
	 */
	getDropDate: function(dragInfo) {
		var hourHeight	= 42;
		var timeColWidth= 42;
		var dayWidth	= this.getDayColWidth();
		var offset		= dragInfo.element.positionedOffset();
			// Offset fix (offset seems to be shifted one hour)
		offset.top	+= hourHeight;

		var weekStart	= this.ext.getWeekStart();
		var dayIndex	= Math.round(Math.abs(offset.left - timeColWidth) / dayWidth);

		var dayHours	= Math.round((offset.top / hourHeight)*4)/4;
		var timestamp	= weekStart + Todoyu.Time.seconds.day * dayIndex + dayHours * Todoyu.Time.seconds.hour;

		return new Date(timestamp*1000);
	}


};