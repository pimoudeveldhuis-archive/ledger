<?php
/**
 * EncryptionHelper
 *
 * This helper handles both the encryption and decryption of data with
 *     a key and the encryption and decryption of data using a public
 *     and private keypair. With the seal and unseal functions the app
 *     can encrypt data without the used logged in, which only the user
 *     itself can decrypt.
 *
 * @package App\Helpers
 * @author Pim Oude Veldhuis <pim@odvh.nl>
 * @license MIT http://www.opensource.org/licenses/mit-license.html
 */

namespace App\Helpers;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Asymmetric\Crypto as Asymmetric;
use ParagonIE\HiddenString\HiddenString;

/**
 * Class EncryptionHelper
 */
class EncryptionHelper
{
    /**
     * Encrypts $data using a $key. The default cipher used is AES-256-CBC,
     *     but an alternative can be used as well. Returns a base64 encoded
     *     string which contains the encrypted data.
     *
     * @param string $key
     * @param string $data
     * @param string $cipher
     *
     * @return string
     */
    public static function encrypt($key, $data, $cipher = 'AES-256-CBC')
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        $encrypted_data = openssl_encrypt($data, $cipher, $key, 0, $iv);

        return base64_encode($encrypted_data . '::' . $iv);
    }

    /**
     * Decrypts the data that is encrypted using the encrypt function. The
     *     $data argument should be a base64_encoded string containing the
     *     encrypted data. The $key should obviously be the same as the data
     *     was encrypted with and the same counts for the $cipher, which by
     *     default uses a AES-256-CBC encryption. It returns the decrypted data
     *     which can be anything from a string to an array.
     *
     * @param string $key
     * @param string $data
     * @param string $cipher
     *
     * @return mixed
     */
    public static function decrypt($key, $data, $cipher = 'AES-256-CBC')
    {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv);
    }

    /**
     * Creates a random string. This can for example be used for a recovery key.
     *     The only required argument is the length of the key, with an optional
     *     argument that defines the keyspace. This defaults in 0-9a-zA-Z.
     *
     * @param int $length
     * @param string $keyspace
     *
     * @return string
     */
    public static function randomString(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    /**
     * Generate a public/private keypair and return it in a listable array.
     *
     * @return array
     */
    public static function generateKeyPair()
    {
        $keypair = KeyFactory::generateEncryptionKeyPair();

        return [
            'public' => KeyFactory::export($keypair->getPublicKey())->getString(),
            'secret' => KeyFactory::export($keypair->getSecretKey())->getString()
        ];
    }

    /**
     * Encrypts the data using a public key. Both the $publickey and $data
     *     should always be a string. It will return the encrypted string.
     *
     * @param string $publickey
     * @param string $data
     *
     * @return string
     */
    public static function seal($publickey, $data)
    {
        return Asymmetric::seal(
            new HiddenString($data),
            KeyFactory::importEncryptionPublicKey(new HiddenString($publickey))
        );
    }

    /**
     * Decrypts an encrypted string using the private key. Both the $secretkey
     *     and $data should always be a string. It will return the original text.
     *
     * @param string $secretkey
     * @param string $data
     *
     * @return string
     */
    public static function unseal($secretkey, $data)
    {
        return Asymmetric::unseal(
            new HiddenString($data),
            KeyFactory::importEncryptionSecretKey(new HiddenString($secretkey))
        )->getString();
    }
}
