kPlaylist is a music database that you manage via the web. With kPlaylist you can stream your music (ogg, mp3, wav, wma, etc.), you can upload, make playlists, share, search, download and a lot more.

Distributed under the terms of the GNU General Public License v2:

> This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
>
> This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
>
> You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

## Current Status
The current master branch has been tested with the following versions:
- Apache 2.4.38 :heavy_check_mark:
- PHP 7.4.20 :heavy_check_mark:
- MySQL 5.5.60 :heavy_check_mark:

Runs in Docker on Raspberry Pi 3 and newer:
- tobi312/php:7.4-apache-arm
- hypriot/rpi-mysql:5.5

### Project History

| Date | Description |
| ---- | ----------- |
| **02 Feb 2002** | **kPlaylist 0.9b (first release)** |
| 14 Apr 2002 | kPlaylist 1.0 |
| 1 May 2002 | kPlaylist 1.1 |
| 10 July 2002 | kPlaylist 1.2 |
| 25 July 2002 | kPlaylist 1.3 |
| 26 September 2004 | kPlaylist 1.4 |
| 26 December 2004 | kPlaylist 1.5 |
| 3 August 2005 | kPlaylist 1.6 |
| 19 May 2006 | kPlaylist 1.7 |
| 20 May 2008 | kPlaylist 1.8 |
| 14 August 2014 | PHP 5.3 end of life |
| **20 March 2015** | **kPlaylist 1.8 build 512 released (final build)** |
| 3 December 2015 | PHP 7.0 released |
| **2017-2021** | **Maintenance updates to run on PHP 7.x** |
| 31 December 2018 | PHP 5.6 end of life |
| 28 November 2021 | PHP 7.4 end of active support |
| 28 November 2022 | PHP 7.4 end of security support |

### Future plans:
- PHP 8 version
- Dedicated docker image

## Maintainer notes:

#### My use case for maintaining this software

I originally found kPlaylist in 2004 and used it to stream music on Windows XP w/ Winamp at work.
Due to a new security policy, we could no longer use CDs or USB drives in our workstations.

My 1.5MBps down/0.5MBps cable internet connection could handle 2-4 concurrent streams, depending on the MP3 quality.
So I made logins for friends and coworkers and it was fun to see what they were listening to from my library.

This was after the days of Napster and while we still played CDs in our cars, some people were starting to buy iPods and other MP3 players.
It was years before Spotify, Seeqpod or even Pandora would become available.

#### Original server configuration used:

- Pentium 4 256MB RAM
- Fedora Core 6
- PHP 4.3/MySQL 4.1

## Customization:

I've tried to maintain as much of the original software as possible. In addition to PHP7 compatibility updates, the following additions have been made:
- Included example `external.css` and `external.js` files
- Options to enable playback via html5 audio or video elements from within the browser.
    - This requires setting the including `external.js` as the "External javascript" value
    - Find these options under Admin control -> Settings -> Customize

---

## Installing kPlaylist:

The web server can be configured to run either from the document root (ex. http://localhost:8080) or under a directory path (ex. http://localhost:8080/kplaylist/)

You will want to have a web server such as Apache or nginx, as well as a MySQL server already installed, or use the tested Docker images.

Copy the `example.kpconfig.php` to `kpconfig.php` to place the database credentials. Otherwise, these can be entered in the installation wizard and kPlaylist will attempt to create the file.

Upon first load in the browser, you are greeted with installation options.

#### Create new database:

- This option will attempt to create a database and user for kPlaylist using the root MySQL user (root credentials are not stored)
- If this does not work, you will instead need to create the database and user manually
- Put settings in `kpconfig.php` and restart the installation using Existing Database

#### Existing Database:

- This option will assume the MySQL user and database have already been created
- Confirm the credentials are correct
- If this does not work, confirm you can connect to the MySQL server from the web server
- In docker, use host mode networking for the MySQL container, or make sure port 3306 is exposed

#### First login:

- The user `admin` is created with password set to `admin`
- **Make sure to change the password using My -> Options in the left sidebar**

#### Next steps:
- Point to the directory where kPlaylist can find your music
- If using docker, make sure the volume bind is configured before performing this step
- Under Admin control -> Settings -> File handling, enter the directory path and click Save
- Start the music crawl from Admin control -> Update

Have fun listening to your music via the web ;-)

### Known issues:

Docker:
- Download Selected or Download Album as zip not working - needs zip package
    - tar works, zip inbuilt works
    - Consider changing the default under My -> Options if the zip binary is not available

Windows XP/Winamp client:
- Stream flac not working
- Streaming flac works using VLC

## More Resources

Homepage: http://www.kplaylist.com/

Forum: https://groups.google.com/d/forum/kplaylist
