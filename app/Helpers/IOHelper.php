<?php
/**
 * IOHElper
 *
 * The IOHelper handles all in and out (imports and exports) of the system.
 *
 * @package App\Helpers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Helpers;

/**
 * Class IOHelper
 */
class IOHelper
{
    /**
     * Imports a CSV file. It needs the current $user, a $file and
     *     a $configuration which contains items like the line_delimiter
     *     etc. It will return two arrays in an array of which one is the
     *     array with succesful transactions and the other are the
     *     rows that contained errors.
     *
     * @param \App\Models\User $user
     * @param string $file
     * @param \App\Models\ImportConfiguration $configuration
     *
     * @return array
     */
    public static function import($user, $file, $configuration)
    {
        if ($file !== null) {
            // If the file exists, explode the file into their seperate lines
            //     using the line_delimiter in the configuration
            $lines = explode($configuration->line_delimiter, $file);

            // Create the two result arrays
            $transactions = [];
            $errors = [];

            // Get accounts array
            $accounts = [];
            foreach ($user->accounts as $account) {
                $accounts[$account->account_hash] = $account->id;
            }

            if ($accounts !== null && count($accounts) > 0 && $lines !== null && count($lines) > 0) {
                $i = 0;
                foreach ($lines as $line) {
                    $i++;

                    // Check for a headerline
                    $headerline = false;
                    foreach ($configuration->header_lines as $header_line) {
                        if (strpos($line, $header_line) !== false) {
                            // This is a headerline
                            $headerline = true;
                        }
                    }

                    if ($headerline === false) {
                        // This is not a headerline, retrieve the items from the CSV
                        $line_data = str_getcsv($line, $configuration->column_delimiter, '"');
                        if (count($line_data) === 9) {
                            // Create a hash of the account nr.
                            $account_hashed = hash('sha256', $line_data[$configuration->columns->account]);
                            
                            $transaction = [
                                'user_id' => $user->id,
                                'user_account_id' => (
                                    ($configuration->columns->account !== null) ? (
                                        isset($accounts[$account_hashed]) ? $accounts[$account_hashed] : null
                                    ) : null
                                ),
                                'book_date' => (
                                    ($configuration->columns->book_date !== null) ? \Carbon\Carbon::createFromFormat(
                                        $configuration->date_format,
                                        $line_data[$configuration->columns->book_date]
                                    ) : null
                                ),
                                'type' => (
                                    ($configuration->columns->type !== null)
                                        ? $line_data[$configuration->columns->type] : null
                                ),
                                'description' => (
                                    ($configuration->columns->description !== null)
                                        ? $line_data[$configuration->columns->description] : null
                                ),
                                'reference' => (
                                    ($configuration->columns->reference !== null)
                                        ? $line_data[$configuration->columns->reference] : null
                                ),
                                'contra_account' => (
                                    ($configuration->columns->contra_account !== null) ? (
                                        ($line_data[$configuration->columns->contra_account] !== '')
                                            ? $line_data[$configuration->columns->contra_account] : null
                                    ) : null
                                ),
                                'contra_account_hash' => hash(
                                    'sha256',
                                    (
                                        ($configuration->columns->contra_account !== null) ? (
                                            ($line_data[$configuration->columns->contra_account] !== '')
                                                ? $line_data[$configuration->columns->contra_account] : null
                                        ) : null
                                    )
                                ),
                                'contra_account_name' => (
                                    ($configuration->columns->contra_account_name !== null) ? (
                                        ($line_data[$configuration->columns->contra_account_name] !== '')
                                            ? $line_data[$configuration->columns->contra_account_name] : null
                                    ) : null
                                ),
                                'currency_id' => \CurrencyHelper::getId(
                                    ($configuration->columns->currency !== null)
                                        ? $line_data[$configuration->columns->currency] : $configuration->currency
                                ),

                                'dw' => (
                                    ($configuration->columns->dw !== null) ? (
                                        ($line_data[$configuration->columns->dw] === $configuration->dw->deposit)
                                            ? 'deposit' : (
                                                ($line_data[$configuration->columns->dw]
                                                    === $configuration->dw->withdrawal)
                                                        ? 'withdrawal' : null
                                            )
                                    ) : null
                                ),
                                
                                'duplicate_hash' => hash('sha256', $line),
                            ];

                            if ($configuration->columns->amount !== null) {
                                // Fix the amount
                                $amount = (
                                    ($configuration->columns->amount !== null)
                                        ? $line_data[$configuration->columns->amount] : null
                                );

                                if ($amount !== null) {
                                    if ($configuration->amount_format->prefix !== '') {
                                        // If a prefix exists (i.e.: EUR), search and remove it
                                        $amount = str_replace($configuration->amount_format->prefix, '', $amount);
                                    }

                                    // Remove any thousand separator
                                    $amount = str_replace($configuration->amount_format->thousands_sep, '', $amount);

                                    // Make a dot from the decimal point so PHP can work with it
                                    $amount = str_replace($configuration->amount_format->dec_point, '.', $amount);

                                    // Make an integer from the decimal
                                    $amount *= pow(10, $configuration->amount_format->decimals);
                                }

                                // Round the amount and save it into the transaction
                                $transaction['amount'] = (int) round($amount);
                            }

                            if ($transaction['dw'] === null) {
                                // Could not determine whether the the transaction is a deposit or a withdrawal
                                $errors[] = [
                                    'err' => 'dw_empty',
                                    'line_nr' => $i,
                                    'line' => $line,
                                    'arr' => $transaction
                                ];
                            } elseif ($transaction['currency_id'] === null) {
                                // Could not find the currency
                                $errors[] = [
                                    'err' => 'currency_id_empty',
                                    'line_nr' => $i,
                                    'line' => $line,
                                    'arr' => $transaction
                                ];
                            } elseif ($transaction['user_account_id'] === null) {
                                // Could not find the bank account
                                $errors[] = [
                                    'err' => 'user_account_id_empty',
                                    'line_nr' => $i,
                                    'line' => $line,
                                    'arr' => $transaction
                                ];
                            } elseif ($transaction['book_date'] === null) {
                                // The book date is empty
                                $errors[] = [
                                    'err' => 'book_date_empty',
                                    'line_nr' => $i,
                                    'line' => $line,
                                    'arr' => $transaction
                                ];
                            } elseif ($transaction['type'] === null) {
                                // The transaction type is empty
                                $errors[] = [
                                    'err' => 'type_empty',
                                    'line_nr' => $i,
                                    'line' => $line,
                                    'arr' => $transaction
                                ];
                            } elseif ($transaction['contra_account_name'] === null
                                && ($transaction['contra_account'] === null
                                    || $transaction['contra_account_hash'] === null
                                )
                            ) {
                                // The contra account is empty
                                $errors[] = [
                                    'err' => 'contra_account_empty',
                                    'line_nr' => $i,
                                    'line' => $line,
                                    'arr' => $transaction
                                ];
                            } else {
                                // Everything is oké, add the transaction to the list
                                $transactions[] = $transaction;
                            }
                        }
                    }
                }
            }
            // Return a listable array with the transactions and the errors
            return [$transactions, $errors];
        }
    }
}
