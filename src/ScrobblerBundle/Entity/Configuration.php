<?php

namespace ScrobblerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Configuration
 *
 * @ORM\Table(name="configuration")
 * @ORM\Entity(repositoryClass="ScrobblerBundle\Repository\ConfigurationRepository")
 */
class Configuration
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="last_scrobbled_track_artist", type="string", length=255, nullable=true)
     */
    private $lastScrobbledTrackArtist;

    /**
     * @var string
     *
     * @ORM\Column(name="last_scrobbled_track_title", type="string", length=255, nullable=true)
     */
    private $lastScrobbledTrackTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="current_now_playing_track_artist", type="string", length=255, nullable=true)
     */
    private $currentNowPlayingTrackArtist;

    /**
     * @var string
     *
     * @ORM\Column(name="current_now_playing_track_title", type="string", length=255, nullable=true)
     */
    private $currentNowPlayingTrackTitle;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Configuration
     */
    public function setId(int $id): Configuration
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastScrobbledTrackArtist()
    {
        return $this->lastScrobbledTrackArtist;
    }

    /**
     * @param string|null $lastScrobbledTrackArtist
     * @return Configuration
     */
    public function setLastScrobbledTrackArtist($lastScrobbledTrackArtist): Configuration
    {
        $this->lastScrobbledTrackArtist = $lastScrobbledTrackArtist;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastScrobbledTrackTitle()
    {
        return $this->lastScrobbledTrackTitle;
    }

    /**
     * @param string|null $lastScrobbledTrackTitle
     * @return Configuration
     */
    public function setLastScrobbledTrackTitle($lastScrobbledTrackTitle): Configuration
    {
        $this->lastScrobbledTrackTitle = $lastScrobbledTrackTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentNowPlayingTrackArtist()
    {
        return $this->currentNowPlayingTrackArtist;
    }

    /**
     * @param string|null $currentNowPlayingTrackArtist
     * @return Configuration
     */
    public function setCurrentNowPlayingTrackArtist($currentNowPlayingTrackArtist): Configuration
    {
        $this->currentNowPlayingTrackArtist = $currentNowPlayingTrackArtist;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentNowPlayingTrackTitle()
    {
        return $this->currentNowPlayingTrackTitle;
    }

    /**
     * @param string $currentNowPlayingTrackTitle
     * @return Configuration
     */
    public function setCurrentNowPlayingTrackTitle(string $currentNowPlayingTrackTitle): Configuration
    {
        $this->currentNowPlayingTrackTitle = $currentNowPlayingTrackTitle;
        return $this;
    }
}

