<?php
/**
 * Configuration helper
 *
 * Helper that handles the retrieval and storage
 *     of configurations. They are primarily stored
 *     in the database, but it also uses the Cache
 *     Facade for local caching.
 *
 * @package App\Helpers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Helpers;

/**
 * Class ConfigurationHelper
 */
class ConfigurationHelper
{
    /**
     * Retrieve a configuration by key. It returns a string,
     *     boolean or array.
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        // Return the item from the cache facade, or retrieve it from the database and cache it for 60 minutes
        return \Illuminate\Support\Facades\Cache::remember('config-'.$key, 60, function () use ($key) {
            $configuration = \App\Models\Configuration::where('key', $key)->first();
            return $configuration->value;
        });
    }

    /**
     * Store a configuration. This can either be a string,
     *     boolean or array.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        // Update or create the model
        \App\Models\Configuration::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Empty the key from the cache
        \Illuminate\Support\Facades\Cache::forget('config-'.$key);
    }
}
