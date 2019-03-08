<?php
/**
 * Configuration model
 *
 * User specific configurations
 *
 * @package App\Models\User
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Configuration
 */
class Configuration extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_configurations';

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
     * Retrieve the value attribute
     *
     * @param string $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return decrypt($value);
    }

    /**
     * Save the value attribute
     *
     * @param mixed $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        // Encrypt the value
        $this->attributes['value'] = encrypt($value);
    }
}
