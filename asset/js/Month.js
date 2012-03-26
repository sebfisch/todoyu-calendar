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
 * Calendar month view functions
 */
Todoyu.Ext.calendar.Month = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext: Todoyu.Ext.calendar,

	/**
	 * Width of a month day
	 */
	monthDaySnap: 94.5,



	/**
	 * Get options for drag'n' drop
	 *
	 * @method	getDragOptions
	 * @return	{Object}
	 */
	getDragOptions: function() {
		return {
			revert: this.dragDropRevert.bind(this),
			snap:	this.getDragDropSnap.bind(this)
		};
	},



	/**
	 * If event dragged in month view and dropping on a day failed, move it back to its day container
	 *
	 * @method	dragDropRevert
	 * @param	{Element}	element
	 */
	dragDropRevert: function(element) {
		if( !element.dropped && element.revertToOrigin ) {
			element.revertToOrigin();
		}

		element.dropped	= false;
	},



	/**
	 * Get drag and drop snap positions callback
	 *
	 * @method	getDragDropSnap
  	 * @param	{Number}		x
	 * @param	{Number}		y
	 * @param	{Draggable}		draggable
	 * @return	{Number[]}
	 */
	getDragDropSnap: function(x, y, draggable) {
		x = Math.round(x/this.monthDaySnap)*this.monthDaySnap;
		y = Math.round(y/20)*20;

			// Kepp in horizontal range
		x = x < 0 ? 0 : x;
		x = x > 570 ? 570 : x;

			// Keep in vertical range
		y = y < 70 ? 70 : y;
		y = y > 770 ? 770 : y;

		return [x,y];
	},



	/**
	 * Add drop functions to all days
	 *
	 * @method	makeDaysDroppable
	 */
	createDayDropZones: function() {
		this.getDropDays().each(function(dayElement){
			Droppables.add(dayElement, {
				accept:		'event',
				onDrop:		this.onDrop.bind(this),
				hoverclass: 'dndHover'
			});
		}, this);
	},



	/**
	 * Get all day containers in month view
	 *
	 * @method	getDropDaysInMonth
	 * @return	{Array}
	 */
	getDropDays: function() {
		return $('mvEventContainer').select('td.content');
	},



	/**
	 * Month view - Handler when event was dropped on a day (successful)
	 *
	 * @method	onMonthDrop
	 * @param	{Element}	dragged
	 * @param	{Element}	dropped
	 * @param	{Event}		event
	 */
	onDrop: function(dragged, dropped, event) {
		dragged.dropped	= true;

		var idEvent		= dragged.id.split('-').last();
		var dateParts	= dropped.id.split('-').slice(1);
		var newDate		= new Date(dateParts[0], dateParts[1]-1, dateParts[2]);

		this.ext.DragDrop.saveDropping('month', idEvent, newDate, false);
	}


};