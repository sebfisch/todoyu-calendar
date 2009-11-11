<?php
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
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuPanelWidgetEventTypeSelector extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Preference name
	 *
	 */
	const PREF = 'panelwidget-eventtypeselector';


	/**
	 * Constructor (init widget)
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
			// construct PanelWidget (init basic configuration)
		parent::__construct(
			'calendar',									// ext key
			'eventtypeSelector',						// panel widget ID
			'LLL:panelwidget-eventtypeselector.title',	// widget title text
			$config,									// widget config array
			$params,									// widget params
			$idArea										// area ID
		);

			// Add assets
		TodoyuPage::addExtAssets('calendar', 'public');
		TodoyuPage::addExtAssets('calendar', 'panelwidget-eventtypeselector');

		$this->addHasIconClass();
		$this->addClass('user');

			// init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.EventTypeSelector.init.bind(Todoyu.Ext.calendar.PanelWidget.EventTypeSelector)');
	}



	/**
	 * Render panel content (event type selector)
	 *
	 * @return	String
	 */
	public function renderContent() {
//		TodoyuExtensions::loadConfig('calendar', 'panelwidgets');

		$selectedEventTypes	= $this->getSelectedEventTypes();
		$eventTypes			= TodoyuEventManager::getEventTypes(true);

		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-eventtypeselector.tmpl';
		$data	= array(
			'id'			=> $this->getID(),
			'eventtypes'	=> $eventTypes,
			'selected'		=> $selectedEventTypes,
			'config'		=> $this->config
		);

		return render($tmpl, $data);



		$prefs	= self::getSelectedEventTypes();

		$selectAll = false;
		if ((count($prefs) == 0 || $prefs == array(0 => 0)) && $this->config['selectAllOnFirstRun']) {
				// No type selected? check if this the first run
			$isPrefSet	= TodoyuPreferenceManager::isPreferenceSet(EXTID_CALENDAR, 'panelwidget-eventtypeselector', 0, null, AREA);
			if (! $isPrefSet) {
				$selectAll = true;
			}
		}

		$types	= TodoyuEventManager::getEventTypes(true);
		foreach($types as $idType => $typeData) {
			if (in_array($idType, $prefs) || $selectAll) {
				$types[ $idType ]['selected']	= 1;
			}
		}

		$data	= array(
			'config'			=> $this->config,
			'currentUserID'		=> userid(),
			'types'				=> $types,
		);

		return render('ext/calendar/view/panelwidgets/panelwidget-eventtypeselector.tmpl', $data);
	}



	/**
	 * Render widget (get evoked)
	 *
	 * @return	String
	 */
	public function render() {
		$this->setContent( $this->renderContent() );

		return parent::render();
	}



	/**
	 * Get context menu items
	 *
	 * @param	Integer	$idProject
	 * @return	Array
	 */
	public static function getContextMenuItems($idProject, array $items) {
//		$idProject	= intval($idProject);
//		$isExpanded	= TodoyuProjectPreferences::isProjectDetailsExpanded($idProject);
//
//		$ownItems	= $GLOBALS['CONFIG']['EXT']['calendar']['ContextMenu']['panelwidget-eventtypeselector'];
//
//		if( $isExpanded ) {
//			unset($ownItems['showdetails']);
//		} else {
//			unset($ownItems['hidedetails']);
//		}
//
//		$items	= array_merge_recursive($items, $ownItems);
//
//		return $items;
	}



	/**
	 * Get current event types selection (from prefs)
	 *
	 * @return array
	 */
	public function getSelectedEventTypes() {
		$eventTypes	= TodoyuCalendarPreferences::getPref('panelwidget-eventtypeselector', 0, AREA);

		if( $eventTypes === false || $eventTypes === '' ) {
			$eventTypes	= TodoyuEventManager::getEventTypes(false);
			$eventTypes	= array_keys($eventTypes);
		} else {
			$eventTypes	= TodoyuDiv::intExplode(',', $eventTypes);
		}

		return $eventTypes;
	}



	/**
	 * Store prefs of the event type selector panel widget
	 *
	 * @param	Integer	$idArea
	 * @param	String	$prefVals
	 */
	public function savePreference($idArea = 0, $prefVals = '') {
		$idArea	= intval($idArea);

		TodoyuPreferenceManager::savePreference(
			EXTID_CALENDAR,							// ext ID
			'panelwidget-eventtypeselector', 	// preference
			$prefVals, 							// value
			0,									// item ID
			true,								// unique?
			$idArea,							// area ID
			userid()							// user ID
		);

	}
}


?>