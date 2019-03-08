<?php
/**
 * Tag model
 *
 * Users can add tags to transactions (many-to-many)
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tag
 */
class Tag extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'color',
        'name',
        'conditions',
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
     * Retrieve the conditions attribute
     *
     * @param string $value
     * @return array|null
     */
    public function getConditionsAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return ($value !== null) ? decrypt($value) : null;
    }

    /**
     * Save the conditions attribute
     *
     * @param array|null $value
     * @return void
     */
    public function setConditionsAttribute($value)
    {
        // Encrypt the value
        $this->attributes['conditions'] = ($value !== null) ? encrypt($value) : null;
    }
}
