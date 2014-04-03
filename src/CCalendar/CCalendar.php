<?php
/*
 * Renders a CCalenderMonth object as a graphical calendar
 */
class CCalendar extends CCalendarMonth
{
	/*
	 * Properties
	 */
	private $calendarHTML;
	private $month;
	
	/*
	 * Constructor
	 *
	 * @param string $month month representation "01"-"12" or "jan"-"dec"
	 * @param string $year year
	 */
	public function __construct($month = "", $year = "") {		
		if ($month == "") {
			$month = isset($_GET['month']) && $_GET['month'] <= 12 && $_GET['month'] >= 1 ? $_GET['month'] : date("m");
		}
		if ($year == "") {
			$year = isset($_GET['year']) && is_numeric($_GET['year']) ? $_GET['year'] : date("Y");
		}
		
		$this->month = new CCalendarMonth($month, $year);
	}
	
	/*
	 * Render a graphical calendar as HTML and print it
	 */
	public function renderCalendar() {		
		$this->calendarHTML = "<table class='calendar'>\n<tr>\n<th>w</th>\n";
		for ($i = 0; $i < 7; $i++) {
			$this->calendarHTML .= "<th>".date("D", strtotime("monday this week +$i days"))."</th>\n";
		}
		$this->calendarHTML .= "</tr>\n";
		
		foreach ($this->month->weeksThisMonth as $week) {
			$this->calendarHTML .= "<tr>\n<td class='week'>".$week->weekNo."</td>\n";
			foreach ($week->datesThisWeek as $day) {
				$isTodayClass = $day->isToday() ? " today" : "";
				$isHolidayClass = $day->isHoliday() ? " holiday" : "";
				$isThisMonth = $this->isThisMonth($day) ? "" : " not-this-month";
				
				$this->calendarHTML .= "<td class='calendar-day{$isTodayClass}{$isHolidayClass}{$isThisMonth}'>".$day->format("j")."</td>\n";
			}
			$this->calendarHTML .= "</tr>\n";
		}
		
		$this->calendarHTML .= "</table>";
		
		return $this->calendarHTML;
	}
	
	/*
	 * Render a div with links to navigate between months and years
	 */
	public function renderMonthYearNav() {
		$month = $this->month->monthNo;
		$year = $this->month->year;

		$prevMonth = strtolower(date("m", strtotime("$year-$month-01 - 1 month")));
		$prevYear = strtolower(date("Y", strtotime("$year-$month-01 - 1 month")));
		$nextMonth = strtolower(date("m", strtotime("$year-$month-01 + 1 month")));
		$nextYear = strtolower(date("Y", strtotime("$year-$month-01 + 1 month")));
		
		$navHTML = "<div class='calendar-nav'>\n";
		$navHTML .= "<p><a href='?month=$prevMonth&amp;year=$prevYear'>&lt;</a> <span class='month-name'>".$this->month->monthName."</span> <a href='?month=$nextMonth&amp;year=$nextYear'>&gt;</a></p>\n";
		$navHTML .= "<p><a href='?month=$month&amp;year=".($year-1)."'>&lt;</a> <span class='year-no'>$year</span> <a href='?month=$month&amp;year=".($year+1)."'>&gt;</a></p>\n";
		$navHTML .= "</div>";
		
		return $navHTML;
	}
	
	/*
	 * Check if date is of this month
	 */
	private function isThisMonth($day) {
		if ($day->format("m") == $this->month->monthNo) {
			return true;
		}
		else {
			return false;
		}
	}
}