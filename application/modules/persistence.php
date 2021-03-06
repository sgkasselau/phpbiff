<?php
/**
 * Persistence mechanisms.
 *
 * Copyright (c) SG Kassel, 2010. All rights reserved.
 *
 * This file is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE TO ANY PARTY FOR DIRECT, INDIRECT,
 * SPECIAL, INCIDENTAL, OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE USE OF
 * THIS CODE, EVEN IF THE AUTHOR HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * THE AUTHOR SPECIFICALLY DISCLAIMS ANY WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE.  THE CODE PROVIDED HEREUNDER IS ON AN "AS IS" BASIS,
 * AND THERE IS NO OBLIGATION WHATSOEVER TO PROVIDE MAINTENANCE,
 * SUPPORT, UPDATES, ENHANCEMENTS, OR MODIFICATIONS.
 *
 * Email: sg_dot_kassel_dot_au_at_gmail_dot_com
 *
 * PHP Version 5.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */

/** Import the application settings. */
require_once(dirname(__FILE__) . '/../Bootstrap.php');

/** Import the hex2bin function. */
require_once(APPLICATION_PATH . '/modules/hex2bin.php');

/**
 * Interface class for key-value persistence.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
interface Persistence {
    // Instance methods.

    /**
     * Function to clear a previously stored value for the given key.
     * Returns TRUE on success, FALSE otherwise.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return bool       Whether the clear operation was successful.
     */
    public function clear($key);

    /**
     * Function to retrieve the stored value for the given key.
     * Returns the stored object, or NULL if the value can't be retrieved.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return mixed      The stored value.
     */
    public function fetch($key);

    /**
     * Function to return whether or not the store contains the given key.
     * Returns TRUE if the key exists in the store; FALSE otherwise.
     *
     * @param string $key The key to be tested.
     *
     * @return bool       Whether the key exists in the store.
     */
    public function hasKey($key);

    /**
     * Function to store the given value under the given key.
     * Returns TRUE on success; FALSE otherwise.
     *
     * @param string $key   The key under which the value is to be stored.
     * @param mixed  $value The value to be stored.
     *
     * @return bool         Whether the store operation was successful.
     */
    public function store($key, $value);
}

