<?php

namespace Drupal\Silex;

use Symfony\Component\Security\Core\User\UserInterface;

class DrupalUser extends \ArrayObject implements UserInterface
{
    protected $_username;
    protected $_sessId;
    protected $_sessName;
    protected $_user;

    /**
     * @param string $username
     * @param string $sess_id
     * @param array $sess_name
     * @param array $user
     *   - roles:
     *   - data:
     *   - login:
     *   - uid:
     *   - name:
     *   - theme:
     *   - signature:
     *   - signature_format:
     *   - created:
     *   - access:
     *   - status:
     *   - timezone:
     *   - language:
     */
    public function __construct($username, $sess_id, $sess_name, array $user)
    {
        $this->_username = $username;
        $this->_sessId = $sess_id;
        $this->_sessName = $sess_name;
        parent::__construct($user);
    }

    /**
     * Implements UserInterface::
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Implements UserInterface::getPassword()
     */
    public function getPassword()
    {
        return $this->_sessId;
    }

    /**
     * @return string
     *
     * @see self::getPassword()
     */
    public function getSessionId()
    {
        return $this->_sessId;
    }

    /**
     * @return string
     */
    public function getSessionName()
    {
        return $this->_sessName;
    }

    /**
     * Implements UserInterface::getRoles()
     */
    public function getRoles()
    {
        return $this['roles'];
    }

    /**
     * Implements UserInterface::eraseCredentials()
     */
    public function eraseCredentials()
    {
        // Nothing to do ...
    }

    /**
     * Implements UserInterface::getSalt()
     *
     * Password is salted on the Drupal side, so we don't have that info.
     */
    public function getSalt()
    {
        // Nothing to do ...
    }
}
