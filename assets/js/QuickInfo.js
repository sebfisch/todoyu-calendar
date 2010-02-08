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

Todoyu.Ext.calendar.Quickinfo = {

	/**
	 * Ext shortcut
	 */
	ext:		Todoyu.Ext.calendar,

	popupID:	'quickinfo',

	cache:		{},

	loading:	false,
	
	hidden:		false,
	
	template:	null,



	/**
	 * Init calendar quickinfo: evoke insertion of quickinfo element
	 */
	init: function(install) {
		if( ! Todoyu.exists(this.popUpID) ) {
			this.insertQuickInfoElement();
		}

		this.uninstallObservers();

		if( install === true ) {
			this.installObservers();
		}
	},



	/**
	 * Install element observers on: event-, holiday- elements.
	 */
	installObservers: function() {
		this.Event.installObservers();
		this.Holiday.installObservers();
		this.Birthday.installObservers();
	},



	/**
	 * Uninstall quickinfo element observers
	 */
	uninstallObservers: function() {
		this.Event.uninstallObservers();
		this.Holiday.uninstallObservers();
		this.Birthday.uninstallObservers();
	},



	/**
	 * Insert quick info elements container
	 */
	insertQuickInfoElement: function() {
		if( ! Todoyu.exists(this.popupID) ) {
			var quickInfo  = new Element('div', {
				'id':	this.popupID
			}).hide();

			$(document.body).insert(quickInfo);
		}
	},



	/**
	 * Update quick info element style to given position and set it visible
	 * 
	 * @param	String		type	('event', 'holiday')
	 * @param	Integer		mouseX
	 * @param	Integer		mouseY
	 */
	show: function(type, key, mouseX, mouseY) {
		this.hidden	= false;
		
		var x = mouseX + 16, y = mouseY-12;
		var cacheID = type + key;

		if( this.loading === true ) {
			return false;
		}
		this.loading = true;

		if( this.isCached(cacheID) ) {
			this.updatePopup(this.getFromCache(cacheID));
			this.showPopUp(x, y);
			this.loading = false;
		} else {
			this.loadQuickInfo(type, key, x, y);
		}
	},



	/**
	 * Display popup layer at given coordinates
	 *
	 * @param	Integer		x
	 * @param	Integer		y
	 */
	showPopUp: function(x, y) {
			// Check hide-flag (prevent lapse due to running request while mouseOut happened)
		if (! this.hidden) {
			$(this.popupID).setStyle({
				'top':	y + 'px',
				'left':	x + 'px'
			}).show();
		}
	},



	/**
	 * Hide quick info tooltip
	 */
	hide: function() {
		if ( $(this.popupID) ) {
			$(this.popupID).hide();
			
				// hide-flag: comprehend overlapping of mouseOut and running show request
			this.hidden	= true;
		}
	},
	
	

	/**
	 * Evoke loading of quickinfo tooltip content
	 *
	 * @param	String	type	('event', 'holiday')
	 * @param	String	key
	 * @param	Integer	mouseX
	 * @param	Integer	mouseY
	 */
	loadQuickInfo: function(type, key, mouseX, mouseY) {
		var	url		= Todoyu.getUrl('calendar', 'quickinfo');
		var options	= {
			'parameters': {
				'action':	type,
				'key':		key
			},
			'onComplete': this.onQuickInfoLoaded.bind(this, type, key, mouseX, mouseY)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Evoked upon quickinfo having been loaded, shows the quickinfo tooltip
	 * 
	 * @param	String	type
	 * @param	String	key
	 * @param	Integer	x
	 * @param	Integer	y
	 * @param	Object	response
	 */
	onQuickInfoLoaded: function(type, key, x, y, response) {
		this.addToCache(type + key, this.buildQuickinfo(response.responseJSON));
		this.loading = false;
		if ( ! this.hidden ) {
			this.show(type, key, x, y);
		}
	},
	
	
	
	/**
	 * Build html code based on json data
	 * 
	 * @param	JSON	json
	 */
	buildQuickinfo: function(json) {
		if( this.template === null ) {
			this.template = new Template('<dt class="#{key}Icon">&nbsp;</dt><dd class="#{key}Label">#{label}&nbsp;</dd>');
		}
		
		var content	= '';
		
		json.each(function(item){
			content += this.template.evaluate(item);
		}.bind(this));
		
		return '<dl>' + content + '</dl>';		
	},
	


	/**
	 * Add quickinfo content to cache
	 * 
	 * @param	String		cacheID
	 * @param	String		content
	 */
	addToCache: function(cacheID, content) {
		this.cache[cacheID] = content;
	},


	
	/**
	 * Get quickinfo from cache
	 * 
	 * @param	String	cacheID
	 * @return	String
	 */
	getFromCache: function(cacheID) {
		return this.cache[cacheID];
	},



	/**
	 * Remove item of given ID from cache
	 * 
	 * @param	String	cacheID
	 */
	removeFromCache: function(cacheID) {
		if( this.cache[cacheID] ) {
			delete this.cache[cacheID];
		}
	},



	/**
	 * Check whether item with given ID is cached
	 * 
	 * @return	Boolean
	 */
	isCached: function(cacheID) {
		return typeof(this.cache[cacheID]) === 'string';
	},



	/**
	 * Update popup content
	 * 
	 * @param	String	content
	 */
	updatePopup: function(content) {
		$(this.popupID).update(content);
	}

};