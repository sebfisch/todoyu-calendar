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

/**
 * Panel widget: holidayset selector
 *
 */

Todoyu.Ext.calendar.PanelWidget.HolidaysetSelector = {

	ext:			Todoyu.Ext.user,

	key:			'panelwidget-holidaysetselector',

	list:			'panelwidget-holidaysetselector-list',



	/**
	 *	Init (evoke observers installation)
	 */
	init: function() {
		this.installObservers();
	},



	/**
	 *	Install observers
	 */
	installObservers: function() {
		$(this.list).observe('change', this.onHolidaysetSelect.bind(this));
	},



	/**
	 *	Holidayset select event handler
	 *
	 *	@param	unknown	event
	 */
	onHolidaysetSelect: function(event) {
		this.onUpdate( this.getSelectedHolidaysetIDs().join(',') );
	},



	/**
	 *	Update event handler
	 *
	 *	@param	String	value
	 */
	onUpdate: function(value) {
		this.savePrefs();
		Todoyu.PanelWidget.inform(this.key, value);
	},



	/**
	 *	Select all holidaysets
	 */
	selectAllHolidaysets: function() {
		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			item.selected = true;
		});
	},



	/**
	 *	Unselect all holidaysets
	 */
	unselectAllHolidaysets: function() {
		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			item.selected = false;
		});
	},


	/**
	 *	Select 'no set'-option only
	 */
	selectNoSetOption: function() {
		this.unselectAllHolidaysets();
		$('panelwidget-holidaysetselector-list').options[0].selected= true;

	},



	/**
	 *	Get IDs of selected holidaysets
	 *
	 *	@return	Array
	 */
	getSelectedHolidaysetIDs: function() {
		var setIDs = [];

		$$('#panelwidget-holidaysetselector-list option').each(function(item) {
			if (item.selected) {
				setIDs.push(item.value);
			}
		});

		return setIDs;
	},



	/**
	 *	Get amount of selected holidaysets
	 *
	 *	@return	Integer
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
	 *	Check if any type is currently selected
	 *
	 *	@return	Boolean
	 */
	isAnyHolidaysetSelected: function() {

		return ( this.getAmountOfselectedSets() > 0  );
	},



	/**
	 *	Store user prefs
	 */
	savePrefs: function() {
		var typeIDs	= this.getSelectedHolidaysetIDs().join(',');

		var url		= Todoyu.getUrl('calendar', 'preference');
		var options	= {
			'parameters': {
				'cmd':						'panelwidgetholidaysetselector',
				'preference':				this.key,
				'area':						Todoyu.getArea(),
				'value':					typeIDs
			}
		};

		Todoyu.send(url, options);
	}
};