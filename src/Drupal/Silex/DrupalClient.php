<?php

namespace Drupal\Silex;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DrupalClient
{
    /**
     * @var SessionInterface
     */
    protected $_session;

    /**
     * @var string
     */
    protected $_basePath;

    /**
     * @var string
     */
    protected $_endpoint;

    /**
     * @var Client
     */
    protected $_client;

    /**
     * @param SessionInterface $session
     * @param string $base_url
     * @param string $endpoint
     * @param string $base_path
     */
    public function __construct(SessionInterface $session, $base_url, $endpoint, $base_path = '/')
    {
        $this->_session = $session;
        $this->_client = new Client($base_url);

        $this
            ->setEndpoint($endpoint)
            ->setBasePath($base_path)
            ->setDefaultHeaders()
        ;
    }

    /**
     * @param string $endpoint
     *
     * @return DrupalClient
     */
    public function setEndpoint($endpoint)
    {
        $this->_endpoint = rtrim($endpoint, '/');
        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->_endpoint;
    }

    /**
     * @param string $base_path
     *
     * @return DrupalClient
     */
    public function setBasePath($base_path)
    {
        $this->_basePath = rtrim($base_path, '/') . '/';
        return $this;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * @param mixed $data
     *
     * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Json.php/function/Json%3A%3Aencode/8
     */
    public static function jsonEncode($data)
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    /**
     * Sets the default headers used for authenticated services requests.
     */
    public function setDefaultHeaders()
    {
        $headers = array('Content-Type' => 'application/json; charset=utf-8');
        $data = $this->_session->get('_security.drupal_authentication');

        if (isset($data['session_name']) && isset($data['sessid'])) {
            $headers['Cookie'] = $data['session_name'] . '=' . $data['sessid'];
        }

        if (isset($data['csrf_token'])) {
            $headers['X_CSRF_TOKEN'] = $data['csrf_token'];
        }

        $this->_client->setDefaultHeaders($headers);
        return $this;
    }

    /**
     * Get a CSRF token for security.
     *
     * @return string
     */
    public function getCsrfToken()
    {
        $path = $this->_basePath . 'services/session/token';
        return (string) $this->_client->get($path)->send()->getBody();
    }

    /**
     * Logs a user in via the API.
     *
     * @param string $username
     * @param string $password
     * @param array|null $header
     */
    public function login($username, $password, $header = null)
    {
        $body = self::jsonEncode(array(
            'username' => $username,
            'password' => $password,
        ));

        $uri = $this->_basePath . $this->_endpoint . '/user/login';
        return $this->_client->post($uri, $header, $body)->send()->json();
    }

    /**
     * @param string $type
     * @param array $data
     * @param array|null $header
     */
    public function createNode($type, $data = array(), $header = null)
    {
        $data['type'] = $type;
        $body = self::jsonEncode($data);

        $uri = $this->_basePath . $this->_endpoint . '/node';
        return $this->_client->post($uri, $header, $body)->send()->json();
    }
}
