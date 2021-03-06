<?php
/**
 * User mailbox definitions.
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

/** Import the server connection classes. */
require_once(APPLICATION_PATH . '/modules/serverconnection.php');

/**
 * Class to define and manipulate mailboxes.
 *
 * @category  Application
 * @package   PHPBiff
 * @author    SG Kassel sg_dot_kassel_dot_au_at_gmail_dot_com
 * @copyright 2010 SG Kassel
 * @license   http://www.gnu.org/licenses/lgpl-2.1.html LGPL-2.1
 * @link      http://phpbiff.sourceforge.net
 */
class Mailbox {
    // Instance attributes.

    /**
     * Mail box account name.
     *
     * @var string
     */
    protected $accountName;

    /**
     * Authentication method (currently only 'plain' is supported.)
     *
     * @var string
     */
    protected $authenticationMethod;

    /**
     * Connection to the server.
     *
     * @var ServerConnection
     */
    protected $connection;

    /**
     * Check frequency in seconds.
     *
     * @var int
     */
    protected $checkFrequency;

    /**
     * Display order of mailbox in mailbox view.
     *
     * @var int
     **/
    protected $displayOrder;

    /**
     * Hostname of the server the mailbox resides upon.
     *
     * @var string
     */
    protected $hostname;

    /**
     * Time of the last mailbox status check.
     *
     * @var int
     */
    protected $lastChecked;

    /**
     * Message count as at the last check.
     *
     * @var int
     */
    protected $messageCount;

    /**
     * Mail box access password. (Plaintext.)
     *
     * @var string
     */
    protected $password;

    /**
     * Mail box access protocol (currently only 'pop3' is supported.)
     *
     * @var string
     */
    protected $protocol;

    /**
     * Mail box access port.
     *
     * @var int
     */
    protected $port;

    /**
     * Read message count (for tracking old and new mail.)
     *
     * @var int
     */
    protected $readMessageCount;

    /**
     * Current mail box status ('error', 'no mail', 'old mail', 'new mail')
     *
     * @var string
     */
    protected $status;

    /**
     * Current connection timeout.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Mail box access username.
     *
     * @var string
     */
    protected $username;

    // Instance attribute accessors.

    /**
     * Returns the current account name.
     *
     * @return string The current account name.
     */
    public function getAccountName() {
        return $this->accountName;
    }

    /**
     * Returns the current authentication method.
     *
     * @return string The current authentication method.
     */
    public function getAuthenticationMethod() {
        return $this->authenticationMethod;
    }

    /**
     * Returns the current check frequency.
     *
     * @return int The current check frequency.
     */
    public function getCheckFrequency() {
        return $this->checkFrequency;
    }

    /**
     * Returns the current display order.
     *
     * @return int The current display order.
     */
    public function getDisplayOrder() {
        return $this->displayOrder;
    }

    /**
     * Returns the current server hostname.
     *
     * @return string The current server hostname.
     */
    public function getHostname() {
        return $this->hostname;
    }

    /**
     * Returns the current last checked time.
     *
     * @return int The current last checked time.
     */
    public function getLastChecked() {
        return $this->lastChecked;
    }

    /**
     * Returns the current message count.
     *
     * @return int The current message count.
     */
    public function getMessageCount() {
        return $this->messageCount;
    }

    /**
     * Returns the current server port.
     *
     * @return int The current server port.
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Returns the current server protocol.
     *
     * @return string The current server protocol.
     */
    public function getProtocol() {
        return $this->protocol;
    }

    /**
     * Returns the current read message count.
     *
     * @return int The current read message count.
     */
    public function getReadMessageCount() {
        return $this->readMessageCount;
    }

    /**
     * Returns the current mailbox status.
     *
     * @return string The current mailbox status.
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Returns the current server connection timeout.
     *
     * @return int The current server connection timeout.
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Returns the current username.
     *
     * @return string The current username.
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Sets the account name to that given.
     *
     * @param string $accountName The new account name.
     *
     * @return NULL
     */
    public function setAccountName($accountName) {
        $this->accountName = $accountName;
    }

    /**
     * Sets the authentication method to that given.
     *
     * @param string $authenticationMethod The new authentication method.
     *
     * @return NULL
     */
    public function setAuthenticationMethod($authenticationMethod) {
        $this->authenticationMethod = $authenticationMethod;
    }

