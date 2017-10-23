<?php

namespace ScrobblerBundle\Handler;

use GuzzleHttp\Client;

// todo decouple scrobble handler and last.fm implementation
// todo 2: much duplication here; merge calls (mostly api related stuff, such as signature)

class ScrobbleHandler
{
    const PROGRESS_FOR_SCROBBLE = 0.75;

    /** @var Client */
    private $httpClient;

    /** @var string */
    private $lastFmApiKey;

    /** @var string */
    private $lastFmSessionId;

    /** @var string */
    private $lastFmApiSecret;

    /** @var string */
    private $lastFmApiUrl;

    /** @var string */
    private $lastFmUsername;

    public function __construct($lastFmApiKey, $lastFmSessionId, $lastFmApiSecret, $lastFmApiUrl, $lastFmUsername)
    {
        $this->setLastFmApiKey($lastFmApiKey)
            ->setLastFmSessionId($lastFmSessionId)
            ->setLastFmApiSecret($lastFmApiSecret)
            ->setLastFmApiUrl($lastFmApiUrl)
            ->setLastFmUsername($lastFmUsername);

        $this->setHttpClient(new Client());
    }

    /**
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * @param Client $httpClient
     * @return ScrobbleHandler
     */
    public function setHttpClient(Client $httpClient): ScrobbleHandler
    {
        $this->httpClient = $httpClient;
        return $this;
    }


    /**
     * @return string
     */
    protected function getLastFmApiKey(): string
    {
        return $this->lastFmApiKey;
    }

    /**
     * @param string $lastFmApiKey
     * @return ScrobbleHandler
     */
    protected function setLastFmApiKey(string $lastFmApiKey): ScrobbleHandler
    {
        $this->lastFmApiKey = $lastFmApiKey;
        return $this;
    }

    /**
     * @return string
     */
    protected function getLastFmSessionId(): string
    {
        return $this->lastFmSessionId;
    }

    /**
     * @param string $lastFmSessionId
     * @return ScrobbleHandler
     */
    protected function setLastFmSessionId(string $lastFmSessionId): ScrobbleHandler
    {
        $this->lastFmSessionId = $lastFmSessionId;
        return $this;
    }

    /**
     * @return string
     */
    protected function getLastFmApiSecret(): string
    {
        return $this->lastFmApiSecret;
    }

    /**
     * @param string $lastFmApiSecret
     * @return ScrobbleHandler
     */
    protected function setLastFmApiSecret(string $lastFmApiSecret): ScrobbleHandler
    {
        $this->lastFmApiSecret = $lastFmApiSecret;
        return $this;
    }

    /**
     * @return string
     */
    protected function getLastFmApiUrl(): string
    {
        return $this->lastFmApiUrl;
    }

    /**
     * @param string $lastFmApiUrl
     * @return ScrobbleHandler
     */
    protected function setLastFmApiUrl(string $lastFmApiUrl): ScrobbleHandler
    {
        $this->lastFmApiUrl = $lastFmApiUrl;
        return $this;
    }

    /**
     * @return string
     */
    protected function getLastFmUsername(): string
    {
        return $this->lastFmUsername;
    }

    /**
     * @param string $lastFmUsername
     * @return ScrobbleHandler
     */
    protected function setLastFmUsername(string $lastFmUsername): ScrobbleHandler
    {
        $this->lastFmUsername = $lastFmUsername;
        return $this;
    }

