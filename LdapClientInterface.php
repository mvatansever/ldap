<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Ldap;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;

/**
 * Ldap interface.
 *
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 * @author Charles Sarrazin <charles@sarraz.in>
 *
 * @internal
 */
interface LdapClientInterface
{
    /**
     * Return a connection bound to the ldap.
     *
     * @param string $dn       A LDAP dn
     * @param string $password A password
     *
     * @throws ConnectionException If dn / password could not be bound.
     */
    public function bind($dn = null, $password = null);

    /*
     * Find any entry into ldap connection.
     *
     * @param string $dn
     * @param string $query
     * @param mixed  $filter
     *
     * @return array|null
     */
    public function find($dn, $query, $filter = '*');

    /**
     * Add an entry to LDAP
     *
     * @param $dn
     * @param array $entry
     * @return bool
     * @throws LdapException
     */
    public function add($dn, array $entry);

    /**
     * Remove an exists entry from LDAP
     *
     * @param string   $dn
     * @return bool
     * @throws LdapException
     */
    public function delete($dn);

    /**
     * Escape a string for use in an LDAP filter or DN.
     *
     * @param string $subject
     * @param string $ignore
     * @param int    $flags
     *
     * @return string
     */
    public function escape($subject, $ignore = '', $flags = 0);

    /**
     * Get current connection
     *
     * @return null | resource
     */
    public function getConnection();
}
