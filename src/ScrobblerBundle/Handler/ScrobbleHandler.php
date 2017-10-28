<?php

namespace ScrobblerBundle\Handler;

use ScrobblerBundle\Api\LastFm;

// todo decouple scrobble handler and last.fm implementation

class ScrobbleHandler
{
    const PROGRESS_FOR_SCROBBLE = 0.75;

    /** @var LastFm */
    private $lastFmApi;

    /**
     * ScrobbleHandler constructor.
     * @param LastFm $lastFmApi
     */
    public function __construct(LastFm $lastFmApi)
    {
        $this->lastFmApi = $lastFmApi;
    }

    /**
     * @return LastFm
     */
    public function getLastFmApi(): LastFm
    {
        return $this->lastFmApi;
    }

    /**
     * @param LastFm $lastFmApi
     * @return ScrobbleHandler
     */
    public function setLastFmApi(LastFm $lastFmApi): ScrobbleHandler
    {
        $this->lastFmApi = $lastFmApi;
        return $this;
    }

    /**
     * @param array $trackData
     * @return ScrobbleHandler
     */
    public function updateCurrentlyPlaying(array $trackData): ScrobbleHandler
    {
        if (! $this->currentlyPlayingShouldBeUpdated($trackData)) {
            return $this;
        }

        $this->getLastFmApi()
            ->updateCurrentlyPlaying($trackData['track_artist'], $trackData['track_title'], $trackData['track_album']);

        return $this;
    }

    /**
     * @param $trackData
     * @return ScrobbleHandler
     */
    public function handleTrackScrobbling($trackData): ScrobbleHandler
    {
        if ($this->trackShouldBeScrobbled($trackData)) {
            $this->getLastFmApi()
                ->scrobbleTrack($trackData['track_artist'], $trackData['track_title'], $trackData['track_album'], time());
        }

        return $this;
    }

    /**
     * @param $trackData
     * @return bool
     */
    protected function currentlyPlayingShouldBeUpdated($trackData): bool
    {
        $trackData = $this->getSanitizedDataForComparison($trackData);
        $currentlyPlayingLastFmData = $this->getSanitizedDataForComparison($this->getLastFmApi()->getCurrentlyPlaying());

        return ! ($currentlyPlayingLastFmData['artist'] == $trackData['track_artist']
            && $currentlyPlayingLastFmData['title'] == $trackData['track_title']);
    }

    /**
     * @param $trackData
     * @return bool
     */
    protected function trackShouldBeScrobbled($trackData): bool
    {
        if (($trackData['current_track_progress'] / $trackData['track_total_time']) <= self::PROGRESS_FOR_SCROBBLE) {
            return false;
        }

        $lastScrobbledTrackData = $this->getSanitizedDataForComparison($this->getLastFmApi()->getLastScrobbledTrack());
        $trackData = $this->getSanitizedDataForComparison($trackData);

        return ! ($lastScrobbledTrackData['artist'] == $trackData['track_artist']
            && $lastScrobbledTrackData['title'] == $trackData['track_title']);
    }

    /**
     * @param $data
     * @return array
     */
    protected function getSanitizedDataForComparison(array $data): array
    {
        foreach ($data as &$value) {
            $value = strtolower(trim($value));
        }

        return $data;
    }
}