<?php
/**
 * Bank model
 *
 * A bank model has all information to
 *     identify a bank account.
 *
 * @package App\Models
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Bank
 */
class Bank extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'bic',
        'country',
        'bankcode',
    ];
}
