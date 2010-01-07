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
 * Autocompletion action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarAutocompletionActionController extends TodoyuActionController {

	/**
	 * Autocomplete searchword
	 *
	 * @var	String
	 */
	protected $sword;



	/**
	 * Init
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		restrict('calendar', 'use');

		$this->sword = trim($params['sword']);
	}



	/**
	 * User action method
	 *
	 * @param	Array $params
	 * @return	String
	 */
	public function userAction(array $params) {
		$results = TodoyuUserFilterDataSource::autocompleteUsers($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}



	/**
	 * Project action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function projectAction(array $params) {
		$results = TodoyuProjectFilterDataSource::autocompleteProjects($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}



	/**
	 * Get task autocomplete
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function taskAction(array $params) {
		$idProject	= intval($params['event']['id_project']);

			// If project is set, only search in project task, else search in all tasks
		if( $idProject !== 0 ) {
			$filters = array(
				array(
					'filter'	=> 'tasknumberortitle',
					'value'		=> $this->sword
				),
				array(
					'filter'	=> 'project',
					'value'		=> $idProject
				)
			);

			$acTasks	= TodoyuTaskFilterDataSource::getTaskAutocompleteListByFilter($filters);
		} else {
			$acTasks	= TodoyuTaskFilterDataSource::getTaskAutocompleteListBySearchword($this->sword);
		}

		return TodoyuRenderer::renderAutocompleteList($acTasks);
	}



	/**
	 * Holiday action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function holidayAction(array $params) {
		$results = TodoyuHolidayManager::autocompleteHolidays($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}



	/**
	 * HolidaySet action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function holidaysetAction(array $params) {
		$results = TodoyuHolidaySetManager::autocompleteHolidaySet($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

}

?>