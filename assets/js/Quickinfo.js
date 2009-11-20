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
	
	popUpID: 'quickinfo',
		
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
		if( ! Todoyu.exists(this.popUpID) ) {
			var quickInfo  = new Element('div', {
				'id':	this.popUpID
			}).setStyle({
				'display': 'none'
			});
			
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

		if( this.loading === true ) {
			return false;
		}		
		this.loading = true;

		if( this.isCached(key) ) {
			this.updateContent(this.getFromCache(key));
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
		$(this.popUpID).setStyle({
			'top': y + 'px',
			'left': x + 'px'
		}).show();
	},



	/**
	 *	@todo	comment
	 *
	 */
	hide: function() {
		$(this.popUpID).hide();
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
			'onComplete': this.onQuickInfoLoaded.bind(this, key, mouseX, mouseY)		
		};
		
		Todoyu.send(url, options);
		
	},



	/**
	 *	@todo	comment
	 *
	 */
	onQuickInfoLoaded: function(key, x, y, response) {
		this.addToCache(key, response.responseText);
		this.updateContent(response.responseText);
		this.showPopUp(x, y);
		this.loading = false;
	},



	/**
	 *	@todo	comment
	 *
	 */
	addToCache: function(key, content) {
		this.cache[key] = content;
	},



	/**
	 *	@todo	comment
	 *
	 */
	getFromCache: function(key) {
		return this.cache[key];
	},



	/**
	 *	@todo	comment
	 *
	 */
	removeFromCache: function(key) {
		if( this.cache[key] ) {
			delete this.cache[key];
		}
	},



	/**
	 *	@todo	comment
	 *
	 */
	isCached: function(key) {
		return typeof(this.cache[key]) === 'string';
	},



	/**
	 *	@todo	comment
	 *
	 */
	updateContent: function(content) {
		$(this.popUpID).update(content);
	},



	/**
	 *	@todo	comment
	 *
	 */
	Event: {
		observers: [],

		elementSelector:  'div.eventQuickInfoHotspot',



		/**
		 *	@todo	comment
		 *
		 */
		installObservers: function() {
			$$(this.elementSelector).each(this.installOnElement.bind(this));
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
		onMouseOver: function(event, idEvent) {
			Todoyu.Ext.calendar.Quickinfo.show('event', idEvent, event.pointerX(), event.pointerY());
		},



		/**
		 *	@todo	comment
		 *
		 */
		onMouseOut: function(event, idEvent) {
			Todoyu.Ext.calendar.Quickinfo.hide();
		}		
		
	},



	/**
	 *	@todo	comment
	 *
	 */
	Holiday: {
		observers: [],
		
		elementSelector:  'span.holidayQuickInfoHotspot',



		/**
		 *	@todo	comment
		 *
		 */
		installObservers: function() {
			$$(this.elementSelector).each(this.installOnElement.bind(this));
		},



		/**
		 *	@todo	comment
		 *
		 */
		installOnElement: function(element) {		
			var dateStr	= element.readAttribute('id').split('-').last();
			
				// Mouseover
			var observerOver= this.onMouseOver.bindAsEventListener(this, dateStr);
			var observerOut	= this.onMouseOut.bindAsEventListener(this, dateStr);
			
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
		onMouseOver: function(event, dateStr) {
			var year	= dateStr.substr(0,4);
			var month	= dateStr.substr(4,2);
			var day		= dateStr.substr(6,2);
			var date	= new Date(year, month, day);
			var time	= date.getTime()/1000;
			
			Todoyu.Ext.calendar.Quickinfo.show('holiday', time, event.pointerX(), event.pointerY());
		},



		/**
		 *	@todo	comment
		 *
		 */
		onMouseOut: function(event, dateStr) {
			Todoyu.Ext.calendar.Quickinfo.hide();
		}		
		
	}
};