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

/**
 * Panel widget: holidaySet selector
 */

Todoyu.Ext.calendar.PanelWidget.HolidaySetSelector = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:			Todoyu.Ext.calendar,

	key:			'panelwidget-holidaysetselector',

	list:			'panelwidget-holidaysetselector-list',



	/**
	 * Init (evoke observers installation)
	 */
	init: function() {
		this.installObservers();
	},



	/**
	 * Install observers
	 */
	installObservers: function() {
		$(this.list).observe('change', this.onHolidaySetSelect.bind(this));
	},



	/**
	 * HolidaySet select event handler
	 *
	 * @param	{Event}		event
	 */
	onHolidaySetSelect: function(event) {
		var selectedSetIDs	= this.getSelectedHolidaySetIDs();

		this.verifySelectedSets(selectedSetIDs);

		this.onUpdate( selectedSetIDs.join(',') );
	},



	/**
	 * Check and verify current selection (e.g. 'none' override any selected sets)
	 *
	 * @param	{Array}		selectedSetIDs
	 */
	verifySelectedSets: function(selectedSetIDs) {
		if (selectedSetIDs.include(0)) {
			this.selectNoSetOption();
		}
	},



	/**
	 * Update event handler
	 *
	 * @param	{String}		value
	 */
	onUpdate: function(value) {
		this.savePrefs();
		Todoyu.PanelWidget.fire(this.key, value);
	},



	/**
	 * Select all holidaySets
	 */
	selectAllHolidaySets: function() {
		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			item.selected = true;
		});
	},



	/**
	 * Unselect all holidaySets
	 */
	unselectAllHolidaySets: function() {
		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			item.selected = false;
		});
	},



	/**
	 * Select 'no set'-option only
	 */
	selectNoSetOption: function() {
		this.unselectAllHolidaySets();
		$('panelwidget-holidaysetselector-list').options[0].selected= true;

	},



	/**
	 * Get IDs of selected holidaySets
	 *
	 * @return	Array
	 */
	getSelectedHolidaySetIDs: function() {
		var setIDs = [];

		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			if (item.selected) {
				setIDs.push(item.value);
			}
		});

		return setIDs;
	},



	/**
	 * Get amount of selected holidaySets
	 *
	 * @return	{Integer}
	 */
	getAmountOfselectedSets: function() {
		var amount = 0;

		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			if (item.selected) {
				amount++;
			}
		});

		return amount;
	},



	/**
	 * Check if any type is currently selected
	 *
	 * @return	{Boolean}
	 */
	isAnyHolidaySetSelected: function() {

		return ( this.getAmountOfselectedSets() > 0  );
	},



	/**
	 * Store prefs
	 */
	savePrefs: function() {
		var typeIDs	= this.getSelectedHolidaySetIDs().join(',');

		var url		= Todoyu.getUrl('calendar', 'preference');
		var options	= {
			'parameters': {
				'action':		'panelwidgetholidaysetselector',
				'preference':	this.key,
				'area':			Todoyu.getArea(),
				'value':		typeIDs
			},
			'onComplete': function(response)	{
				this.onPrefsSaved(response);
			}.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler after prefs saved: evoke refresh
	 *
	 * @param	{Object}	response
	 */
	onPrefsSaved: function(response) {
		Todoyu.Ext.calendar.refresh();
	}

};