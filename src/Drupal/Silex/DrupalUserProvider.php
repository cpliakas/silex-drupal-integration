<?php

namespace Drupal\Silex;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class DrupalUserProvider implements UserProviderInterface
{
    /**
     * @var DrupalClient
     */
    protected $_drupal;

    /**
     * @param DrupalClient $drupal
     */
    public function __construct(DrupalClient $drupal)
    {
        $this->_drupal = $drupal;
    }

    public function loadUserByUsername($username)
    {
        $data = $this->_drupal->getSession()->get('_security.drupal_authentication', array());

        if (!isset($data['user']['name']) || $username != $data['user']['name']) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return new DrupalUser($username, $data['sessid'], $data['session_name'], $data['user']);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof DrupalUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Endafi\Security\User\DrupalUser';
    }
}
