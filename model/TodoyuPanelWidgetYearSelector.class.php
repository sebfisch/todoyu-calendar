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
 * Panel widget: Year selector
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuPanelWidgetYearSelector extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Preference name
	 *
	 */
	const PREF = 'panelwidget-yearselector-filter';



	/**
	 * Constructor of the class Todoyu(initialize widget)
	 *
	 * @param	Array	$config
	 * @param	Array	$params
	 * @param	Integer	$idArea
	 * @param	Boolean	$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {

				// construct PanelWidget (init basic configuration)
			parent::__construct(
				'calendar',								// ext key
				'yearselector',							// panel widget ID
				'LLL:panelwidget-yearselector.title',	// widget title text
				$config,								// widget config array
				$params,								// widget params
				$idArea									// area ID
			);

			// Add widget assets
		TodoyuPage::addExtAssets('calendar', 'panelWidgetYearSelector');

			// Load widget data
		//$this->initData();

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.calendar.PanelWidget.YearSelector.init.bind(Todoyu.Ext.calendar.PanelWidget.YearSelector)');

	}



	/**
	 * Render filter (form)
	 *
	 * @return String
	 */
	private function renderFilter() {
		$xmlPath	= 'ext/calendar/config/form/panelwidget-yearselector-filter.xml';

			// Construct form object
		$form		= TodoyuFormManager::getForm($xmlPath);

			// Load/ prepare form data
		$form->setUseRecordID(false);
		$filterValue	= $this->getActiveFilterValue();
		$formData		= array();
		if( $filterValue !== false ) {
			$formData['fulltext'] = $filterValue;
		}
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, 0);

			// Set form data
		$form->setFormData( $formData );

			// Render
		return $form->render();
	}



	/**
	 * Get years to be listed
	 *
	 * @return Array
	 */
	private function getListYears() {
		$start	= $GLOBALS['CONFIG']['EXT']['calendar']['PanelWidgets']['YearSelector']['start'];
		$end	= $GLOBALS['CONFIG']['EXT']['calendar']['PanelWidgets']['YearSelector']['end'];
		$years	= array();

		for($year = $start; $year <= $end; $year++) {
			$years[]= $year;
		}

		return $years;
	}



	/**
	 * Render list
	 *
	 * @return	String
	 */

	public function renderList() {
		$tmpl	= 'ext/calendar/view/panelwidgets/panelwidget-yearselector.tmpl';
		$data	= array(
			'id'	=> $this->getID(),
			'years'	=> $this->getListYears()
		);

		return render($tmpl, $data);
	}


	/**
	 * Render content
	 *
	 */
	public function renderContent() {
		$form	= $this->renderFilter();
		$list	= $this->renderList();

		$this->setContent( $form . $list );
	}



	/**
	 * Render year selector
	 *
	 * @return unknown
	 */
	public function render() {

		$this->renderContent();

//
//		$activeFilters	= array(array('filter'=> 'fulltext', 'value'=>'erni'));
//
//		$userFilter = new TodoyuUserFilter($activeFilters);
//
//		$userIDs	= $userFilter->getUserIDs();

//		TodoyuDebug::printInFirebug($userIDs);
//		TodoyuDebug::printInFirebug(Todoyu::db()->getLastQuery());


		return parent::render();
	}



	/**
	 * Get active filter value
	 *
	 * @return unknown
	 */
	private function getActiveFilterValue() {
		return TodoyuUserPreferences::getPreference(self::PREF, 0, $this->getArea());
	}



	/**
	 * Get active filter
	 *
	 * @return unknown
	 */
	public function getActiveFilter() {
		$filterValue	= $this->getActiveFilterValue();

		if( $filterValue === false ) {
			$filter = array();
		} else {
			$filter = array(
				'filter'	=> 'fulltext',
				'value'		=> $filterValue,
				'negate'	=> false
			);
		}

		return $filter;
	}



	/**
	 * Save active filter
	 *
	 * @param	Mixed	$value
	 * @param	Integer	$idArea
	 */
	public static function saveActiveFilter($value, $idArea) {
		TodoyuUserPreferences::savePreference(self::PREF, $value, 0, true, $idArea);
	}


}

?>