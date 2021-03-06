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
        $sso    = \FreeFW\DI\DI::getShared('sso');
        $user   = false;
        $config = $p_request->getAttribute('broker_config', []);
        if (!is_array($config)) {
            $config = [];
        }
        $login = $p_header->getParameter('id');
        $user = \FreeSSO\Model\User::findFirst(['user_login' => $login]);
        if ($user && $user->isActive()) {
            $hmac  = $user->getUserPassword();
            $inMac = $p_header->getParameter('mac');
            $this->logger->debug('mac.hmac : ' . $hmac);
            $this->logger->debug('mac.in : ' . $inMac);
            $calcMac = $this->generateMac($p_request, $p_header, $hmac, 'header');
            $this->logger->debug('mac.calc : ' . $calcMac);
            if ($inMac == $calcMac) {
                $user = $sso->signinByIdAndLogin($user->getUserId(), $login, false);
            } else {
                $this->logger->info('FreeFW.Middleware.Hawk.wrong.mac');
                $user = false;
            }
            return $user;
        }
        return false;
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
    public function generateMac(
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
        $app     = $p_auth_header->getParameter('app', '');
        $dlg     = $p_auth_header->getParameter('dlg', '');
        $path    = $uri->getPath();
        $query   = $uri->getQuery();
        parse_str($query, $params);
        if (isset($params['_request'])) {
            unset($params['_request']);
        }
        $query = http_build_query($params);
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
        if ($app != '') {
            $default['app'] = $app;
            $default['dlg'] = $dlg;
        }
        $data = implode("\n", $default) . "\n";
        $this->logger->debug($data);
        $hash = hash_hmac('sha256', $data, $p_secret, true);
        // Return base64 value
        return base64_encode($hash);
    }
}
