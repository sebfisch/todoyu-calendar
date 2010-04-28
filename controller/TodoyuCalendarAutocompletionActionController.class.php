<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
		restrict('calendar', 'general:use');

		$this->sword = trim($params['sword']);
	}



	/**
	 * Autocomplete persons
	 *
	 * @param	Array 	$params
	 * @return	String
	 */
	public function personAction(array $params) {
		$results = TodoyuPersonFilterDataSource::autocompletePersons($this->sword);

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