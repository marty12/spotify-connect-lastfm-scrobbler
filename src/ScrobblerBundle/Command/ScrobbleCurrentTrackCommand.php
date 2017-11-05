<?php

namespace ScrobblerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrobbleCurrentTrackCommand extends ContainerAwareCommand
{
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
            if (! $this->getContainer()->get('scrobbler.handler.stream_handler')->shouldExecute()) {
                return;
            }

            $currentTrackData = $this->getContainer()
                ->get('scrobbler.handler.stream_handler')
                ->getCurrentlyPlayingSpotifyTrack();

            if (!$currentTrackData) {
                return;
            }

            $this->getContainer()->get('scrobbler.handler.scrobble_handler')
                ->updateCurrentlyPlaying($currentTrackData);

            $this->getContainer()->get('scrobbler.handler.scrobble_handler')
                ->handleTrackScrobbling($currentTrackData);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            // todo add more error handling
        }
    }
}