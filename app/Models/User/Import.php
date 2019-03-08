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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Class Import
 */
class Import extends Model
{
    use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_imports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'import_configuration_id',
        'filename',
        'errors',
        'processed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'import_configuration_id' => 'id',
        'processed' => 'boolean',
    ];

    /**
     * Retrieve the filename attribute
     *
     * @param string $value
     * @return string
     */
    public function getFilenameAttribute($value)
    {
        // Decrypt and return the value
        return decrypt($value);
    }

    /**
     * Save the filename attribute
     *
     * @param mixed $value
     * @return void
     */
    public function setFilenameAttribute($value)
    {
        // Encrypt the value
        $this->attributes['filename'] = encrypt($value);
    }

    /**
     * Retrieve the errors attribute
     *
     * @param string $value
     * @return array
     */
    public function getErrorsAttribute($value)
    {
        // Decrypt and return the value, this could be a string, but also a boolean, array etc.
        return decrypt($value);
    }

    /**
     * Save the errors attribute
     *
     * @param mixed $value
     * @return void
     */
    public function setErrorsAttribute($value)
    {
        // Encrypt the value
        $this->attributes['errors'] = encrypt($value);
    }

    /**
     * The configurationmodel for the import
     *
     * @return \App\Models\ImportConfiguration
     */
    public function importConfiguration()
    {
        return $this->belongsTo('App\Models\ImportConfiguration');
    }

    /**
     * User that requested the import
     *
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Returns the file from storage or null if
     *     file does not exists
     *
     * @return Illuminate\Support\Facades\Storage|null
     */
    public function getFileAttribute()
    {
        // Check if the file exists in storage, if so return it
        if (Storage::exists('imports/'. $this->user->id .'/'. $this->uuid .'/'. $this->filename)) {
            return Storage::get('imports/'. $this->user->id .'/'. $this->uuid .'/'. $this->filename);
        }

        // If file did not exists, return null
        return null;
    }
}
