<?php
/**
 * Cache model
 *
 * Some database heavy data retrievals are cached in a JSON
 *     string that is stored in the database.
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Cache
 */
class Cache extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_caches';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',

        'key',
        'value',
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
     * Returns the user the cache belongs to
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Retrieve the name attribute
     *
     * @param string $value
     * @return string
     */
    public function getValueAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the name attribute
     *
     * @param string $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        // Seal $value and save it
        $this->attributes['value'] = \EncryptionHelper::seal($this->user->publickey, $value);
    }
}
