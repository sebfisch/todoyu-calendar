<?php

class TodoyuCalendarAutocompletionActionController extends TodoyuActionController {

	protected $sword;

	public function init(array $params) {
		$this->sword = trim($params['sword']);
	}

	public function userAction(array $params) {
		$results = TodoyuUserFilterDataSource::autocompleteUsers($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}


	public function projectAction(array $params) {
		$results = TodoyuProjectFilterDataSource::autocompleteProjects($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	public function taskAction(array $params) {
		$results = TodoyuTaskFilterDataSource::autocompleteTasks($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	public function holidayAction(array $params) {
		$results = TodoyuHolidayManager::autocompleteHolidays($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}

	public function holidaysetAction(array $params) {
		$results = TodoyuHolidaySetManager::autocompleteHolidaySet($this->sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}


}

?>