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

	ext: Todoyu.Ext.calendar,

	popupID: 'quickinfo',

	cache: {},

	loading: false,



	/**
	 *	@todo	comment
	 *
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
	 *	@todo	comment
	 *
	 */
	installObservers: function() {
		this.Event.installObservers();
		this.Holiday.installObservers();
	},



	/**
	 *	@todo	comment
	 *
	 */
	uninstallObservers: function() {
		this.Event.uninstallObservers();
		this.Holiday.uninstallObservers();
	},



	/**
	 *	Insert quick info elements container
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
	 *	Update quick info element style to given position and visible
	 *	@param	Integer	mouseX
	 *	@param	Integer	mouseY
	 */
	show: function(type, key, mouseX, mouseY) {
		var x = mouseX + 16, y = mouseY-12;
		var cacheID = type+key;
		


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
	 *	@todo	comment
	 *
	 */
	showPopUp: function(x, y) {
		$(this.popupID).setStyle({
			'top': y + 'px',
			'left': x + 'px'
		}).show();
	},



	/**
	 *	@todo	comment
	 *
	 */
	hide: function() {
		if ( $(this.popupID) ) {
			$(this.popupID).hide();
		}
	},
	
	

	/**
	 *	@todo	comment
	 *
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
	 *	@todo	comment
	 *
	 */
	onQuickInfoLoaded: function(type, key, x, y, response) {
		this.addToCache(type+key, response.responseText);
		this.loading = false;
		this.show(type, key, x, y);
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
	 * @param	 key
	 */
	getFromCache: function(cacheID) {
		return this.cache[cacheID];
	},



	/**
	 *	@todo	comment
	 *
	 */
	removeFromCache: function(cacheID) {
		if( this.cache[cacheID] ) {
			delete this.cache[cacheID];
		}
	},



	/**
	 *	@todo	comment
	 *
	 */
	isCached: function(cacheID) {
		return typeof(this.cache[cacheID]) === 'string';
	},



	/**
	 *	@todo	comment
	 *
	 */
	updatePopup: function(content) {
		$(this.popupID).update(content);
	},



	/**
	 *	@todo	comment
	 *
	 */
	Event: {
		ext: Todoyu.Ext.calendar,

		observers: [],


		/**
		 *	@todo	comment
		 *
		 */
		installObservers: function() {
			$$('div.quickInfoEvent').each(this.installOnElement.bind(this));
		},



		/**
		 *	@todo	comment
		 *
		 */
		installOnElement: function(element) {
			var idEvent	= element.readAttribute('id').split('-').last();

				// Mouseover
			var observerOver= this.onMouseOver.bindAsEventListener(this, idEvent);
			var observerOut	= this.onMouseOut.bindAsEventListener(this, idEvent);

			this.observers.push({
				'element':	element,
				'over':		observerOver,
				'out':		observerOut
			});

			element.observe('mouseover', observerOver);
			element.observe('mouseout', observerOut);
		},



		/**
		 *	@todo	comment
		 *
		 */
		uninstallObservers: function() {
			this.observers.each(function(observer){
				Event.stopObserving(observer.element, 'mouseover', observer.over);
				Event.stopObserving(observer.element, 'mouseout', observer.out);
			});

			this.observers = [];
		},



		/**
		 *	@todo	comment
		 *
		 */
		onMouseOver: function(event, idEvent) {
			this.ext.Quickinfo.show('event', idEvent, event.pointerX(), event.pointerY());
		},



		/**
		 *	@todo	comment
		 *
		 */
		onMouseOut: function(event, idEvent) {
			this.ext.Quickinfo.hide();
		},
		
		removeFromCache: function(idEvent) {
			this.ext.Quickinfo.removeFromCache('event' + idEvent);
		}

	},



	/**
	 *	@todo	comment
	 *
	 */
	Holiday: {
		ext: Todoyu.Ext.calendar,
		
		observers: [],


		/**
		 *	@todo	comment
		 *
		 */
		installObservers: function() {
			$$('span.quickInfoHoliday').each(this.installOnElement.bind(this));
		},



		/**
		 *	@todo	comment
		 *
		 */
		installOnElement: function(element) {
			var timestamp	= element.readAttribute('id').split('-').last();

				// Mouseover
			var observerOver= this.onMouseOver.bindAsEventListener(this, timestamp);
			var observerOut	= this.onMouseOut.bindAsEventListener(this, timestamp);

			this.observers.push({
				'element': element,
				'over': observerOver,
				'out': observerOut
			});

			element.observe('mouseover', observerOver);
			element.observe('mouseout', observerOut);
		},



		/**
		 *	@todo	comment
		 *
		 */
		uninstallObservers: function() {
			this.observers.each(function(observer){
				Event.stopObserving(observer.element, 'mouseover', observer.over);
				Event.stopObserving(observer.element, 'mouseout', observer.out);
			});

			this.observers = [];
		},



		/**
		 *	@todo	comment
		 *
		 */
		onMouseOver: function(event, timestamp) {
			this.ext.Quickinfo.show('holiday', timestamp, event.pointerX(), event.pointerY());
		},



		/**
		 *	@todo	comment
		 *
		 */
		onMouseOut: function(event, dateStr) {
			this.ext.Quickinfo.hide();
		},
		
		removeFromCache: function(idHoliday) {
			this.ext.Quickinfo.removeFromCache('holiday' + idHoliday);
		}

	}
};