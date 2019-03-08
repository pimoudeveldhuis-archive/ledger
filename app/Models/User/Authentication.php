<?php
/**
 * Authentication model
 *
 * Basic model for authentication containing
 *     the user that owns it, a authentication
 *     type (i.e.: password, or facebook) and
 *     the data (i.e.: a hash for the password
 *     or a facebook username/token)
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Authentication
 */
class Authentication extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_authentications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Retrieve the data attribute
     *
     * @param string $value
     * @return mixed
     */
    public function getDataAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return decrypt($value);
    }

    /**
     * Save the data attribute
     *
     * @param mixed $value
     * @return void
     */
    public function setDataAttribute($value)
    {
        // Encrypt the value
        $this->attributes['data'] = encrypt($value);
    }
}
