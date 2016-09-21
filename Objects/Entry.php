<?php

/**
 * Mesut Vatansever | mesut.vatansever[at]freebirdairlines.com
 * Date: 21/09/16 17:019
 */

namespace Symfony\Component\Ldap\Objects;

use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\LdapClientInterface;

class Entry
{
    /**
     * @var LdapClientInterface
     */
    private $ldapClient;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $removedAttributes;

    /**
     * @var array
     */
    private $newAttributes;

    /**
     * @var array
     */
    private $diffAttributes;

    /**
     * Attribute constructor.
     *
     * @param LdapClientInterface $ldapClient
     * @param array               $entry
     */
    public function __construct(LdapClientInterface $ldapClient, array $entry)
    {
        $this->ldapClient = $ldapClient;
        $this->fillAttributes($entry);
    }

    private function fillAttributes($entry)
    {
        foreach ($entry as $attribute => $value) {
            if(is_array($value)){
                unset($value['count']);
                $this->attributes[$attribute] = $value;
            }else{
                $this->attributes[$attribute] = $value;
            }
        }
    }

    /**
     * Update an exists entry
     *
     * @param $dn
     * @param array $attributes
     */
    public function update($dn, array $attributes)
    {
        $modifyBatches = $this->getModifyBatches($attributes);

        $modify = ldap_modify_batch($this->ldapClient->getConnection(), $dn, $modifyBatches);

        if (! $modify) {
            throw new LdapException($this->getError());
        }

        $this->syncAttributes();

        return $modify;
    }

    /**
     * Sync removed, difference and new attributes values with "attribute" property.
     *
     * @return void
     */
    private function syncAttributes()
    {
        foreach ($this->removedAttributes as $key => $removedAttribute) {
            unset($this->attributes[$removedAttribute]);
        }
        $this->removedAttributes = [];

        foreach ($this->newAttributes as $newAttribute => $value) {
            $this->attributes[$newAttribute] = $value;
        }
        $this->newAttributes = [];

        foreach ($this->diffAttributes as $diffAttribute => $value) {
            $this->attributes[$diffAttribute] = $value;
        }
        $this->diffAttributes = [];
    }

    /**
     * @param array $attributes
     * @return array
     */
    private function getModifyBatches(array $attributes)
    {
        $modifyBatches = [];

        $diffAttributes = $this->diffAttributes($attributes);
        $newAttributes = $this->newAttributes($attributes);
        $removeAttributes = $this->removeAttributes($attributes);

        foreach ($diffAttributes as $key => $value) {
            $modifyBatches[] = $value;
        }

        foreach ($newAttributes as $key => $value) {
            $modifyBatches[] = $value;
        }

        foreach ($removeAttributes as $key => $value) {
            $modifyBatches[] = $value;
        }

        return $modifyBatches;
    }

    /**
     * Compare current entry attributes with given attributes
     *
     * @param array  $attributes
     * @return array
     */
    private function diffAttributes(array $attributes)
    {
        $modifies = [];

        foreach ($attributes as $attribute => $value) {

            if(in_array($attribute, $this->attributes)){
                if(is_array($value)){
                    // May be required some conditions for compare
                    if(! ($value === $this->attributes[$attribute])){

                        $modifies[] = [
                            "attrib"  => $attribute,
                            "modtype" => LDAP_MODIFY_BATCH_REMOVE_ALL
                        ];

                        $modifies[] = [
                            "attrib"  => $attribute,
                            "modtype" => LDAP_MODIFY_BATCH_ADD,
                            "values"  => [$value]
                        ];

                        $this->diffAttributes[$attribute] = $value;
                    }
                }else{
                    if($this->attributes[$attribute] != $value) {
                        $modifies[] = [
                            "attrib"  => $attribute,
                            "modtype" => LDAP_MODIFY_BATCH_REPLACE,
                            "values"  => [$value]
                        ];

                        $this->diffAttributes[$attribute] = $value;
                    }
                }
            }
        }

        return $modifies;
    }

    /**
     * Detect new attributes and return with an array
     *
     * @param array $attributes
     * @return array
     */
    private function newAttributes(array $attributes)
    {
        $news = [];

        foreach ($attributes as $attribute => $value) {

            if (!in_array($attribute, array_keys($this->attributes))) {
                $news = [
                    'attrib'  => $attribute,
                    'modtype' => LDAP_MODIFY_BATCH_ADD,
                    "values"  => [$value]
                ];

                $this->newAttributes[$attribute] = $value;
            }
        }

        return $news;
    }

    /**
     * Detect removed attributes and return with an array
     *
     * @param array $attributes
     * @return array
     */
    private function removeAttributes(array $attributes)
    {
        $removed = [];

        foreach ($this->attributes as $attribute => $value) {

            if (! in_array($attribute, $attributes)) {
               $removed = [
                   "attrib"  => $attribute,
                   "modtype" => LDAP_MODIFY_BATCH_REMOVE_ALL
               ];

                $this->removedAttributes[] = $attribute;
            }
        }

        return $removed;
    }

    /**
     * @return string
     */
    private function getError()
    {
        return ldap_error($this->ldapClient->getConnection());
    }
}