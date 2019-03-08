<?php
/**
 * ImportConfiguration model
 *
 * Stores the configuration data for an import
 *     configuration. This data contains everything
 *     needed to extract the transaction information
 *     from the .txt or .csv the bank exports
 *
 * @package  App\Models
 * @author   Pim Oude Veldhuis <pim@odvh.nl>
 * @license  MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ImportConfiguration
 */
class ImportConfiguration extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
    ];
}
