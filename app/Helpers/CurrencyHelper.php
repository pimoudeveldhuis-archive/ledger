<?php
/**
 * CurrencyHelper
 *
 * Handles currency specific functionalities as displaying amounts
 *     and retrieving the correct currency_id with a code.
 *
 * @package App\Helpers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */

namespace App\Helpers;

/**
 * Class CurrencyHelper
 */
class CurrencyHelper
{
    /**
     * Get the currency row ID from a given shortcode.
     *
     * @return string|null
     */
    public static function getId($code)
    {
        // Make sure the shortcode ain't null, if so, just return null
        if ($code === null) {
            return null;
        }

        $currency = null;

        // If the currency exists in the cached list, assign it to the variable
        if (isset(self::list()[$code])) {
            $currency = self::list()[$code];
        }

        // If it's not null, return the ID, else return null
        if ($currency !== null) {
            return $currency->id;
        }

        return null;
    }

    /**
     * Return the cached list of currencies. If the cache is empty or older then
     *     60 minutes, retrieve all currencies from the database, store them into
     *     the array and cache that array.
     *
     * @return array
     */
    public static function list()
    {
        return \Illuminate\Support\Facades\Cache::remember('currencies', 60, function () {
            $currencies = [];
            
            // Making sure that there actually are currencies
            if (\App\Models\Currency::count() > 0) {
                foreach (\App\Models\Currency::get() as $currency) {
                    // Assign the currency to the array with the shortcode (e.g.: EUR) as key
                    $currencies[$currency->code] = $currency;
                }
            }

            return $currencies;
        });
    }

    /**
     * Converts user input to a storable amount.
     *
     * @param string $currency_code Shortcode like EUR
     * @param string $amount Amount from user input
     *
     * @return int|null
     */
    public static function convert($currency_code, $amount)
    {
        if ($currency_code === null) {
            // If the currency code is null, return null
            return null;
        }

        $currency = null;
        if (isset(self::list()[$currency_code])) {
            // If the currency code exists, retrieve the currency
            $currency = self::list()[$currency_code];
        }

        if ($currency !== null) {
            // Remove the currency symbol from the string
            $amount = str_replace($currency->symbol, '', $amount);

            // Remove any whitespaces from the string
            $amount = str_replace(' ', '', $amount);

            // Replace the comma with a dot
            // TODO; Currencies use different decimal seperators, this should be stored and used instead assuming this
            $amount = str_replace(',', '.', $amount);

            // Create the integer from the decimals
            $amount *= pow(10, $currency->decimals);

            // Return the amount as integer
            return (int) $amount;
        }

        // Currency not found, return a null
        return null;
    }

    /**
     * Create a human readable format from a database stored amount.
     *
     * @param string $currency_code Currency code, i.e.: EUR
     * @param integer $amount The amount as stored in the database (as integer, no decimals)
     * @param boolean $negative If this is true then a negative amount should be displayed, false by default
     *
     * @return string|null
     */
    public static function readable($currency_code, $amount, $negative = false)
    {
        // If the currency code is null, just return null
        if ($currency_code === null) {
            return null;
        }

        $currency = null;
        // Check if the currency code exists and if so assign it to $currency
        if (isset(self::list()[$currency_code])) {
            $currency = self::list()[$currency_code];
        }

        if ($currency !== null) {
            // Make a decimal again from the integer
            $amount /= pow(10, $currency->decimals);

            if ($negative === true) {
                // Return the value as a negative number
                return $currency->symbol.' ('.number_format($amount, 2, ',', '.').')';
            } else {
                // Return the value as a positive number
                return $currency->symbol.' '.number_format($amount, 2, ',', '.');
            }
        }

        return null;
    }
}
