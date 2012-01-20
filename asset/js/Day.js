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
 * Special day view methods
 */
Todoyu.Ext.calendar.Day = {

	ext: Todoyu.Ext.calendar,

	/**
	 * Get time for position
	 *
	 * @param	{Number}	x
	 * @param	{Number}	y
	 */
	getDateForPosition: function(x, y) {
		var offsetTop	= this.ext.CalendarBody.getFixedTopOffset(y);

		return this.ext.getDayStart() + this.ext.CalendarBody.getDayOffset(offsetTop);
	}

};