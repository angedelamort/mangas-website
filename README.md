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

By default, the database is empty. You will need to add a user manually.

## Deploying

1. The script is only windows specific and use power shell.
1. You will need to generate a ssh key pair and upload the public one on your server. Usually located in your ``.ssh/authorized_keys``
1. Double click on the script or

```Shell
cd deploy
update
```

After that, you will need to create a ``db.ini`` file on your host with the appropriate
connection string for your database. You will also need to create the tables as well.
See ``data/sql/*.sql`` scripts for easy imports.

## TODO

### Current

* Committing in gitlab. 

### Next

* Pass the todos
* login page with referer
* make categories per language? with sections? Might be more interesting for browsing.
* when click on AngeDeLaMort -> go to profile.
