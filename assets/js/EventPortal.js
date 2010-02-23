Todoyu.Ext.calendar.EventPortal = {

	ext: Todoyu.Ext.calendar,

	/**
	 *	Toggle details of listed event entry (in listing of e.g portal's events tab). Used for eventslist only
	 *
	 *	@param	Integer	idEvent
	 */
	toggleDetails: function(idEvent) {
			// If detail is not loaded yet, send request
		if( this.isDetailsLoaded(idEvent)) {
			$('event-' + idEvent + '-details').toggle();
			this.saveEventExpandedStatus(idEvent, $('event-' + idEvent + '-details').visible());
		} else {
			this.loadDetails(idEvent);
		}
	},



	/**
	 *	Load event details
	 *
	 *	@param	Intger	idEvent
	 *	@param	String	mode	'day' / 'week' / 'month'
	 */
	loadDetails: function(idEvent) {
		var url		= Todoyu.getUrl('calendar', 'portal');
		var options	= {
			'parameters': {
				'action': 	'detail',
				'event': 	idEvent
			},
			'onComplete': this.onDetailsLoaded.bind(idEvent)
		};
		var target	= 'event-' + idEvent + '-header';

		Todoyu.Ui.append(target, url, options);
	},

	onDetailsLoaded: function(idEvent, response) {


	},

	/**
	 *	Check whether event details are loaded
	 *
	 *	@param	Integer		idEvent
	 *	@return	Boolean
	 */
	isDetailsLoaded: function(idEvent)	{
		return Todoyu.exists('event-' + idEvent + '-details');
	},


	/**
	 *	Save event details
	 *
	 *	@param	Intger	idEvent
	 *	@param	Boolean	open
	 */
	saveEventExpandedStatus: function(idEvent, expanded) {
		var value = expanded ? 1 : 0;
		this.ext.savePref('portalEventExpanded', value, idEvent);
	},




	/**
	 *	Set event acknowledged
	 *
	 *	@param	Integer		idEvent
	 *	@param	Integer		idPerson
	 */
	acknowledgeEvent: function(idEvent, idPerson)	{
		var url = Todoyu.getUrl('calendar', 'event');

		var options = {
			'parameters': {
				'action':	'acknowledge',
				'event':	idEvent,
				'person':	idPerson
			},
			'onComplete': this.onAcknowledged.bind(this, idEvent, idPerson)
		};

		$('acknowledge-' + idEvent).removeClassName('not');

		Todoyu.send(url, options);
	},



	/**
	 *	'On acknowledged' event handler
	 *
	 *	@param	Response	response
	 */
	onAcknowledged: function(idEvent, idPerson, response)	{

	}

};