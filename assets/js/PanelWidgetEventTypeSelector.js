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
 * Panel widget: event type selector
 */

Todoyu.Ext.calendar.PanelWidget.EventTypeSelector = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.calendar,

	list:	null,



	/**
	 * Init event type selector panel widget
	 */
	init: function() {
		this.list	= $('panelwidget-eventtypeSelector-list');

		this.installObservers();
	},



	/**
	 * Install observers
	 */
	installObservers: function() {
		this.list.observe('change', this.onSelectionChange.bind(this));
	},



	/**
	 * Event type select event handler
	 *
	 * @param	{Event}		event
	 */
	onSelectionChange: function(event) {
		this.onUpdate();
	},



	/**
	 * Update event handler
	 *
	 * @param	{Mixed}	value
	 */
	onUpdate: function() {
		this.savePrefs();
	},



	/**
	 * Select all event types
	 * 
	 * @param	{Boolean}		select
	 * @todo	remove param 'select'?
	 */
	selectAllEventTypes: function(select) {
		var selected = select === true;
		this.list.select('option').each(function(option){
			option.selected = selected;
		});
	},



	/**
	 * Get IDs of selected event types
	 *
	 * @return	Array
	 */
	getSelectedEventTypes: function() {
		return $F(this.list);
	},



	/**
	 * Get amount of selected event types
	 *
	 * @return	{Integer}
	 */
	getNumberOfSelectedEventTypes: function() {
		return this.getSelectedEventTypes().size();
	},



	/**
	 * Check if any type is currently selected
	 *
	 * @return	{Boolean}
	 */
	isAnyEventTypeSelected: function() {
		return this.getNumberOfSelectedEventTypes() > 0;
	},



	/**
	 * Store prefs
	 */
	savePrefs: function() {
		var pref = this.getSelectedEventTypes().join(',');

		Todoyu.Pref.save('calendar', 'panelwidgeteventtypeselector', pref, 0, this.onPrefsSaved.bind(this));
	},



	/**
	 * Hanlder after prefs have been saved: send updtate info
	 *
	 * @param	{Object}	response
	 */
	onPrefsSaved: function(response) {
		Todoyu.PanelWidget.fire('eventtypeselector', this.getSelectedEventTypes());
	}

};