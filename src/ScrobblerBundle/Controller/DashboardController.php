<?php

namespace ScrobblerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Process\Process;

class DashboardController extends Controller
{
    /**
     * @Route("/admin/run_command")
     */
    public function runAction()
    {
        // very hackish, needs a decent solution
        $fullCommand = sprintf('php %s/bin/console scrobble:current-track', $this->get('kernel')->getRootDir() . '/..');
        $process = new Process($fullCommand);
        $process->setTimeout(null);
        $process->start();

        return new RedirectResponse('/admin');
    }

    /**
     * @Route("/admin/stop_command")
     */
    public function stopAction()
    {
        // again quite the hack... does the job but not exactly elegant
        exec('kill -15 $(pgrep -f scrobble:current-track)');
        return new RedirectResponse('/admin');
    }
}