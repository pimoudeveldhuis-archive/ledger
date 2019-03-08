<?php
/**
 * Organisation model
 *
 * Contains information about known organisation
 *     to make the transaction overview more readable
 *
 * @package App\Models
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Organisation
 */
class Organisation extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'account_hash',
        'name',
        'description',
    ];
}