/**
 * Concrete class that defines attributes and methods common to all 
 * encrypting persistence methods.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class EncryptedPersistence implements Persistence {
    // Instance attributes.

    /**
     * Cipher type used to encrypt data.
     *
     * @var int
     */
    const CIPHERTYPE = 'serpent';

    /**
     * Encryption resource (for use with mcrypt.)
     *
     * @var mixed
     */
    protected $encryptionResource;

    /**
     * Encryption key to use with this store.
     *
     * @var string
     */
    protected $encryptionKey;

    /**
     * Encryption initialization vector to use with the store.
     *
     * @var int
     */
    protected $encryptionIV;

    /**
     * Encryption mode.
     *
     * @var int
     */
    const ENCRYPTIONMODE = 'ecb';

    /**
     * Whether the encryption key is already hashed.
     *
     * @var bool
     */
    protected $isEncryptionKeyHashed;

    // Instance methods.

    /**
     * Creates a new encrypted store from the given parameters.
     *
     * @param string $key                   The key to use while encrypting data.
     * @param string $isEncryptionKeyHashed Whether the given key is hashed.
     *
     * @return NULL
     */
    public function __construct($key = '', $isEncryptionKeyHashed = FALSE) {
        // Set attributes from the given parameters.
        $this->isEncryptionKeyHashed = $isEncryptionKeyHashed;

        // Open the mcrypt module for the current cipher type and mode.
        $this->encryptionResource = mcrypt_module_open(self::CIPHERTYPE, '',
                                                       self::ENCRYPTIONMODE,
                                                       '');

        // Get an appropriate initialization vector for the current cipher
        // type.
        // (Not strictly necessary for the current ECB mode, but good
        //  cryptographic practice.)
        $ivsize = mcrypt_enc_get_iv_size($this->encryptionResource);
        $this->encryptionIV = mcrypt_create_iv($ivsize, MCRYPT_RAND);

        // Get the key size for the current cipher type.
        $keysize = mcrypt_enc_get_key_size($this->encryptionResource);

        // If necessary, convert the key to a SHA-256 hash for use.
        if (!$this->isEncryptionKeyHashed) {
            $hashedKey = hash('sha256', $key,
                              $raw_output = FALSE);
            // The key will now be stored in its hashed form.
            $this->isEncryptionKeyHashed = TRUE;
        } else {
            $hashedKey = $key;
        }

        // Mangle the hashed key down to the correct keysize, and store it for
        // later.
        $this->encryptionKey = substr($hashedKey, 0, $keysize);

        // Initialize the encryption resource.
        $this->_initializeEncryptionResource();
    }

    /**
     * Destroys the current encrypted store.
     *
     * @return NULL
     */
    public function __destruct() {
        // Close the encryption resource.
        mcrypt_module_close($this->encryptionResource);
    }

    /**
     * Function to deinitialize the encryption resource.
     *
     * @return NULL
     */
    private function _deinitializeEncryptionResource() {
        // Deinitialize the encryption resource.
        mcrypt_generic_deinit($this->encryptionResource);
    }

    /**
     * Function to initialize the encryption resource.
     *
     * @return NULL
     */
    private function _initializeEncryptionResource() {
        // Initialize the module with the store key and the initialization
        //vector.
        mcrypt_generic_init($this->encryptionResource, $this->encryptionKey,
                            $this->encryptionIV);
    }

    /**
     * Function to clear a previously stored value for the given key.
     * Returns TRUE on success, FALSE otherwise.
     * Implementation to be supplied by inheriting classes.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return bool       Whether the clear operation was successful.
     *
     * @abstract
     */
    public function clear($key) {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to decode and decrypt the string returned from encryptData (and
     * stored in an untrusted backend) returning the original binary string.
     *
     * @param string $data The data to decode and decrypt.
     *
     * @return string      The decoded and decrypted binary data.
     */
    public function decryptData($data) {
        // Hex decode the incoming data, as the original should have been
        // hex encoded and encrypted.
        $decodedData = hex2bin($data);

        // Initialize the encryption resource for this pass.
        $this->_initializeEncryptionResource();

        // Now decrypt the decoded data.
        $decryptedData = mdecrypt_generic($this->encryptionResource,
                                          $decodedData);

        // Deinitialize the encryption resource for this pass.
        $this->_deinitializeEncryptionResource();

        // Trim off any null padding added to the end of the string, as this
        // confuses hex2bin.
        $decryptedData = rtrim($decryptedData, "\0");

        // Give the decrypted data another hex decode, as the encoding process
        // should have hex encoded the input binary string before passing it to be
        // encrypted.
        $finalDecryptedData = hex2bin($decryptedData);

        // Return the final decoded and decrypted data.
        return $finalDecryptedData;
    }

    /**
     * Function to encode and encrypt the given potentially untrusted binary
     * data, returning values safe to store in an untrusted backend.
     *
     * @param string $data The binary data to encode and encrypt.
     *
     * @return string      The encoded and encrypted binary data.
     */
    public function encryptData($data) {
        // Hex encode the incoming data, so that we don't hit any problems with
        // null characters.
        $encodedData = bin2hex($data);

        // Initialize the encryption resource for this pass.
        $this->_initializeEncryptionResource();

        // Now encrypt the encoded data.
        $encryptedData = mcrypt_generic($this->encryptionResource,
                                        $encodedData);

        // Deinitialize the encryption resource for this pass.
        $this->_deinitializeEncryptionResource();

        // Give the encrypted data a hex encode, to ensure that any null padding
        // is preserved in the underlying store.
        $finalEncryptedData = bin2hex($encryptedData);

        // Return the final encoded and encrypted data.
        return $finalEncryptedData;
    }

    /**
     * Function to retrieve the stored value for the given key.
     * Returns the stored object, or NULL if the value can't be retrieved.
     * Implementation to be supplied by inheriting classes.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return mixed      The stored value.
     *
     * @abstract
     */
    public function fetch($key) {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to return whether or not the store contains the given key.
     * Returns TRUE if the key exists in the store; FALSE otherwise.
     * Implementation to be supplied by inheriting classes.
     *
     * @param string $key The key to be tested.
     *
     * @return bool       Whether the key exists in the store.
     *
     * @abstract
     */
    public function hasKey($key) {
        throw new RuntimeException("Not implemented by this class.");
    }

    /**
     * Function to store the given value under the given key.
     * Returns TRUE on success; FALSE otherwise.
     * Implementation to be supplied by inheriting classes.
     *
     * @param string $key   The key under which the value is to be stored.
     * @param mixed  $value The value to be stored.
     *
     * @return bool         Whether the store operation was successful.
     *
     * @abstract
     */
    public function store($key, $value) {
        throw new RuntimeException("Not implemented by this class.");
    }
}

/**
 * Concrete class implementing encrypted file-based persistence.
 *
 * Key-value pairs are stored as individual files under the store path.
 * This permits file locking to be used during read and write activities,
 * potentially allowing many simultaneous processes to safely access the store.
 *
 * File names are the hashed keys - file contents are the encrypted
 * serialized version of the stored value.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class EncryptedFilePersistence extends EncryptedPersistence {
    // Instance attributes.

    /**
     * Cache of keys and their filenames.
     *
     * @var array
     */
    protected $filenameCache;

    /**
     * Path to the file store directory.
     *
     * @var store
     */
    protected $storePath;

    // Instance methods.

    /**
     * Creates a new encrypted file store from the given parameters.
     *
     * @param string $key                   The key to use to encrypt the
     *                                      file store.
     * @param string $isEncryptionKeyHashed Whether the given key is hashed.
     * @param string $storePath             The path to the file store.
     */
    public function __construct($key = '', $isEncryptionKeyHashed = FALSE,
                                $storePath = '') {
        // Call the superclass construction method with the given key.
        EncryptedPersistence::__construct($key, $isEncryptionKeyHashed);

        // Set attributes from the given parameters, setting a safe default if
        // not given a valid value.
        if (!realpath($storePath)) {
            $storePath = APPLICATION_PATH . '/data/';
        }

        $this->storePath = $storePath;

        // Set up the filename cache.
        $this->filenameCache = array();
    }

    /**
     * Function to return the base filename used to store the value data under
     * the given key.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return string     The base filename (without path).
     */
    private function _generateFilenameFromKey($key) {
        // Check the filename cache for the existance of this key first.
        if (!isset($this->filenameCache)) {
            $this->filenameCache = array();
        }

        if (isset($this->filenameCache[$key])) {
            // A cached filename was found for this key - return it.
            return $this->filenameCache[$key];
        }

        // If no cached filename could be found for this key, generate one.

        // Use a hash function without a salt that is still collision-resistant
        // enough to take the given key and convert it to a reasonably unique
        // filename in a repeatable (but not reversible) fashion.
        $filename = hash('sha256', $key, $raw_output = FALSE);

        // Cache the generated filename for later.
        $this->filenameCache[$key] = $filename;

        // Now return the generated filename.
        return $filename;
    }

    /**
     * Function to clear a previously stored value for the given key.
     * Returns TRUE on success, FALSE otherwise.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return bool       Whether the clear operation was successful.
     */
    public function clear($key) {
        // Get the filename that maps to the given key.
        $filename = $this->_generateFilenameFromKey($key);

        // Construct the full path to the file storing the value.
        $valuePath = $this->storePath . '/' . $filename;

        // Check whether a file exists under the generated filename.
        if (!is_readable($valuePath)) {
            // No such file exists.
            // Return TRUE, the file has already been cleared.
            return TRUE;
        }

        // Remove the value file.
        try {
            unlink($valuePath);
        } catch (Exception $e) {
            return FALSE;
        }

        // If we're here, the clear succeeded.
        return TRUE;
    }

    /**
     * Function to retrieve the stored value for the given key.
     * Returns the stored object, or NULL if the value can't be retrieved.
     *
     * Accesses a file with name equal to the hash of the given key, and
     * retrieves the encrypted, encoded, serialized value from the file.
     * Decodes, decrypts, and deserializes the value from the read data,
     * and returns it. Returns NULL on any error.
     *
     * @param string $key The key under which the value is stored.
     *
     * @return mixed      The stored value.
     */
    public function fetch($key) {
        // Get the filename that maps to the given key.
        $filename = $this->_generateFilenameFromKey($key);

        // Construct the full path to the file storing the value.
        $valuePath = $this->storePath . '/' . $filename;

        // Check whether a file exists under the generated filename.
        if (!is_readable($valuePath)) {
            // No such file exists. Return NULL.
            return NULL;
        }

        // Open the file for reading binary data.
        try {
            $fileDescriptor = fopen($valuePath, "r");
        } catch (Exception $e) {
            // Something went wrong during the open process. Just return NULL.
            return NULL;
        }

        // Attempt to lock the file in shared mode to perform the read.
        if (!flock($fileDescriptor, LOCK_SH)) {
            // Could not obtain the lock. Just return NULL.
            return NULL;
        }

        // Read the data from the file.
        if (!$encryptedData = file_get_contents($valuePath, FILE_TEXT)) {
            // No data could be read from the file. Return NULL.
            return NULL;
        }

        try {
            // Unlock and close the file.
            flock($fileDescriptor, LOCK_UN);
            fclose($fileDescriptor);

            // Decode and decrypt the data read from the file.
            $decryptedData = $this->decryptData($encryptedData);

            // Now deserialize the data.
            $value = unserialize($decryptedData);
        } catch (Exception $e) {
            // Something went wrong, so just return NULL;
            return NULL;
        }

        // Return the deserialized value.
        return $value;
    }

    /**
     * Function to return whether or not the store contains the given key.
     * Returns TRUE if the key exists in the store; FALSE otherwise.
     *
     * @param string $key The key to be tested.
     *
     * @return bool       Whether the key exists in the store.
     */
    public function hasKey($key) {
        // Get the filename that maps to the given key.
        $filename = $this->_generateFilenameFromKey($key);

        // Construct the full path to the file storing the value.
        $valuePath = $this->storePath . '/' . $filename;

        // Check whether a file exists under the generated filename.
        if (realpath($valuePath)) {
            // There does, so return TRUE, indicating this.
            return TRUE;
        } else {
            // There does not, so return FALSE, indicating this.
            return FALSE;
        }
    }

    /**
     * Function to store the given value under the given key.
     * Returns TRUE on success; FALSE otherwise.
     *
     * @param string $key   The key under which the value is to be stored.
     * @param mixed  $value The value to be stored.
     *
     * @return bool         Whether the store operation was successful.
     */
    public function store($key, $value) {
        // Serialize the given value, in preparation for encrypting and
        // encoding it for storage.
        $serializedValue = serialize($value);

        // Encode and encrypt the serialized value.
        $encryptedData = $this->encryptData($serializedValue);

        // Generate the filename of a file to store the value from the given key.
        $filename = $this->_generateFilenameFromKey($key);

        // Construct the full path to the file that will store the value from
        // the store path.
        $valuePath = $this->storePath . '/' . $filename;

        // Open the value store file for writing binary data.
        try {
            $fileDescriptor = fopen($valuePath, "w");
        } catch (Exception $e) {
            // Something went wrong during the open process. Just return FALSE.
            return FALSE;
        }

        // Attempt to lock the file in exclusive mode to perform the write.
        if (!flock($fileDescriptor, LOCK_EX)) {
            // Could not obtain the lock. Just return FALSE.
            return FALSE;
        }

        try {
            // Truncate any existing data, then write the encrypted, encoded,
            // serialized value.
            ftruncate($fileDescriptor, 0);
            fwrite($fileDescriptor, $encryptedData);

            // Unlock and close the file.
            flock($fileDescriptor, LOCK_UN);
            fclose($fileDescriptor);
        } catch (Exception $e) {
            // Something went wrong, so just return FALSE.
            return FALSE;
        }

        // If we're here, the value was successfully stored.
        return TRUE;
    }
}

?>
