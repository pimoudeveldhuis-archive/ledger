<?php
/**
 * UserHelper
 *
 * Handles user specific actions that are needed system-wide.
 *
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */
namespace App\Helpers;

/**
 * Class UserHelper
 */
class UserHelper
{
    /**
     * Find a user by email address. A special function is made because the email is
     *     encrypted and MySQL can't search through encrypted data. Therefor a SHA256
     *     string is generated for each email as well and when a match is found it also
     *     doublechecks with now decrypted email address to make sure it's identical and
     *     not a SHA256 collision.
     *
     * @param string $email The emailaddress
     * @return \App\Models\User|null The user instance (if found)
     */
    public static function find($email)
    {
        // Search a user via the email_hash
        $users = \App\Models\User::where('email_hash', hash('sha256', $email))->get();

        // Loop through the result(s), which is hopefully just one result,
        //      but in case of multiple collisions this might be multiple.
        foreach ($users as $user) {
            if ($user->email === $email) {
                // If the email is identical then return the user object
                return $user;
            }
        }
        
        return null;
    }
}
