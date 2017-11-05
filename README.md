Spotify Connect Last.fm Scrobbler
=========

This is an application written in PHP using Symfony 3 that scrobbles currently playing tracks on Spotify Connect to Last.fm (since the official clients do not support this).

## Installation

Use composer to install the necessary dependencies

``` bash
composer install
```

## Usage

**NOTE: this application is currently in alpha phase and may not always work as expected.**

To use this application, you need a Spotify API account and a Last.fm API account:

Spotify: https://developer.spotify.com/my-applications

Last.fm: https://www.last.fm/api/account/create

Make sure you have a web server running with PHP-support. After setting the right parameters in parameters.yml, open http://localhost and you should be redirected to Spotify. Allow API access and when returned to your callback url (default localhost/set_token), copy the refresh token to parameters.yml

After this, run the following command (replace username and password with your own credentials):

``` bash
php bin/console lastfm:create-session-id --username --password
```

Copy the session id to parameters.yml. After this, you should be ready to go! Run the following to start listening and scrobbling when necessary:

``` bash
php bin/console scrobble:current-track
```
