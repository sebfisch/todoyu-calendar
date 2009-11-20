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
 * Panel widget: event type selector
 *
 */

Todoyu.Ext.calendar.PanelWidget.EventTypeSelector = {

	ext:	Todoyu.Ext.calendar,

	list:	null,


	/**
	 *	Init event type selector panel widget
	 */
	init: function() {
		this.list	= $('panelwidget-eventtypeSelector-list');
						
		this.installObservers();
	},



	/**
	 *	Install observers
	 */
	installObservers: function() {
		this.list.observe('change', this.onSelectionChange.bind(this));
	},



	/**
	 *	Event type select event handler
	 *
	 *	@param	unknown	event
	 */
	onSelectionChange: function(event) {
		console.log('dsfasdf');
		this.onUpdate();
	},



	/**
	 *	Update event handler
	 *
	 *	@param	Mixed	value
	 */
	onUpdate: function() {
		this.savePrefs();
	},



	/**
	 *	Select all event types
	 */
	selectAllEventTypes: function(select) {
		var selected = select === true;
		this.list.select('option').each(function(option){
			option.selected = selected;
		});
	},



	/**
	 *	Get IDs of selected event types
	 *
	 *	@return	Array
	 */
	getSelectedEventTypes: function() {
		return $F(this.list);
	},



	/**
	 *	Get amount of selected event types
	 *
	 *	@return	Integer
	 */
	getNumberOfSelectedEventTypes: function() {
		return this.getSelectedEventTypes().size();
	},



	/**
	 *	Check if any type is currently selected
	 *
	 *	@return	Boolean
	 */
	isAnyEventTypeSelected: function() {
		return this.getNumberOfSelectedEventTypes() > 0;
	},



	/**
	 *	Store user prefs
	 */
	savePrefs: function() {
		var pref = this.getSelectedEventTypes().join(',');
				
		Todoyu.Pref.save('calendar', 'panelwidgeteventtypeselector', pref, 0, this.onPrefsSaved.bind(this));
	},



	onPrefsSaved: function(response) {
		Todoyu.PanelWidget.inform('eventtypeselector', this.getSelectedEventTypes());
	}

};