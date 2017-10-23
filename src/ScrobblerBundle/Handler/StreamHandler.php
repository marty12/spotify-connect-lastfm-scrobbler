<?php

namespace ScrobblerBundle\Handler;

use SpotifyWebAPI;

// todo decouple stream handler and spotify implementation
class StreamHandler
{
    /** @var string */
    private $spotifyAccessToken = '';

    /** @var string */
    private $spotifyRefreshToken = '';

    /** @var SpotifyWebAPI\SpotifyWebAPI */
    private $spotifyWebApi;

    /** @var SpotifyWebapi\Session */
    private $spotifyWebApiSession;


    public function __construct($spotifyRefreshToken, SpotifyWebAPI\SpotifyWebAPI $spotifyWebAPI, SpotifyWebAPI\Session $spotifyWebApiSession)
    {
        $this->spotifyWebApi = new SpotifyWebAPI\SpotifyWebAPI();
        $this->spotifyWebApi->setReturnType(SpotifyWebAPI\SpotifyWebAPI::RETURN_ASSOC);

        $this->spotifyWebApiSession = $spotifyWebApiSession;

        $this->setSpotifyRefreshToken($spotifyRefreshToken);
        $this->refreshSpotifyAccessToken();
    }

    /**
     * @return SpotifyWebAPI\SpotifyWebAPI
     */
    public function getSpotifyWebApi(): SpotifyWebAPI\SpotifyWebAPI
    {
        return $this->spotifyWebApi;
    }

    /**
     * @param SpotifyWebAPI\SpotifyWebAPI $spotifyWebApi
     */
    public function setSpotifyWebApi(SpotifyWebAPI\SpotifyWebAPI $spotifyWebApi)
    {
        $this->spotifyWebApi = $spotifyWebApi;
    }

    /**
     * @return SpotifyWebAPI\Session
     */
    public function getSpotifyWebApiSession(): SpotifyWebAPI\Session
    {
        return $this->spotifyWebApiSession;
    }

    /**
     * @param SpotifyWebAPI\Session $spotifyWebApiSession
     */
    public function setSpotifyWebApiSession(SpotifyWebAPI\Session $spotifyWebApiSession)
    {
        $this->spotifyWebApiSession = $spotifyWebApiSession;
    }

    public function getCurrentlyPlayingSpotifyTrack()
    {
        try {
            $spotifyCurrentTrackData = $this->getSpotifyWebApi()->getMyCurrentTrack();
        } catch (\Exception $e) {
            if ($e->getCode() == 401) {
                $this->refreshSpotifyAccessToken();
                echo "refreshed spotify access token \n"; //todo remove debug line
            }

            // todo should point to itself, but better error handling is required to prevent an infinite loop
            $spotifyCurrentTrackData = $this->getSpotifyWebApi()->getMyCurrentTrack();
        }

        if (! isset($spotifyCurrentTrackData['item'])) {
            return false;
        }

        return array(
            'timestamp' => time(),
            'current_track_progress' => $spotifyCurrentTrackData['progress_ms'],
            'track_total_time' => $spotifyCurrentTrackData['item']['duration_ms'],
            'track_artist' => $spotifyCurrentTrackData['item']['artists'][0]['name'], // todo concatenate artists
            'track_title'  => $spotifyCurrentTrackData['item']['name'],
            'track_album'  => $spotifyCurrentTrackData['item']['album']['name']
        );
    }

    protected function refreshSpotifyAccessToken()
    {
        $this->getSpotifyWebApiSession()
            ->refreshAccessToken($this->getSpotifyRefreshToken());

        $this->setSpotifyAccessToken($this->getSpotifyWebApiSession()->getAccessToken());
        $this->getSpotifyWebApi()->setAccessToken($this->getSpotifyAccessToken());
    }

    /**
     * @return string
     */
    protected function getSpotifyAccessToken(): string
    {
        return $this->spotifyAccessToken;
    }

    /**
     * @param string $spotifyAccessToken
     */
    protected function setSpotifyAccessToken(string $spotifyAccessToken)
    {
        $this->spotifyAccessToken = $spotifyAccessToken;
    }

    /**
     * @return string
     */
    protected function getSpotifyRefreshToken(): string
    {
        return $this->spotifyRefreshToken;
    }

    /**
     * @param string $spotifyRefreshToken
     * @return StreamHandler
     */
    protected function setSpotifyRefreshToken(string $spotifyRefreshToken): StreamHandler
    {
        $this->spotifyRefreshToken = $spotifyRefreshToken;
        return $this;
    }
}