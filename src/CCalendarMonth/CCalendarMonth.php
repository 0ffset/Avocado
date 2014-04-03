<?php
/*
 * A calendar month containing all its calendar weeks, presumably to be part of a graphical calendar
 */
class CCalendarMonth extends CCalendarWeek
{
	/*
	 * Properties
	 */
	protected $year;
	protected $monthNo;
	protected $monthName;
	protected $weeksThisMonth = array();
	
	/*
	 * Constructor
	 *
	 * @param string $monthNo month representation "01"-"12"
	 * @param string $year year
	 */
	public function __construct($monthNo = "", $year = "") {
		if ($monthNo == "") $monthNo = date("M");
		if ($year == "") $year = date("Y");
		
		$firstWeekNoOfMonth = intval(date("W", strtotime("{$year}-{$monthNo}-01")));
		$lastDayNoOfMonth = intval(date("t", strtotime("{$year}-{$monthNo}-01")));
		$lastWeekNoOfMonth = intval(date("W", strtotime("{$year}-{$monthNo}-{$lastDayNoOfMonth}")));		
		
		// to make up for issues around new years ("00" will be passed to CalendarWeek class and be solved from there)
		if ($firstWeekNoOfMonth >= 52) $firstWeekNoOfMonth = 0;
		if ($lastWeekNoOfMonth == 1) $lastWeekNoOfMonth = 53;
		
		for ($w = $firstWeekNoOfMonth; $w <= $lastWeekNoOfMonth; $w++) {
			$weekNo = $w < 10 ? "0".$w : $w;
			
			$this->weeksThisMonth[$w] = new CCalendarWeek($weekNo, $year);
		}
		
		list($year, $monthNo) = explode("-", date("Y-m", strtotime("$year-$monthNo-01")));
		
		$this->year = $year;
		$this->monthNo = $monthNo;
		$this->monthName = date("F", strtotime("$year-$monthNo-01"));
	}
	
	/*
	 * Get the individual weeks this month in an array
	 */
	public function getWeeksThisMonth() {
		return $this->weeksThisMonth;
	}
}