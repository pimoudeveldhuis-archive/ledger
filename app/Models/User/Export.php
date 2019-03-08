<?php
/**
 * Export model
 *
 * Stores a request for an export. Contains
 *     a unique UUID, the user that requested
 *     the export and a boolean whether the
 *     export has been processed.
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Export
 */
class Export extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_exports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'processed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'processed' => 'boolean',
    ];
}
