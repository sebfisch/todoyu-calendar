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
 * Calendar section in profile
 *
 * @namespace	Todoyu.Ext.calendar.Profile
 */
Todoyu.Ext.calendar.Profile =  {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,


	/**
	 * Handler for tabs in calendar area of profile
	 *
	 * @method	onTabClick
	 * @param	{Event}		event
	 * @param	{String}	tabKey
	 */
	onTabClick: function(event, tabKey) {
//		this.loadTab(tabKey);
	},



	/**
	 * Save settings of profile main tab
	 *
	 * @method	saveMain
	 * @param	{Element}		form
	 */
	saveMain: function(form) {
		form.request({
			'parameters': {
				'action': 'saveMain'
			},
			'onComplete': this.onMainSaved.bind(this)
		});
	},



	/**
	 * Notify about profile saving success, have browser reload
	 *
	 * @method	onMainSaved
	 * @param	{Ajax.Response}		response
	 */
	onMainSaved: function(response) {
		Todoyu.notifySuccess('[LLL:calendar.ext.profile.main.saved]');
	}

};