# Mangas library

Simple personal mangas library.

This was developed for my personal library. It was to test my Framework (php-sun-framework) and revamp 
my old 2000 web site. I was young back then and some tables were weird.

## Prerequisites

* Docker (or php and mysql configured)

## Running

```Shell
docker-compose up
```

### Initialization

By default, the database is empty. You will need to add a user manually if you
want to be able to edit. Just create a simple script to create you password.
 
```PHP
<?php
echo hash('sha512', "your password here");
```

If you want to be an admin, be sure to set the ``rolw`` to 1. (Yeah yeah, there's a typo)

## Deploying

1. The script is only windows specific and use power shell.
1. You will need to generate a ssh key pair and upload the public one on your server. Usually located in your ``.ssh/authorized_keys``
1. Double click on the script or in a powershell

```Shell
cd deploy
update
```

After that, you will need to create a ``db.ini`` file on your host with the appropriate
connection string for your database. You will also need to create the tables as well.
See ``data/sql/*.sql`` scripts for easy imports.

## TODO

### Current


### Next

* login page with referer
* make categories per language? with sections? Might be more interesting for browsing.
* when click on AngeDeLaMort -> go to profile.
* Refactor the databases
    1. Modify the mangas_resources table in order to contains all the required fields for each page.
    1. When scrapping, fill those fields if they are empty and download images as well
    1. Modify the library to use the Mangas_resources table instead of the scrapper
    1. Make an "Edit Screen" with a way to merge the data (could be: 
        left side  = mangas_resources fields &
        right side = mangas_scrappers tabs with button [<-])
* Be able to choose the scrapper data more specifically. Right now it's just a priority list.