# Getting Started

### Install Composer

    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

### Install Zuluru Code

Clone the repository and install the dependencies

    git clone git@github.com:Zuluru/Zuluru3.git
    cd Zuluru3
    composer install

### Zuluru Folder Permissions

This ensures that various folders are writable by the webserver

    HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    setfacl -R -m u:${HTTPDUSER}:rwx tmp logs upload
    setfacl -R -d -m u:${HTTPDUSER}:rwx tmp logs upload

### Launch Zuluru

Configure your web server (Apache, NGINX, IIS, etc.) to point at Zuluru3/webroot

If you don't have a web server installed, you can run CakePHP's command line server:

    bin/cake server

Note that this should never be used for a production site!

### Configuration

To run the install process, go to

    http://your.domain/installer/install

You will first need to have an empty database created and configured with a login. To date, Zuluru has only been tested on MySQL.

### Periodic Tasks

Zuluru has a number of processes that should happen on a daily (or even more often) basis, such as sending roster and attendance emails,
opening and closing leagues, deactivating old player profiles, etc. These are handled through a command-line task.

You should set up the following command to be run regularly (every 10 minutes is recommended) by your `cron` (under Linux/UNIX).

    cd /path/to/zuluru && env HTTP_HOST="yourdomain.com" bin/cake scheduler > /dev/null

Note that the `/path/to/zuluru` will be the folder that contains things like `src`, `config` and `webroot`.

If you have a custom theme set up, your command line should reference it as well:

    env DOMAIN_PLUGIN=

If you are running under Windows, something similar can be set up through the Task Scheduler.

### Troubleshooting

If you get error messages about invalid time zones, you may need to follow the instructions from http://dev.mysql.com/doc/refman/5.7/en/mysql-tzinfo-to-sql.html
