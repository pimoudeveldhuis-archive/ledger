<?php
/**
 * Date helper
 *
 * Helper that handles the date and time functionalities
 *     of the application.
 *
 * @package App\Helpers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Helpers;

/**
 * Class DateHelper
 */
class DateHelper
{
    /**
     * Returns the current year
     *
     * @return string
     */
    public static function currentYear()
    {
        return date('Y');
    }

    /**
     * Returns the current month
     *
     * @return string
     */
    public static function currentMonth()
    {
        return date('n');
    }

    /**
     * Returns a Carbon object with the first
     *     day of the previous month.
     *
     * @return \Carbon\Carbon
     */
    public static function previousMonth()
    {
        return \Carbon\Carbon::now()->subMonth()->startOfMonth();
    }

    /**
     * Returns the name of the previous month and
     *     year by using the language library.
     *
     * @return string
     */
    public static function displayPreviousMonth()
    {
        return __(
            'date.months.' . self::getMonthName(self::previousMonth()->format('n'))
        ) .' '. self::previousMonth()->format('Y');
    }

    /**
     * Returns an array with years (the year is both
     *     the key and value). The first and last year
     *     can be provided as arguments, where the last
     *     year is optional. If it is not given it will
     *     use the current year as final.
     *
     * @param int $start First year (i.e.: 2012)
     * @param int $end Last year (i.e.: 2018)
     *
     * @return array
     */
    public static function getYearsArray($start, $end = null)
    {
        $years = [];

        if ($end === null) {
            // If there is no last year, use the current year as last year
            $end = self::currentYear();
        }

        for ($year = $start; $year <= $end; $year++) {
            $years[(int) $year] = (int) $year;
        }

        return $years;
    }

    /**
     * Returns an array of months. A start and end month can be given,
     *     but if the first month is null then it assume Januari and
     *     if the last month is null it assumes December. Without arguments
     *     this function will return an array from Januari to December.
     *
     * @param int $start
     * @param int $end
     *
     * @return array
     */
    public static function getMonthsArray($start = null, $end = null)
    {
        $months = [];

        if ($start === null) {
            $start = 1;
        }

        if ($end === null) {
            $end = 12;
        }

        for ($month = $start; $month <= $end; $month++) {
            $months[(int) $month] = (int) $month;
        }

        return $months;
    }

    /**
     * Returns the $amount number of months in the past. So $amount = 6
     *     will return an array with the past 6 months. A $skip argument
     *     is optional to skip a number of months, so when it's December
     *     2019 and getLastMonths(3, 2) is requested it will skip 2 months
     *     and therefor will return July, August and September. Next to the
     *     $dates there is also a $range item that contains the start and
     *     end dates as Carbon object to be used in queries as date range.
     *
     * @param int $amount Amount of months to be loaded
     * @param int $skip Amount of months to skip before loading them
     *
     * @return array
     */
    public static function getLastMonths($amount, $skip = 0)
    {
        $range = [];
        $dates = [];

        // Get current year and previous month (getLastMonths(1, 0) will return the past month)
        $year = (int) date('Y');
        $month = (int) (date('n') - 1);
   
        for ($i = 0; $i < ($skip + $amount); $i++) {
            if ($month <= 0) {
                // If we go a month back from Januari it should become December of the previous year
                $month = 12;
                $year--;
            }
            
            // Once the iterator is past the number of months to skip, start saving them
            if ($i >= $skip) {
                $dates[$year.'.'.$month] = 0;

                if (!isset($range['end'])) {
                    // If there is no end yet, save it
                    $range['end'] = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0);
                }
                
                // Save the start date
                $range['start'] = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0);
            }

            $month--;
        }

        if (isset($range['end'])) {
            // The ending date range should be the full month, not the first day of the month
            //     so we add a month, and substract a second
            $range['end']->addMonth()->subSecond();
        }
        
        // Reverse the date array to make it chronological
        $dates = array_reverse($dates, true);

        // Return the dates and the range in an array, so they can be listed
        return [$dates, $range];
    }

    /**
     * Returns an array with the previous years as key. Next to the $amount it
     *     is also possible to give a $skip argument which will make it skip
     *     that number of years. So if it's 2018, getLastYears(2, 2) will return
     *     an array with [2015, 2016]. It will also return a range of two Carbon
     *     date objects to be used in queries.
     *
     * @param int $amount The number of years
     * @param int $skip The number of years to skip
     *
     * @return array
     */
    public static function getLastYears($amount, $skip = 0)
    {
        $range = [];
        $dates = [];

        $year = (int) date('Y');
   
        for ($i = 0; $i < ($skip + $amount); $i++) {
            // Once the iterator is past the number of years to skip, start saving them
            if ($i >= $skip) {
                $dates[$year] = 0;

                if (!isset($range['end'])) {
                    // If there is no end yet, save it
                    $range['end'] = \Carbon\Carbon::create($year, 1, 1, 0, 0, 0);
                }
                
                // Save the start date
                $range['start'] = \Carbon\Carbon::create($year, 1, 1, 0, 0, 0);
            }

            $year--;
        }

        if (isset($range['end'])) {
            // The ending date range should be the full year, not the first day of the year
            //     so we add a year, and substract a second
            $range['end']->addYear()->subSecond();
        }

        // Reverse the date array to make it chronological
        $dates = array_reverse($dates, true);

        // Return the dates and the range in an array, so they can be listed
        return [$dates, $range];
    }

    /**
     * Returns a abbriviated month name string
     *     based on the given integer.
     *
     * @param int $n Month number (Januari = 1, Februari = 2, etc.)
     * @return string
     */
    public static function getMonthName($n)
    {
        switch ($n) {
            case 1:
                return 'jan';
            break;
            case 2:
                return 'feb';
            break;
            case 3:
                return 'mar';
            break;
            case 4:
                return 'apr';
            break;
            case 5:
                return 'may';
            break;
            case 6:
                return 'jun';
            break;
            case 7:
                return 'jul';
            break;
            case 8:
                return 'aug';
            break;
            case 9:
                return 'sep';
            break;
            case 10:
                return 'okt';
            break;
            case 11:
                return 'nov';
            break;
            case 12:
                return 'dec';
            break;

            default:
                return $n;
            break;
        }
    }
}
