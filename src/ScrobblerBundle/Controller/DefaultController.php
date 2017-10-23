<?php

namespace ScrobblerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SpotifyWebAPI;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 * todo this just contains a very rough and hackish version of the authentication process for PoC, needs major refactoring
 * @package ScrobblerBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $session = new SpotifyWebAPI\Session(
            $this->getParameter('spotify_client_id'),
            $this->getParameter('spotify_client_secret'),
            $this->getParameter('spotify_redirect_url')
        );

        $options = [
            'scope' => [
                'user-read-currently-playing',
                'user-read-recently-played',
            ],
        ];

        header('Location: ' . $session->getAuthorizeUrl($options));
    }

    /**
     * @Route("/set_token")
     */
    public function setTokenAction(Request $request)
    {
        $authorizationCode = $request->get('code');

        $session = new SpotifyWebAPI\Session(
            $this->getParameter('spotify_client_id'),
            $this->getParameter('spotify_client_secret'),
            $this->getParameter('spotify_redirect_url')
        );

        $session->requestAccessToken($authorizationCode);

        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();

        echo "access token $accessToken <br />";
        echo "refresh token $refreshToken <br />";
    }
}
