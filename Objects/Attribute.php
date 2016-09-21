<?php

/**
 * Mesut Vatansever | mesut.vatansever[at]freebirdairlines.com
 * Date: 21/09/16 17:03
 */

namespace Symfony\Component\Ldap\Objects;

use Symfony\Component\Ldap\Exception\AttributeException;
use Symfony\Component\Ldap\LdapClientInterface;

class Attribute
{

    /**
     * @var LdapClientInterface
     */
    private $ldapClient;

    /**
     * Attribute constructor.
     *
     * @param LdapClientInterface $ldapClient
     */
    public function __construct(LdapClientInterface $ldapClient)
    {
        $this->ldapClient = $ldapClient;
    }

    /**
     * Get current connection
     *
     * @return null | resource
     */
    private function getConnection()
    {
        return $this->ldapClient->getConnection();
    }

    /**
     * Add a new attribute to exists entry
     *
     * @param string $dn
     * @param array  $attribute
     * @return bool
     * @throws AttributeException
     */
    public function addAttribute($dn, array $attribute)
    {
        $this->checkAttributeCount($attribute);

        $addAttribute = ldap_mod_add($this->getConnection(), $dn, $attribute);

        if(false === $addAttribute){
            throw new AttributeException(ldap_error($this->getConnection()));
        }

        return $addAttribute;
    }

    /**
     * Change an exists attribute's value
     *
     * @param string $dn
     * @param array  $attribute
     * @return bool
     * @throws AttributeException
     */
    public function updateAttribute($dn, array $attribute)
    {
        $this->checkAttributeCount($attribute);

        $changeAttribute = ldap_mod_replace($this->getConnection(), $dn, $attribute);

        if(false === $changeAttribute){
            throw new AttributeException(ldap_error($this->getConnection()));
        }

        return $changeAttribute;
    }

    /**
     * Delete an exist attribute
     *
     * @param string $dn
     * @param array  $attribute
     * @return bool
     * @throws AttributeException
     */
    public function deleteAttribute($dn, array $attribute)
    {
        $this->checkAttributeCount($attribute);

        $deleteAttribute = ldap_mod_del($this->getConnection(), $dn, $attribute);

        if(false === $deleteAttribute){
            throw new AttributeException(ldap_error($this->getConnection()));
        }

        return $deleteAttribute;
    }

    /**
     * Check attribute array exists value
     *
     * @param array $attribute
     * @return bool
     * @throws AttributeException
     */
    protected function checkAttributeCount(array $attribute)
    {
        if(count($attribute) == 0){
            throw new AttributeException("Attribute must have some values!");
        }

        return true;
    }
}