    /**
     * Sets the check frequency to that given.
     *
     * @param int $checkFrequency The new check frequency.
     *
     * @return NULL
     */
    public function setCheckFrequency($checkFrequency) {
        $this->checkFrequency = $checkFrequency;
    }

    /**
     * Sets the mailbox display order to that given.
     *
     * @param int $displayOrder The new mailbox display order.
     *
     * @return NULL
     */
    public function setDisplayOrder($displayOrder) {
        $this->displayOrder = $displayOrder;
    }

    /**
     * Sets the server hostname to that given.
     *
     * @param int $hostname The new server hostname.
     *
     * @return NULL
     */
    public function setHostname($hostname) {
        $this->hostname = $hostname;
    }

    /**
     * Sets the password to that given.
     *
     * @param string $password The new password.
     *
     * @return NULL
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Sets the server port to that given.
     *
     * @param int $port The new server port.
     *
     * @return NULL
     */
    public function setPort($port) {
        $this->port = $port;
    }

    /**
     * Sets the server protocol to that given.
     *
     * @param string $protocol The new server protocol.
     *
     * @return NULL
     */
    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    /**
     * Sets the server connection timeout to that given.
     *
     * @param int $timeout The new server connection timeout.
     *
     * @return NULL
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    /**
     * Sets the username to that given.
     *
     * @param string $username The new username.
     *
     * @return NULL
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    // Instance methods.

    /**
     * Creates a new mailbox from the given parameters.
     *
     * @param string $accountName          The new account's name.
     * @param string $authenticationMethod The authentication method to use.
     * @param string $checkFrequency       The mail check frequency.
     * @param string $displayOrder         The display order of the mailbox
     *                                     in the UI.
     * @param string $hostname             The server hostname.
     * @param string $password             The password to use with the server.
     * @param string $port                 The port upon which to connect to
     *                                     the server.
     * @param string $protocol             The protocol to use to connect to
     *                                     the server.
     * @param string $timeout              The server connection timeout to use.
     * @param string $username             The username to user with the server.
     *
     * @return NULL
     */
    public function __construct($accountName = 'New account',
                                $authenticationMethod = 'plain',
                                $checkFrequency = 60, $displayOrder = 1,
                                $hostname = 'localhost', $password = '',
                                $port = 110, $protocol = 'pop3',
                                $timeout = 10, $username = '') {
        // Set attributes from the given parameters.
        $this->accountName = $accountName;
        $this->authenticationMethod = $authenticationMethod;
        $this->checkFrequency = $checkFrequency;
        $this->displayOrder = $displayOrder;
        $this->hostname = $hostname;
        $this->password = $password;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->timeout = $timeout;
        $this->username = $username;

        // Make sure the internal counters are set correctly.
        $this->connection = NULL;
        $this->lastChecked = NULL;
        $this->messageCount = 0;
        $this->readMessageCount = 0;
        $this->status = 'error';
    }

    /**
     * Function to check the mailbox, updating the mail box status and
     * message count.
     *
     * @return NULL
     */
    public function check() {
        global $serverConnectionFactory;

        try {
            // Establish a server connection with the given settings for this
            // mailbox.
            $this->connection = $serverConnectionFactory->createConnection(
                                    $this->protocol, $this->hostname,
                                    $this->port, $this->timeout);
            // Log into the server.
            $this->connection->login($this->username, $this->password,
                                     $this->authenticationMethod);

            // Get and store the current message count.
            $this->messageCount = $this->connection->messageCount();

            // Work out the status of the mailbox from the old and new message
            // counts.
            if ($this->readMessageCount < $this->messageCount) {
                $this->status = 'new mail';
            } else {
                $this->status = 'old mail';
            }

            if ($this->messageCount == 0) {
                $this->status = 'no mail';
            }

            // Set the last check time.
            $this->lastChecked = time();
        } catch (Exception $e) {
            // Indicate that the mailbox status is in error, and that
            // no messages could be retrieved.
            $this->status = 'error';
            $this->messageCount = 0;
        }
    }

    /**
     * Function to mark the current mailbox's messages as read.
     *
     * @return NULL
     */
    public function markAsRead() {
        $this->readMessageCount = $this->messageCount;

        if ($this->status == 'new mail') {
            $this->status = 'old mail';
        }
    }
}
?>
