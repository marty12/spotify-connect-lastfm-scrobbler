<?php

namespace ScrobblerBundle\Api;

use GuzzleHttp\Client;

class LastFm
{
    const RESPONSE_TYPE_JSON = 'json';
    const RESPONSE_TYPE_XML = 'xml';

    const REQUEST_TYPE_GET = 'get';
    const REQUEST_TYPE_POST = 'post';

    /** @var Client */
    private $httpClient;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $sessionId;

    /** @var string */
    private $apiSecret;

    /** @var string */
    private $apiUrl;

    /** @var string */
    private $username;

    /** @var string */
    private $preferredResponseType;

    /**
     * LastFm constructor.
     * @param string $apiKey
     * @param string $sessionId
     * @param string $apiSecret
     * @param string $apiUrl
     * @param string $username
     */
    public function __construct($apiKey, $sessionId, $apiSecret, $apiUrl, $username)
    {
        $this->setHttpClient(new Client());

        $this->apiKey = $apiKey;
        $this->sessionId = $sessionId;
        $this->apiSecret = $apiSecret;
        $this->apiUrl = $apiUrl;
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPreferredResponseType(): string
    {
        if (! $this->preferredResponseType) {
            $this->setPreferredResponseType(self::RESPONSE_TYPE_JSON);
        }

        return $this->preferredResponseType;
    }

    /**
     * @param string $preferredResponseType
     * @return LastFm
     */
    public function setPreferredResponseType(string $preferredResponseType): LastFm
    {
        $this->preferredResponseType = $preferredResponseType;
        return $this;
    }

    /**
     * @return array
     */
    public function getLastScrobbledTrack(): array
    {
        return $this->getRecentlyScrobbledTrack(1);
    }

    /**
     * @return array
     */
    public function getCurrentlyPlaying(): array
    {
        return $this->getRecentlyScrobbledTrack(0);
    }

    /**
     * @param int $position
     * @return array
     */
    public function getRecentlyScrobbledTrack(int $position = 0): array
    {
        $queryParameters = array(
            'limit' => $position + 1,
            'nowplaying' => true, // does not seem to have any effect, so set anyway
            'user' => $this->username
        );

        $responseArray = $this->doGetRequest('user.getrecenttracks', $queryParameters);
        $trackData = $responseArray['recenttracks']['track'][$position];

        return ['artist' => $trackData['artist']['#text'], 'title' => $trackData['name']];
    }

    /**
     * @param string $trackArtist
     * @param string $trackTitle
     * @param string $trackAlbum
     * @return LastFm
     */
    public function updateCurrentlyPlaying($trackArtist, $trackTitle, $trackAlbum)
    {
        $queryParameters = array(
            'artist' => $trackArtist,
            'track'  => $trackTitle,
            'album' => $trackAlbum,
        );

        $responseArray = $this->doPostRequest('track.updateNowPlaying', $queryParameters);

        // debug stuff
        $artist = $responseArray['nowplaying']['artist']['#text'];
        $album = $responseArray['nowplaying']['album']['#text'];
        $title = $responseArray['nowplaying']['track']['#text'];

        echo sprintf("now playing %s - %s (%s) \n", $artist, $title, $album);

        return $this;
    }

    /**
     * @param string $trackArtist
     * @param string $trackTitle
     * @param string $trackAlbum
     * @param int $timestamp
     * @return LastFm
     */
    public function scrobbleTrack($trackArtist, $trackTitle, $trackAlbum, $timestamp)
    {
        $queryParameters = array(
            'artist' => $trackArtist,
            'track'  => $trackTitle,
            'album' => $trackAlbum,
            'timestamp' => $timestamp
        );

        $responseArray = $this->doPostRequest('track.scrobble', $queryParameters);

        // debug stuff
        $artist = $responseArray['scrobbles']['scrobble']['artist']['#text'];
        $album = $responseArray['scrobbles']['scrobble']['album']['#text'];
        $title = $responseArray['scrobbles']['scrobble']['track']['#text'];

        echo sprintf("scrobbled %s - %s (%s) \n", $artist, $title, $album);

        return $this;
    }

    /**
     * @return Client
     */
    protected function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * @param Client $httpClient
     * @return LastFm
     */
    protected function setHttpClient(Client $httpClient): LastFm
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @param string $methodName
     * @param array $queryParameters
     * @return array|null
     */
    protected function doGetRequest($methodName, array $queryParameters): array // todo add nullable after PHP7.1 test
    {
        return $this->doRequest(self::REQUEST_TYPE_GET, $methodName, $queryParameters);
    }

    /**
     * @param string $methodName
     * @param array $queryParameters
     * @return array|null
     */
    protected function doPostRequest($methodName, array $queryParameters): array // todo add nullable after PHP7.1 test
    {
        $queryParameters['sk'] = $this->sessionId;
        return $this->doRequest(self::REQUEST_TYPE_POST, $methodName, $queryParameters, true);
    }

    protected function doRequest($requestType, $methodName, $queryParameters, $addSignature = false)
    {
        if ($requestType != self::REQUEST_TYPE_GET && $requestType != self::REQUEST_TYPE_POST) {
            throw new \Exception(sprintf('cannot execute request of type %s', $requestType));
        }

        $queryParameters['api_key'] = $this->apiKey;
        $queryParameters['method'] = $methodName;

        if ($addSignature) {
            $queryParameters['api_sig'] = $this->getSignature($queryParameters);
        }

        $queryParameters['format'] = $this->getPreferredResponseType();
        $guzzleOptionsKey = ($requestType == 'get') ? 'query' : 'form_params';

        $response = $this->getHttpClient()
            ->$requestType($this->apiUrl, [$guzzleOptionsKey => $queryParameters]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function getSignature(array $parameters): string
    {
        ksort($parameters);

        $signature = '';
        foreach ($parameters as $key => $value) {
            $signature .= $key;
            $signature .= $value;
        }

        $signature .= $this->apiSecret;

        return md5($signature);
    }
}