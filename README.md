Table of Contents
=================

* [Getting Started](#getting-started)
   * [Install Composer](#install-composer)
   * [Install Zuluru Code](#install-zuluru-code)
   * [Zuluru Folder Permissions](#zuluru-folder-permissions)
   * [Launch Zuluru](#launch-zuluru)
   * [Configuration](#configuration)
   * [Periodic Tasks](#periodic-tasks)
   * [Troubleshooting](#troubleshooting)
   * [Updates](#updates)
   * [Updating from Zuluru 1](#updating-from-zuluru-1)
   * [Version](#version)
* [Development](#development)
   * [Themes](#themes)

## Getting Started

### Install Composer

```sh
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Install Zuluru Code

Clone the repository and install the dependencies

```sh
git clone git@github.com:Zuluru/Zuluru3.git
cd Zuluru3
composer install
```

### Zuluru Folder Permissions

This ensures that various folders are writable by the webserver

```sh
HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
setfacl -R -m u:${HTTPDUSER}:rwx tmp logs upload
setfacl -R -d -m u:${HTTPDUSER}:rwx tmp logs upload
```

### Launch Zuluru

Configure your web server (Apache, NGINX, IIS, etc.) to point at Zuluru3/webroot

If you don't have a web server installed, you can run CakePHP's command line server:

```sh
bin/cake server
```

Note that this should never be used for a production site!

### Configuration

To run the install process, go to

    http://your.domain/installer/install

You will first need to have an empty database created and configured with a login. To date, Zuluru has only been tested on MySQL.

### Periodic Tasks

Zuluru has a number of processes that should happen on a daily (or even more often) basis, such as sending roster and attendance emails,
opening and closing leagues, deactivating old player profiles, etc. These are handled through a command-line task.

You should set up the following command to be run regularly (every 10 minutes is recommended) by your `cron` (under Linux/UNIX).

```sh
cd /path/to/zuluru && env HTTP_HOST="yourdomain.com" bin/cake scheduler > /dev/null
```

Note that the `/path/to/zuluru` will be the folder that contains things like `src`, `config` and `webroot`.

If you have a custom theme set up, your command line should reference it as well:

```sh
cd /path/to/zuluru && env DOMAIN_PLUGIN="Xyz" HTTP_HOST= ...
```

If you are running under Windows, something similar can be set up through the Task Scheduler.

### Troubleshooting

If you get error messages about invalid time zones, you may need to follow the instructions from http://dev.mysql.com/doc/refman/5.7/en/mysql-tzinfo-to-sql.html

### Updates

Ideally, you will never need to update any core Zuluru files. Assuming that this is the case, you should be able to simply update the source with:

```sh
git pull
```

If this gives you errors because you have made changes in files that there are also new changes in, this may work:

```sh
git stash
git pull
git stash pop
```

However, you should do this on a copy of your site instead of the live version, as any conflicts between your changes and those from the main repository
will cause errors which will render your site inoperative!

Regardless of which way you do the update, there may be database changes required.
If the `pull` operation reports any new files under `/config/Migrations`, you will need to:

```sh
bin/cake migrations migrate
bin/cake orm_cache clear
```

If you're not sure whether this is required, you can just run it; it's harmless if there is nothing to be done.
It's good practice to always take a database backup before doing any of this, just in case!

### Updating from Zuluru 1

The number of people running Zuluru 1, outside of sites that I control and can do manual updates on, is quite small, so not worth spending the time building an automated upgrade process.

If you need to do such an update, my recommendation would be for you to follow the install instructions above to get Zuluru 3 in a separate directory, and in a fresh database, in order to get the config files you need (`.env` and `app_local.php`).

Then, edit the `.env` to point at your existing database, and change the SECURITY_SALT value to match your existing install (old one will be in `/config/core.php`).

Then from the command line, in the new folder, run

```sh
bin/cake migrations migrate
```

to bring your existing database up-to-date with the latest changes.

Do a backup of everything first, obviously!

At this point, you should have a functional version of Z3 with all your existing data.

### Version

See `config/version.php` for the version of Zuluru currently installed.

## Development

### Themes

CakePHP applications such as Zuluru generate their output through the
use of "views". Each page in the system has a primary view, with a name
similar to the page. For example, the view for /people/edit is located
at `/src/Template/People/edit.ctp`. The page /leagues is a shortform
for /leagues/index, with a view at `/src/Template/Leagues/index.ctp`.

Many views also make use of elements, which are like mini-views that
are needed in various places. Elements are all in `/src/Template/Element`
and folders below there.

The content for emails is found under `/src/Template/Email`, with most
having both `html` and `text` versions.

CakePHP provides a way for you to replace any of these views, without
actually editing them. This is important for when you install a Zuluru
update; it will keep you from losing your customizations. To use this,
follow the [CakePHP Themes documentation](https://book.cakephp.org/3.0/en/views/themes.html).
You don't need to update any `beforeRender` function as they describe,
though; Zuluru takes care of that using your configuration. For example,
if your league is called "XYZ", you might create an `Xyz` plugin, then
edit `app_local.php` to set the name of your theme:

```php
  return [
    'App' => [
      'theme' => 'Xyz',
    ],
  ];
```

Now, copy and edit any view that you want to replace into your Xyz
folder. For example, to replace the photo upload legal disclaimer text,
you would copy `/src/Template/Element/People/photo_legal.ctp` into
`/plugins/Xyz/src/Template/Element/People/photo_legal.ctp` and
edit the resulting file. View files are PHP code, so you should have at
least a little bit of PHP knowledge if you are making complex changes.

Other common views to edit include the page header (the empty default is
found in `/src/Template/Element/Layout/header.ctp`) or the main
layout itself (`/src/Template/Layout/default.ctp`). The layout is
built to be fairly customizable without needing to resort to theming;
for example you can add additional CSS files to include with an entry in
`app_local.php`.
