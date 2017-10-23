<?php

namespace ScrobblerBundle\Command;

use GuzzleHttp\Client;
use SpotifyWebAPI;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ScrobbleCurrentTrackCommand extends ContainerAwareCommand
{
    const PROGRESS_FOR_SCROBBLE = 0.75;

    protected function configure()
    {
        $this->setName('scrobble:current-track')
            ->setDescription('scrobble the current track playing on Spotify');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {
            $this->runProcess();
            sleep(1);
        }
    }

    protected function runProcess()
    {
        try {
            $currentTrackData = $this->getContainer()
                ->get('scrobbler.handler.stream_handler')
                ->getCurrentlyPlayingSpotifyTrack();

            if (!$currentTrackData) {
                return;
            }

            $this->getContainer()->get('scrobbler.handler.scrobble_handler')
                ->updateCurrentlyPlaying($currentTrackData);

            if ($this->getContainer()->get('scrobbler.handler.scrobble_handler')->trackShouldBeScrobbled($currentTrackData)) {
                $this->getContainer()->get('scrobbler.handler.scrobble_handler')->scrobbleTrack($currentTrackData);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            // todo add more error handling
        }
    }
}