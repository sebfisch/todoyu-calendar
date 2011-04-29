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
 * @namespace	Todoyu.Ext.calendar.Share
 */
Todoyu.Ext.calendar.Share =  {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.calendar,

	/**
	 * IDs of calendar sharing token types
	 *
	 * @property	tokens
	 * @type		Object
	 */
	tokenIDs: {
		personal:		4,
		availability:	5
	},



	/**
	 * Get token type ID to given type key
	 *
	 * @param	{String}	tokenTypeKey
	 * @return	{Number}
	 */
	getTokenTypeIdFromKey: function(tokenTypeKey) {
		return parseInt(this.tokenIDs[tokenTypeKey], 10);
	}

};