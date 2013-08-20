<?php

namespace Drupal\Silex;

use Guzzle\Http\Exception\BadResponseException;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DrupalAuthenticationProvider extends UserAuthenticationProvider
{
    /**
     * @var DrupalClient
     */
    protected $_drupal;

    /**
     * Constructor.
     */
    public function __construct(DrupalClient $drupal, UserCheckerInterface $user_checker, $provider_key)
    {
        $this->_drupal = $drupal;
        parent::__construct($user_checker, $provider_key);
    }

    /**
     * Implements UserAuthenticationProvider::retrieveUser()
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            return $user;
        }

        try {

            $data = $this->_drupal->login($username, $token->getCredentials());
            return new DrupalUser($username, $data['sessid'], $data['session_name'], $data['user']);

        } catch (BadResponseException $e) {

            $response = $e->getResponse();
            $code = $response->getStatusCode();
            if (401 == $code) {
                throw new BadCredentialsException($response->getReasonPhrase());
            } else {
                throw new AuthenticationServiceException($response->getReasonPhrase());
            }

        } catch (\Exception $e) {
            $service_ex = new AuthenticationServiceException($e->getMessage(), 0, $e);
            $service_ex->setToken($token);
            throw $service_ex;
        }
    }

    /**
     * Implements UserAuthenticationProvider::checkAuthentication()
     *
     * If we got here user is valid, just save the data in a session.
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $data = array(
            'sessid' => $user->getSessionId(),
            'session_name' => $user->getSessionName(),
            'user' => (array) $user,
        );

        // Save the Drupal session data, reset the headers for the CSRF request.
        $this->setDrupalSessionData($data);

        // Get the CSRF token associated with this session.
        $data['csrf_token'] = $this->_drupal->getCsrfToken();

        // Reset the headers for subsequent authenticated services requests.
        $this->setDrupalSessionData($data);
    }

    /**
     * Saves session data in the key that stores Drupal user info. Resets the
     * default headers to use the session data for authenticated requests.
     *
     * @param array $data
     *   - sessid:
     *   - session_name:
     *   - user:
     *   - csrf_token: (optional)
     */
    public function setDrupalSessionData($data)
    {
        $this->_drupal->getSession()->set('_security.drupal_authentication', $data);
        $this->_drupal->setDefaultHeaders();
    }
}
