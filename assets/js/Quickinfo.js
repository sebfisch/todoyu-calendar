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
	
	init: function(install) {
		if( ! Todoyu.exists(this.popUpID) ) {
			this.insertQuickInfoElement();
		}
		
		this.uninstallObservers();
		
		if( install === true ) {
			this.installObservers();
		}
	},
	
	installObservers: function() {
		this.Event.installObservers();
		this.Holiday.installObservers();
	},
	
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

	showPopUp: function(x, y) {
		$(this.popUpID).setStyle({
			'top': y + 'px',
			'left': x + 'px'
		}).show();
	},

	hide: function() {
		$(this.popUpID).hide();
	},

	loadQuickInfo: function(type, key, mouseX, mouseY) {
		var	url		= Todoyu.getUrl('calendar', 'quickinfo');
		var options	= {
			'parameters': {
				'cmd':		type,
				'key':		key
			},
			'onComplete': this.onQuickInfoLoaded.bind(this, key, mouseX, mouseY)		
		};
		
		Todoyu.send(url, options);
		
	},

	onQuickInfoLoaded: function(key, x, y, response) {
		this.addToCache(key, response.responseText);
		this.updateContent(response.responseText);
		this.showPopUp(x, y);
		this.loading = false;
	},

	addToCache: function(key, content) {
		this.cache[key] = content;
	},

	getFromCache: function(key) {
		return this.cache[key];
	},

	removeFromCache: function(key) {
		if( this.cache[key] ) {
			delete this.cache[key];
		}
	},

	isCached: function(key) {
		return typeof(this.cache[key]) === 'string';
	},

	updateContent: function(content) {
		$(this.popUpID).update(content);
	},

	Event: {
		observers: [],

		elementSelector:  'div.eventQuickInfoHotspot',

		installObservers: function() {
			$$(this.elementSelector).each(this.installOnElement.bind(this));
		},

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

		uninstallObservers: function() {
			this.observers.each(function(observer){
				Event.stopObserving(observer.element, 'mouseover', observer.over);
				Event.stopObserving(observer.element, 'mouseout', observer.out);
			});

			this.observers = [];
		},

		onMouseOver: function(event, idEvent) {
			Todoyu.Ext.calendar.Quickinfo.show('event', idEvent, event.pointerX(), event.pointerY());
		},
		
		onMouseOut: function(event, idEvent) {
			Todoyu.Ext.calendar.Quickinfo.hide();
		}		
		
	},
	
	Holiday: {
		observers: [],
		
		elementSelector:  'span.holidayQuickInfoHotspot',
		
		installObservers: function() {
			$$(this.elementSelector).each(this.installOnElement.bind(this));
		},
		
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
		
		uninstallObservers: function() {
			this.observers.each(function(observer){
				Event.stopObserving(observer.element, 'mouseover', observer.over);
				Event.stopObserving(observer.element, 'mouseout', observer.out);
			});
			
			this.observers = [];
		},
			
		onMouseOver: function(event, dateStr) {
			var year	= dateStr.substr(0,4);
			var month	= dateStr.substr(4,2);
			var day		= dateStr.substr(6,2);
			var date	= new Date(year, month, day);
			var time	= date.getTime()/1000;
			
			Todoyu.Ext.calendar.Quickinfo.show('holiday', time, event.pointerX(), event.pointerY());
		},
		
		onMouseOut: function(event, dateStr) {
			Todoyu.Ext.calendar.Quickinfo.hide();
		}		
		
	}
};