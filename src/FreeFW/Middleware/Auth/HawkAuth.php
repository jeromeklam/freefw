<?php
namespace FreeFW\Middleware\Auth;

use \Psr\Http\Message\ServerRequestInterface;
use \FreeFW\Middleware\Auth\AuthorizationHeader;

/**
 * HAWK Auth
 *
 * @author jeromeklam
 */
class HawkAuth implements
    \Psr\Log\LoggerAwareInterface,
    \FreeFW\Interfaces\ConfigAwareTraitInterface,
    \FreeFW\Interfaces\AuthAdapterInterface
{

    /**
     * Behaviour
     */
    use \Psr\Log\LoggerAwareTrait;
    use \FreeFW\Behaviour\EventManagerAwareTrait;
    use \FreeFW\Behaviour\ConfigAwareTrait;

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\AuthAdapterInterface::getAuthorizationHeader()
     */
    public function getAuthorizationHeader(ServerRequestInterface $p_request, AuthorizationHeader $p_header)
    {
        return '';
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\AuthAdapterInterface::verifyAuthorizationHeader()
     */
    public function verifyAuthorizationHeader(ServerRequestInterface $p_request, AuthorizationHeader $p_header)
    {
        $user   = false;
        $config = $p_request->getAttribute('broker_config', []);
        if (!is_array($config)) {
            $config = [];
        }
        $hmac = '56a29c018a4e4752a6a217f7fb1070a4';
        if (array_key_exists('hawk', $config)) {
            if (array_key_exists('hmac', $config['hawk'])) {
                $hmac = $config['hawk']['hmac'];
            }
        }
        $inMac = $p_header->getParameter('mac');
        $this->debug('mac.hmac : ' . $hmac);
        $this->debug('mac.in : ' . $inMac);
        $calcMac = self::generateMac($p_request, $p_header, $hmac, 'header');
        $this->debug('mac.calc : ' . $calcMac);
        if ($inMac == $calcMac) {
            $token = $p_header->getParameter('user');
            /**
             * @var \FreeSSO\Server $sso
             */
            $sso  = \FreeFW\DI\DI::getShared('sso');
            $user = $sso->signinByUserToken($token);
        }
        return $user;
    }

    /**
     * Generate the MAC
     *
     * @param ServerRequestInterface $p_request
     * @param AuthorizationHeader    $p_auth_header
     * @param string                 $p_secret
     * @param string                 $p_type
     * @param boolean                $p_withPort
     *
     * @return string         The base64 encode MAC
     */
    public static function generateMac(
        ServerRequestInterface $p_request,
        AuthorizationHeader $p_auth_header,
        $p_secret = '',
        $p_type = 'header',
        $p_withPort = true
    ) {
        $uri     = $p_request->getUri();
        $nonce   = null;
        $ts      = $p_auth_header->getParameter('ts', '');
        $ext     = $p_auth_header->getParameter('ext', '');
        $nonce   = $p_auth_header->getParameter('nonce', '');
        $path    = $uri->getPath();
        $query   = $uri->getQuery();
        if ($p_withPort) {
            $port = $uri->getPort();
            if ($port == '') {
                if ($uri->getScheme() == 'https') {
                    $port = 443;
                } else {
                    $port = 80;
                }
            }
        } else {
            $port = '';
        }
        if ($query != '') {
            $path = $path . '?' . $query;
        }
        $default = [
            'vers'   => 'hawk.1.' . $p_type,
            'ts'     => $ts,
            'nonce'  => $nonce,
            'method' => strtoupper($p_request->getMethod()),
            'path'   => urldecode($path),
            'host'   => strtolower($uri->getHost()),
            'port'   => $port,
            'hash'   => '',
            'ext'    => $ext
        ];
        $data = implode("\n", $default) . "\n";
        self::debug($data);
        $hash = hash_hmac('sha256', $data, $p_secret, true);
        // Return base64 value
        return base64_encode($hash);
    }
}