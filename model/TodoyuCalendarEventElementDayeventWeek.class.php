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
 * Dayevent element for week view
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarEventElementDayeventWeek extends TodoyuCalendarEventElementWeek {

	/**
	 * Base event element
	 *
	 * @var	TodoyuCalendarEventElementWeek
	 */
	protected $eventElement;



	/**
	 * Initialize
	 *
	 * @param	TodoyuCalendarEventElementWeek	$eventElement
	 */
	public function __construct(TodoyuCalendarEventElementWeek $eventElement) {
		parent::__construct($eventElement->getEvent(), $eventElement->getView());

		$this->eventElement	= $eventElement;

		$this->addClass('dayevent');
	}



	/**
	 * Get element template data
	 *
	 * @param TodoyuDayRange $range
	 * @return Array
	 */
	protected function getElementTemplateData(TodoyuDayRange $range) {
		$data		= parent::getElementTemplateData($range->getStart());
		$days		= $this->getView()->getRange()->getOverlappingRange($this->getEvent()->getRange())->getAmountOfDays();
		$dayWidth	= $this->getView()->isWeekendDisplayed() ? 88 : 124;
		$realWitdh	= ($days * $dayWidth) - 6;


		$data['titleCropLength']= TodoyuCalendarPreferences::isWeekendDisplayed() ? 11 : 16;
		$data['width']			= $realWitdh;

		return $data;
	}



	/**
	 * Get template path
	 *
	 * @return	String
	 */
	protected function getTemplate() {
		return 'ext/calendar/view/event/dayevent.tmpl';
	}



	/**
	 * Get template data
	 *
	 * @param	TodoyuDayRange		$range		Week in where the elements is rendered
	 * @return	Array
	 */
	public function getTemplateData(TodoyuDayRange $range) {
		$elementTemplateData= $this->getElementTemplateData($range);
		$eventTemplateData	= $this->getEvent()->getTemplateData(true);

		return array_merge($elementTemplateData, $eventTemplateData);
	}



	/**
	 * Render dayevent for a range
	 *
	 * @param	TodoyuDayRange		$range
	 * @return	String
	 */
	public function render(TodoyuDayRange $range) {
		$data	= $this->getTemplateData($range);
		$tmpl	= $this->getTemplate();

		return Todoyu::render($tmpl, $data);
	}

}

?>