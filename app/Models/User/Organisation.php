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
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
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
        // Seal $value and save it
        $this->attributes['account'] = \EncryptionHelper::seal($this->user->publickey, $value);
    }
    
    /**
     * Retrieve the name attribute
     *
     * @param string $value
     * @return string
     */
    public function getNameAttribute($value)
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
    public function setNameAttribute($value)
    {
        // Seal $value and save it
        $this->attributes['name'] = \EncryptionHelper::seal($this->user->publickey, $value);
    }
    
    /**
     * Retrieve the description attribute
     *
     * @param string $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        // If there is no secret key in session, return the encrypted value
        if (session('secretkey') === null) {
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
        // Seal $value and save it
        $this->attributes['description'] = \EncryptionHelper::seal($this->user->publickey, $value);
    }
}
