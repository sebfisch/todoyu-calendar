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
 * Event
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
*/

class TodoyuEvent extends TodoyuBaseObject {

	/**
	 * Initialize event
	 *
	 * @param	Integer		$idEvent
	 */
	public function __construct($idEvent) {
		parent::__construct($idEvent, 'ext_calendar_event');
	}


	public function getStartDate() {
		return $this->get('date_start');
	}




	/**
	 * Sets event to deleted
	 *
	 * @todo	Wrong place for this function!
	 */
	public function delete()	{
		$idEvent = intval($this->get('id'));

		$update = array('deleted'	=> 1);

		Todoyu::db()->doUpdate('ext_calendar_event', 'id = ' . $idEvent, $update);
		Todoyu::db()->doDelete('ext_calendar_mm_event_user', 'id_event = ' . $idEvent);

		TodoyuCache::removeRecord('Event', $idEvent);
	}



	/**
	 * caches event
	 *
	 */
	public function cache()	{
		$cacheClassKey = TodoyuCache::makeClassKey('TodoyuEvent', intval($this->get('id')));
		TodoyuCache::set($cacheClassKey, $this);
	}



	/**
	 * Load event foreign data (assigned users)
	 *
	 */
	public function loadForeignData()	{
		$this->data['user'] = TodoyuEventManager::getAssignedUsersOfEvent($this->id, true);
	}


	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}
}

?>