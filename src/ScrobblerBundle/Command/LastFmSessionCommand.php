<?php

declare(strict_types = 1);

namespace ScrobblerBundle\Command;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LastFmSessionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('lastfm:create-session-id')
            ->setDescription('create a last.fm session id')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'last.fm username')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'last.fm password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption('username');
        $password = $input->getOption('password');

        $apiKey = $this->getContainer()->getParameter('lastfm_api_key');
        $apiSecret = $this->getContainer()->getParameter('lastfm_api_secret');
        $signature = md5(sprintf('api_key%smethodauth.getMobileSessionpassword%susername%s%s',
            $apiKey,
            $password,
            $username,
            $apiSecret));

        $queryParameters = array(
            'username' => $username,
            'password' => $password,
            'api_key' => $apiKey,
            'api_sig' => $signature
        );

        $client = new Client();
        $url = $this->getContainer()->getParameter('lastfm_api_url') . '?method=auth.getMobileSession&format=json';
        $response = $client->post($url, ['form_params' => $queryParameters]);
        echo $response->getBody();

    }
}