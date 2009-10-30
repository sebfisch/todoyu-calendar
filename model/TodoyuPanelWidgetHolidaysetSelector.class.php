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
 * Panel widget: holidayset selector
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuPanelWidgetHolidaysetSelector extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Preference name
	 *
	 */
	const PREF = 'panelwidget-holidaysetselector';


	/**
	 * Constructor (init widget)
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
		$idArea	= intval($idArea);

			// Have public ext. and widget specific assets added
		TodoyuPage::addExtAssets('calendar', 'public');
		TodoyuPage::addExtAssets('calendar', 'PanelWidgetHolidaysetSelector');

			// Construct PanelWidget (init basic configuration)
		parent::__construct(
			'calendar',									// ext key
			'holidaysetSelector',						// panel widget ID
			'LLL:panelwidget-holidaysetselector.title',	// widget title text
			$config,									// widget config array
			$params,									// widget params
			$idArea										// area ID
		);

		$this->addHasIconClass();
		$this->addClass('user');

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.HolidaysetSelector.init.bind(Todoyu.Ext.calendar.PanelWidget.HolidaysetSelector)');
	}



	/**
	 * Render panel content (holidayset selector)
	 *
	 * @return	String
	 */
	public function renderContent() {
		require_once(PATH_EXT_CALENDAR . '/config/panelwidgets.php');

		$prefs	= self::getSelectedHolidaysetIDs();
		$sets	= TodoyuHolidaySetManager::getAllHolidaySets();

		foreach($sets as $idSet => $typeData) {
			if (in_array($idSet, $prefs)) {
				$sets[ $idSet ]['selected']	= 1;
			}
		}

		$data	= array(
			'config'	=> $this->config,
			'sets'		=> $sets,
			'prefs'		=> $prefs
		);

		return render('ext/calendar/view/panelwidgets/panelwidget-holidaysetselector.tmpl', $data);
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
	 * Store prefs of the holidayset selector panel widget
	 *
	 * @param	Integer	$idArea
	 * @param	String	$prefVals
	 */
	public function savePreference($idArea = 0, $prefVals = '') {
		$idArea	= intval($idArea);

		TodoyuPreferenceManager::savePreference(
			EXTID_CALENDAR,							// ext ID
			'panelwidget-holidaysetselector', 	// preference
			$prefVals, 							// value
			0,									// item ID
			true,								// unique?
			$idArea,							// area ID
			userid()							// user ID
		);

	}



	/**
	 * Get IDs of selected holidaysets
	 *
	 * @return	Array
	 */
	public function getSelectedHolidaysetIDs($area = AREA) {
		$selectorPref	= TodoyuCalendarPreferences::getPref('panelwidget-holidaysetselector', 0, $area);
		$selectedSetIDs	= TodoyuDiv::intExplode(',', $selectorPref);
		
		return $selectedSetIDs;
	}

}


?>