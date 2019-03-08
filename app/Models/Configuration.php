<?php
/**
 * Configuration model
 *
 * System-wide configuration settings
 *
 * @package App\Models
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Configuration
 */
class Configuration extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Retrieve the value attribute
     *
     * @param string $value
     * @return string|null
     */
    public function getValueAttribute($value)
    {
        // If value isn't null then return it unserialized, else just return null
        return ($value !== null) ? unserialize($value) : null;
    }

    /**
     * Save the value attribute
     *
     * @param string $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        // If value is null, save null, else serialize it
        $this->attributes['value'] = ($value !== null) ? serialize($value) : null;
    }
}
