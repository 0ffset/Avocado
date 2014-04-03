<?php
/*
 * A calendar day, presumably to be part of a graphical calendar
 */
class CCalendarDay extends DateTime
{
	// inherits properties from DateTime class

	/*
	 *	Constructor, creates an object with DateTime properties
	 */
	public function __construct($dateString) {
		parent::__construct($dateString);
	}
	
	/*
	 * Check if this calendar day is today
	 *
	 * @return boolean
	 */
	public function isToday() {
		if ($this->format("Y-m-d") == date("Y-m-d")) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/*
	 * Check if this calendar day is a holiday
	 *
	 * @return boolean
	 */
	public function isHoliday() {
		$conditions[] = $this->format("N") == 7;
		
		if (in_array(false, $conditions, false)) {
			return false;
		}
		else {
			return true;
		}
	}
}