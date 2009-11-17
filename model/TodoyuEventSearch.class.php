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
 * Event search
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuEventSearch implements TodoyuSearchEngineIf {

	/**
	 * Search project in fulltext mode. Return the ID of the matching projects
	 *
	 * @param	Array		$find		Keywords which have to be in the projects
	 * @param	Array		$ignore		Keywords which must not be in the project
	 * @param	Integer		$limit
	 * @return	Array		Project IDs
	 */
	public static function searchEvents(array $find, array $ignore = array(), $limit = 100) {
		$table	= 'ext_calendar_event';
		$fields	= array('title', 'description', 'place');

		return TodoyuSearch::searchTable($table, $fields, $find, $ignore, $limit);
	}



	/**
	 * Get search results for events
	 *
	 * @param	Array		$find
	 * @param	Array		$ignore
	 * @param	Integer		$limit
	 * @return	Array
	 */
	public static function getResults(array $find, array $ignore = array(), $limit = 100) {
		return array();
	}



	/**
	 * Get suggestions data array for event search
	 *
	 * @param	Array		$find
	 * @param	Array		$ignore
	 * @param	Integer		$limit
	 * @return	Array
	 */
	public static function getSuggestions(array $find, array $ignore = array(), $limit = 5) {
		$limit			= intval($limit);
		$suggestions	= array();

		$eventIDs		= self::searchEvents($find, $ignore, $limit);

			// Get comment details
		if( sizeof($eventIDs) > 0 ) {
			$fields	= '	e.id,
						e.date_start,
						e.date_end,
						e.title,
						e.description';
			$table	= '	ext_calendar_event e';
			$where	= '	e.id IN(' . implode(',', $eventIDs) . ')';
			$order	= '	e.date_start DESC';

			$events	= Todoyu::db()->getArray($fields, $table, $where, '', $order);

			foreach($events as $event) {
				$suggestions[] = array(
					'labelTitle'=> TodoyuTime::format($event['date_start'], 'datetime') . ': ' . $event['title'],
					'labelInfo'	=> TodoyuDiv::getSubstring($event['description'], $find[0], 20, 30, false),
					'title'		=> TodoyuTime::format($event['date_start'], 'datetime') . ' - ' . TodoyuTime::format($event['date_end'], 'datetime'),
					'onclick'	=> 'location.href=\'?ext=calendar&amp;tab=view&amp;event=' . $event['id'] . '\''
				);
			}
		}

		return $suggestions;
	}
}

?>