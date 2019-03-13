<?php
/**
 * Organisation model
 *
 * User can add organisations to make the transactional
 *     overview more readable
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Organisation
 */
class Organisation extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_organisations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'account',
        'account_hash',
        'name',
        'description',
    ];
    
    /**
     * Retrieve the account attribute
     *
     * @param string $value
     * @return string
     */
    public function getAccountAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the account attribute
     *
     * @param string $value
     * @return void
     */
    public function setAccountAttribute($value)
    {
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if (config('app.debug') === true) {
            $this->attributes['account'] = $value;
        } else {
            $this->attributes['account'] = \EncryptionHelper::seal($this->publickey, $value);
        }
    }
    
    /**
     * Retrieve the name attribute
     *
     * @param string $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
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
    public function setNameAttribute($value)
    {
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if (config('app.debug') === true) {
            $this->attributes['name'] = $value;
        } else {
            $this->attributes['name'] = \EncryptionHelper::seal($this->publickey, $value);
        }
    }
    
    /**
     * Retrieve the description attribute
     *
     * @param string $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        // If the application is running in debug mode, or there secretkey session is empty then return the raw value
        if (config('app.debug') === true || session('secretkey') === null) {
            return $value;
        }
    
        // Unseal the encrypted value and return it
        return \EncryptionHelper::unseal(session('secretkey'), $value);
    }

    /**
     * Save the description attribute
     *
     * @param string $value
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        // If the app is in debug mode store the raw string, if not seal the string with the public key
        if (config('app.debug') === true) {
            $this->attributes['description'] = $value;
        } else {
            $this->attributes['description'] = \EncryptionHelper::seal($this->publickey, $value);
        }
    }
}
