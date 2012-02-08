<?php
/****************************************************************************
 * todoyu is published under the BSD License:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Event element base class for day and week view (to share some functions)
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
abstract class TodoyuCalendarEventElementDayWeek extends TodoyuCalendarEventElement {

	/**
	 * Index of the column the event element is rendered
	 * @var	Integer
	 */
	protected $columnIndex	= 0;

	/**
	 * Number of conflicting event in other columns
	 * @var	Integer
	 */
	protected $columnConflicts	= 0;



	/**
	 * Get element template data
	 *
	 * @param	Integer		$date
	 * @return	Array
	 */
	protected function getElementTemplateData($date = 0) {
		$elementData	= parent::getElementTemplateData();

		$elementData['positionStyles']	= $this->getPositionStyleString($date);

		return $elementData;
	}



	/**
	 * Get position style string
	 *
	 * @param	Integer		$date
	 * @return	String
	 */
	protected function getPositionStyleString($date) {
		$styles	= $this->getPositionStyles($date);

		return TodoyuArray::implodeAssoc($styles, ':', ';');
	}



	/**
	 * Get position styles
	 *
	 * @param	Integer		$date
	 * @return	Array
	 */
	protected function getPositionStyles($date) {
		$styles	= array(
			'left'	=> $this->getLeftOffset() . 'px',
			'width'	=> $this->getWidth() . 'px',
			'height'=> $this->getHeight($date, true) . 'px'
		);

		return $styles;
	}



	/**
	 * Get template path
	 *
	 * @return	String
	 */
	protected function getTemplate() {
		return 'ext/calendar/view/event/dayweek.tmpl';
	}



	/**
	 * Get element height
	 *
	 * @param	Integer		$date
	 * @param	Boolean		$assertMinimalDuration		Assert minimal height of element (force duration to 30min)
	 * @return	Integer
	 */
	public function getHeight($date, $assertMinimalDuration = true) {
		$todayRange	= new TodoyuCalendarRangeDay($date);
		$duration	= $this->getEvent()->getRange()->getOverlappingRange($todayRange)->getDuration();

		if( $assertMinimalDuration ) {
			$duration	= TodoyuNumeric::intInRange($duration, CALENDAR_EVENT_MIN_DURATION);
		}

		return intval(round($duration * CALENDAR_HEIGHT_HOUR / TodoyuTime::SECONDS_HOUR, 0));
	}



	/**
	 * Get element width
	 *
	 * @return	Integer
	 */
	public function getWidth() {
		$totalConflictingElements	= $this->getColumnConflicts()+1;

		return intval(round($this->getViewWidth()/$totalConflictingElements, 0));
	}



	/**
	 * Get left offset, depending on the column the element is in
	 *
	 * @return	Integer
	 */
	public function getLeftOffset() {
		return intval($this->getWidth() * $this->getColumnIndex());
	}



	/**
	 * Set column index
	 *
	 * @param	Integer		$columnIndex
	 */
	public function setColumnIndex($columnIndex) {
		$this->columnIndex	= intval($columnIndex);
	}



	/**
	 * Get column index
	 *
	 * @return	Integer
	 */
	public function getColumnIndex() {
		return $this->columnIndex;
	}



	/**
	 * Get conflicting columns count
	 *
	 * @return	Integer
	 */
	public function getColumnConflicts() {
		return $this->columnConflicts;
	}



	/**
	 * @param	TodoyuCalendarEventElementDayWeek[]	$allElements
	 */
	public function setOverlapCounter(array $allElements) {
		$conflictingColumns	= array();

		foreach($allElements as $element) {
			if( $element !== $this ) {
				if( $element->getColumnIndex() !== $this->getColumnIndex() ) {
					if( $this->isOverlapping($element) ) {
						$conflictingColumns[]	= $element->getColumnIndex();
					}
				}
			}
		}

		$this->columnConflicts	= sizeof(array_unique($conflictingColumns));
	}



	/**
	 * Get top offset, depending on the time of day of the event start
	 *
	 * @param	Integer		$date
	 * @return	Integer
	 */
	public function getTopOffset($date) {
		$dateDayStart	= TodoyuTime::getStartOfDay($date);

		if( $this->event->getDateStart() <= $dateDayStart ) {
			return 0;
		} else {
			return TodoyuCalendarManager::getOffsetByDayTime($this->event->getDateStart());
		}
	}



	/**
	 * Get width for associated view. Day or week
	 *
	 * @return	Integer
	 */
	abstract protected function getViewWidth();

}

?>