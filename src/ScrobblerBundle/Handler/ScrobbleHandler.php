<?php

namespace ScrobblerBundle\Handler;

use Doctrine\ORM\EntityManager;
use ScrobblerBundle\Api\LastFm;
use ScrobblerBundle\Entity\Configuration;

// todo decouple scrobble handler and last.fm implementation

class ScrobbleHandler
{
    const PROGRESS_FOR_SCROBBLE = 0.75;

    /** @var EntityManager */
    private $entityManager;

    /** @var LastFm */
    private $lastFmApi;

    /**
     * ScrobbleHandler constructor.
     * @param LastFm $lastFmApi
     * @param EntityManager $entityManager
     */
    public function __construct(LastFm $lastFmApi, EntityManager $entityManager)
    {
        $this->setEntityManager($entityManager);
        $this->setLastFmApi($lastFmApi);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     * @return ScrobbleHandler
     */
    public function setEntityManager(EntityManager $entityManager): ScrobbleHandler
    {
        $this->entityManager = $entityManager;
        return $this;
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

        $this->getScrobblerConfiguration()->setCurrentNowPlayingTrackArtist($trackData['track_artist']);
        $this->getScrobblerConfiguration()->setCurrentNowPlayingTrackTitle($trackData['track_title']);

        // set last scrobbled to null to trigger scrobbling when the playback threshold has been reached
        $this->getScrobblerConfiguration()->setLastScrobbledTrackArtist(null);
        $this->getScrobblerConfiguration()->setLastScrobbledTrackTitle(null);

        $this->getEntityManager()->flush();

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

            $this->getScrobblerConfiguration()->setLastScrobbledTrackArtist($trackData['track_artist']);
            $this->getScrobblerConfiguration()->setLastScrobbledTrackTitle($trackData['track_title']);
            $this->getEntityManager()->flush();
        }

        return $this;
    }

    /**
     * @param $trackData
     * @return bool
     */
    protected function currentlyPlayingShouldBeUpdated($trackData): bool
    {
        $configurationData = $this->getScrobblerConfiguration();

        // todo track could have been played before, this is not taken into account yet
        return ! ($configurationData->getCurrentNowPlayingTrackArtist() == $trackData['track_artist']
            && $configurationData->getCurrentNowPlayingTrackTitle() == $trackData['track_title']);
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

        $configurationData = $this->getScrobblerConfiguration();

        // todo track could have been played before, this is not taken into account yet
        return ! ($configurationData->getLastScrobbledTrackArtist() == $trackData['track_artist']
            && $configurationData->getLastScrobbledTrackTitle() == $trackData['track_title']);
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

    /**
     * @return null|object|\ScrobblerBundle\Entity\Configuration
     */
    protected function getScrobblerConfiguration()
    {
        // for now only one row, so always the first one. later on it should contain more configurations
        $configuration = $this->getEntityManager()
            ->getRepository('ScrobblerBundle:Configuration')
            ->find(1);

        if (! $configuration) {
            $configuration = new Configuration();
            $this->getEntityManager()->persist($configuration);
        }

        return $configuration;
    }
}