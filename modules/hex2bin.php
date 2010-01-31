<?php
/*
 * Copyright (c) Geoff Kassel, 2010. All rights reserved.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 2 as published by the Free
 * Software Foundation and appearing in the file LICENSE.GPL included in
 * the packaging of this file.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
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
 * Email: gkassel_at_users_dot_sourceforge_dot_net
 */
/**
 * Hex digit string ('hexits') to binary string decoder.
 *
 * @author Geoff Kassel gkassel_at_users_dot_sourceforce_dot_net
 * @copyright Copyright (c) Geoff Kassel, 2010. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GPL-2
 * @package phpbiff
 */

/**
 * Function to decode a string containing hex digits ('hexits') to the
 * original binary string representation. This is the inverse operation
 * for the built-in bin2hex function.
 * Raises a LogicException if an invalid hex digit string is given.
 *
 * @param string $hex The hex digit string to decode.
 * @return string     The decoded binary string representation.
 */
function hex2bin($hex)
{
    $binary = '';
    $len = strlen($hex);
    if (($len % 2) == 1)
    {
        throw new LogicException("Invalid hex string given.");
    }
    $stepLimit = $len - 1;
    for ($index = 0; $index < $stepLimit; $index += 2)
    {
        $hexit = substr($hex, $index, 2);
        if (!preg_match("/^[0-9ABCDEF]+$/i", $hexit))
        {
            throw new LogicException("Invalid hex digit '$hexit' given,");
        }
        $binary .= chr(hexdec($hexit));
    }
    return $binary;
}

?>