    public function updateCurrentlyPlaying(array $trackData)
    {
        $currentlyPlayingLastFmData = $this->getCurrentlyPlayingInLastFm();
        if ($currentlyPlayingLastFmData['artist'] == $trackData['track_artist']
            && $currentlyPlayingLastFmData['title'] == $trackData['track_title']) {
            return;
        }

        $signature = 'album' . $trackData['track_album'];
        $signature .= 'api_key' . $this->getLastFmApiKey();
        $signature .= 'artist' . $trackData['track_artist'];
        $signature .= 'methodtrack.updateNowPlaying';
        $signature .= 'sk' . $this->getLastFmSessionId();
        $signature .= 'track' . $trackData['track_title'];
        $signature .= $this->getLastFmApiSecret();

        $signature = md5($signature);

        $queryParameters = array(
            'artist' => $trackData['track_artist'],
            'track'  => $trackData['track_title'],
            'album' => $trackData['track_album'],
            'api_key' => $this->getLastFmApiKey(),
            'api_sig' => $signature,
            'sk'    => $this->getLastFmSessionId(),
            'method' => 'track.updateNowPlaying',
            'format' => 'json'
        );

        $client = new Client();
        $response = $client->post($this->getLastFmApiUrl(), ['form_params' => $queryParameters]);

        // debug stuff
        $responseArray = json_decode($response->getBody()->getContents(), true);
        $artist = $responseArray['nowplaying']['artist']['#text'];
        $album = $responseArray['nowplaying']['album']['#text'];
        $title = $responseArray['nowplaying']['track']['#text'];

        echo sprintf("now playing %s - %s (%s) \n", $artist, $title, $album);
    }

    public function trackShouldBeScrobbled($trackData)
    {
        if (($trackData['current_track_progress'] / $trackData['track_total_time']) <= self::PROGRESS_FOR_SCROBBLE) {
            return false;
        }

        $lastScrobbledTrackData = $this->getLastScrobbledTrack();
        return ($lastScrobbledTrackData['artist'] != $trackData['track_artist']
            && $lastScrobbledTrackData['title'] != $trackData['track_title']);
    }

    public function scrobbleTrack($trackData)
    {
        $queryParameters = array(
            'artist' => $trackData['track_artist'],
            'track'  => $trackData['track_title'],
            'album' => $trackData['track_album'],
            'timestamp' => $trackData['timestamp'],
            'api_key' => $this->getLastFmApiKey(),
            'api_sig' => $this->getLastFmFunctionSignature($trackData),
            'sk'    => $this->getLastFmSessionId(),
            'method' => 'track.scrobble',
            'format' => 'json'
        );

        $response = $this->getHttpClient()
            ->post($this->getLastFmApiUrl(), ['form_params' => $queryParameters]);

        // debug stuff
        $responseArray = json_decode($response->getBody()->getContents(), true);
        $artist = $responseArray['scrobbles']['scrobble']['artist']['#text'];
        $album = $responseArray['scrobbles']['scrobble']['album']['#text'];
        $title = $responseArray['scrobbles']['scrobble']['track']['#text'];

        echo sprintf("scrobbled %s - %s (%s) \n", $artist, $title, $album);
    }

    protected function getLastFmFunctionSignature($trackData)
    {
        $signature = 'album' . $trackData['track_album'];
        $signature .= 'api_key' . $this->getLastFmApiKey();
        $signature .= 'artist' . $trackData['track_artist'];
        $signature .= 'methodtrack.scrobble';
        $signature .= 'sk' . $this->getLastFmSessionId();
        $signature .= 'timestamp' . $trackData['timestamp'];
        $signature .= 'track' . $trackData['track_title'];
        $signature .= $this->getLastFmApiSecret();

        return md5($signature);
    }

    protected function getLastScrobbledTrack()
    {
        return $this->getRecentlyScrobbledTrack(1);
    }

    protected function getCurrentlyPlayingInLastFm()
    {
        return $this->getRecentlyScrobbledTrack(0);
    }

    protected function getRecentlyScrobbledTrack($position)
    {
        $queryParameters = array(
            'limit' => $position + 1,
            'user' => $this->getLastFmUsername(),
            'api_key' => $this->getLastFmApiKey(),
            'method' => 'user.getrecenttracks',
            'format' => 'json',
            'nowplaying' => true // does not seem to have any effect, so set anyway
        );

        $response = $this->getHttpClient()
            ->get($this->getLastFmApiUrl(), ['query' => $queryParameters]);

        $responseArray = json_decode($response->getBody()->getContents(), true);

        $trackData = $responseArray['recenttracks']['track'][$position];

        return ['artist' => $trackData['artist']['#text'], 'title' => $trackData['name']];
    }
}