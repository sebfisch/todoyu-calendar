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

	protected $sword;

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function init(array $params) {
		$this->sword = trim($params['sword']);
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function userAction(array $params) {
		$results = TodoyuUserFilterDataSource::autocompleteUsers($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function projectAction(array $params) {
		$results = TodoyuProjectFilterDataSource::autocompleteProjects($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function taskAction(array $params) {
		$results = TodoyuTaskFilterDataSource::autocompleteTasks($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function holidayAction(array $params) {
		$results = TodoyuHolidayManager::autocompleteHolidays($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function holidaysetAction(array $params) {
		$results = TodoyuHolidaySetManager::autocompleteHolidaySet($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}


}

?>