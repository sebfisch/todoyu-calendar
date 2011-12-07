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
 * Panel widget: holidaySet selector
 *
 * @namespace	Todoyu.Ext.calendar.PanelWidget.HolidaySetSelector
 */
Todoyu.Ext.calendar.PanelWidget.HolidaySetSelector = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:			Todoyu.Ext.calendar,

	/**
	 * @property	key
	 * @type		String
	 */
	key:			'panelwidget-holidaysetselector',

	/**
	 * @property	list
	 * @type		String
	 */
	list:			'panelwidget-holidaysetselector-list',



	/**
	 * Init (evoke observers installation)
	 *
	 * @method	init
	 */
	init: function() {
		this.installObservers();
	},



	/**
	 * Install observers
	 *
	 * @method	installObservers
	 */
	installObservers: function() {
		$(this.list).on('change', this.onHolidaySetSelect.bind(this));
	},



	/**
	 * HolidaySet select event handler
	 *
	 * @method	onHolidaySetSelect
	 * @param	{Event}		event
	 */
	onHolidaySetSelect: function(event) {
		var selectedSetIDs	= this.getSelectedHolidaySetIDs();

		this.verifySelectedSets(selectedSetIDs);

		selectedSetIDs	= this.getSelectedHolidaySetIDs();

		this.onUpdate(selectedSetIDs.join(','));
	},



	/**
	 * Check and verify current selection (e.g. 'none' override any selected sets)
	 *
	 * @method	verifySelectedSets
	 * @param	{Array}		selectedSetIDs
	 */
	verifySelectedSets: function(selectedSetIDs) {
		if( selectedSetIDs.include(0) ) {
			this.selectNoSetOption();
		}
	},



	/**
	 * Update event handler
	 *
	 * @method	onUpdate
	 * @param	{String}		value
	 */
	onUpdate: function(value) {
		this.savePrefs();
		Todoyu.PanelWidget.fire(this.key, value);
	},



	/**
	 * Select all holidaySets
	 *
	 * @method	selectAllHolidaySets
	 * @param	{Boolean}				select
	 */
	selectAllHolidaySets: function(select) {
		var selected = select === true;

		$(this.list).select('option').each(function(option) {
			option.selected = selected;
		});
	},



	/**
	 * Deselect all holidaySets
	 *
	 * @method	deselectAllHolidaySets
	 */
	deselectAllHolidaySets: function() {
		this.selectAllHolidaySets(false);
	},



	/**
	 * Select 'no set'-option only
	 *
	 * @method	selectNoSetOption
	 */
	selectNoSetOption: function() {
		this.deselectAllHolidaySets();

		$(this.list).options[0].selected= true;
	},



	/**
	 * Get IDs of selected holidaySets
	 *
	 * @method	getSelectedHolidaySetIDs
	 * @return	{Array}
	 */
	getSelectedHolidaySetIDs: function() {
		return $(this.list).select('option:selected').collect(function(option) {
			return option.value;
		});
	},



	/**
	 * Get amount of selected holidaySets
	 *
	 * @method	getAmountOfSelectedSets
	 * @return	{Number}
	 */
	getAmountOfSelectedSets: function() {
		return $(this.list).select('option:selected').length;
	},



	/**
	 * Check if any type is currently selected
	 *
	 * @method	isAnyHolidaySetSelected
	 * @return	{Boolean}
	 */
	isAnyHolidaySetSelected: function() {
		return this.getAmountOfSelectedSets() > 0;
	},



	/**
	 * Store prefs
	 *
	 * @method	savePrefs
	 */
	savePrefs: function() {
		var typeIDs	= this.getSelectedHolidaySetIDs().join(',');

		var url		= Todoyu.getUrl('calendar', 'preference');
		var options	= {
			parameters: {
				action:		'panelwidgetholidaysetselector',
				'preference':	this.key,
				'area':			Todoyu.getArea(),
				'value':		typeIDs
			},
			onComplete: function(response) {
				this.onPrefsSaved(response);
			}.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler after prefs saved: evoke refresh
	 *
	 * @method	onPrefsSaved
	 * @param	{Ajax.Response}	response
	 */
	onPrefsSaved: function(response) {
		Todoyu.Ext.calendar.refresh();
	}

};