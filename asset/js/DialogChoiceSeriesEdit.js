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
 * Popup to decide if the whole series or just a single event should be edited
 */
Todoyu.Ext.calendar.DialogChoiceSeriesEdit = Class.create(Todoyu.DialogChoice, {

	/**
	 * Initialize popup
	 *
	 * @param	{Function}	$super
	 * @param	{Function}	onSelect		Callback for selection. Args: selection and data
	 * @param	{Object}	data			Additional data to give to the callback function as second parameter
	 */
	initialize: function($super, onSelect, data) {
		var options		= {
			title: 'Edit series or single event?'
		};
		var config = {
			description: 'This is a series event. Do you want to edit the whole series or just this single event?',
			options: [
				{
					id: 'event',
					button: 'Only this event',
					label: 'The other events of the series will not change'
				},
				{
					id: 'series',
					button: 'Whole series',
					label: 'Modify all events of this series'
				}
			],
			onSelect: onSelect,
			data: data || {}
		};

		$super(config, options);
	}

});