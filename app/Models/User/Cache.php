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
     * Retrieve the value attribute
     *
     * @param string $value
     * @return string
     */
    public function getValueAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the value attribute
     *
     * @param string $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if (config('app.debug') === true) {
            $this->attributes['value'] = $value;
        } else {
            $this->attributes['value'] = \EncryptionHelper::seal($this->publickey, $value);
        }
    }
}
