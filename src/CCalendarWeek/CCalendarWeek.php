<?php
/*
 * A calendar week containing 7 calendar days, presumably to be part of a graphical calendar
 */
class CCalendarWeek extends CCalendarDay
{
	/*
	 * Properties
	 */
	protected $year;
	protected $weekNo;
	protected $datesThisWeek = array();
	
	/*
	 * Constructor
	 *
	 * @param string $weekNo week representation "01"-"53"
	 * @param string $year year
	 */
	public function __construct($weekNo = "", $year = "") {
		if ($weekNo == "") $weekNo = date("W");
		if ($year == "") $year = date("Y");
		
		// to make up for issues around new years; "00" is sent from CalenderMonth if first week of year is w52 or w53
		if ($weekNo == "00") {
			$year--;
			$lastDayPrevYear = strtotime("$year-12-31");
			$weekNo = date("W", $lastDayPrevYear);
		}
		
		for ($day = 1; $day <= 7; $day++) {
			$timestamp = strtotime("{$year}W{$weekNo}{$day}");
			
			$dayOfWeek = date("l", $timestamp);
			$date = date("Y-m-d", $timestamp);
			$this->datesThisWeek[$dayOfWeek] = new CCalendarDay($date);
		}
		
		$this->weekNo = $weekNo;
		$this->year = $year;
	}
	
	/*
	 * Get the individual dates this week in an array
	 */
	public function getDatesThisWeek() {
		return $this->datesThisWeek;
	}
}