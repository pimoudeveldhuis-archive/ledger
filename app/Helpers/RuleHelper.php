<?php
/**
 * RuleHelper
 *
 * Applies the rules to a condition.
 *
 * @package App\Helpers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Helpers;

/**
 * Class RuleHelper
 */
class RuleHelper
{
    /**
     * Apply the $conditions to the $transaction. The function
     *     return false if no rule applied or a blocking rule
     *     was matching, and true if no blocking rule is matching
     *     but a non blocking rule is.
     *
     * @param \App\Models\Transaction $transaction
     * @param \Illuminate\Support\Collection $conditions
     *
     * @return boolean
     */
    public static function check($transaction, $conditions)
    {
        if ($conditions !== null && count($conditions) > 0) {
            // Default false
            $result = false;

            foreach ($conditions as $condition) {
                // Run through all conditions
                
                if ($condition->type === 'account_match'
                    && $transaction->account->account === $condition->data
                ) {
                    $result = true;
                } elseif ($condition->type === 'account_match_blocking'
                    && $transaction->account->account !== $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'account_not_match_blocking'
                    && $transaction->account->account === $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'contra_account_match'
                    && $transaction->contra_account === $condition->data
                ) {
                    $result = true;
                } elseif ($condition->type === 'contra_account_not_match_blocking'
                    && $transaction->contra_account === $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'contra_account_name_match'
                    && $transaction->contra_account_name === $condition->data
                ) {
                    $result = true;
                } elseif ($condition->type === 'contra_account_name_contains'
                    && strpos($transaction->contra_account_name, $condition->data) !== false
                ) {
                    $result = true;
                } elseif ($condition->type === 'contra_account_name_not_contains_blocking'
                    && strpos($transaction->contra_account_name, $condition->data) !== false
                ) {
                    return false;
                } elseif ($condition->type === 'amount_smaller_blocking'
                    && $transaction->amount > $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'amount_bigger_blocking'
                    && $transaction->amount < $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'type_match'
                    && $transaction->type === $condition->data
                ) {
                    $result = true;
                } elseif ($condition->type === 'type_no_match_blocking'
                    && $transaction->type === $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'dw_match'
                    && $transaction->dw === $condition->data
                ) {
                    $result = true;
                } elseif ($condition->type === 'dw_not_match_blocking'
                    && $transaction->dw === $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'currency_match_blocking'
                    && $transaction->currency->code !== $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'currency_not_match_blocking'
                    && $transaction->currency->code === $condition->data
                ) {
                    return false;
                } elseif ($condition->type === 'description_contains'
                    && strpos($transaction->description, $condition->data) !== false
                ) {
                    $result = true;
                } elseif ($condition->type === 'description_not_contains_blocking'
                    && strpos($transaction->description, $condition->data) !== false
                ) {
                    return false;
                } elseif ($condition->type === 'reference_match'
                    && $transaction->reference === $condition->data
                ) {
                    $result = true;
                } elseif ($condition->type === 'reference_not_match_blocking'
                    && $transaction->reference === $condition->data
                ) {
                    return false;
                }
            }

            return $result;
        }

        return false;
    }
}
