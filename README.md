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

When the ``docker-compose`` starts, it will use the script(s) in ``data/sql/*`` to
initialize the database. By default, the database is empty.

You will be prompted to create a new user. If you are on a remote server, you can
either create a ``db.ini``. or use the wizard when loading the site for the first time.

## Deploying

1. The script is only windows specific and use power shell.
1. You will need to generate a ssh key pair and upload the public one on your server. Usually located in your ``.ssh/authorized_keys``
1. Double click on the script or in a powershell

```Shell
cd deploy
update
```

After that, as mentioned in the previous section, you will need a ``db.ini`` file on your host 
with the appropriate connection string for your database. You will also need to create 
the tables as well before running the site. See ``data/sql/*.sql`` scripts for easy imports.

## Using the site

TODO: describe the different sections and how to use them.

## TODO

### Current

* Test all pages and actions and fix bugs then publish

### Next

* Make a merge screen when updating from scrapper? Probably go to another page already filled with 2 rows?
    * If doing that, we can probably delete the mangas_scrapper table.
* Probably remove all FORM-SUBMIT and replace with real API calls.
    * all APIs should return a JSON object
* uploader assets manually (in the edit windows)
* make categories per language? with sections? Might be more interesting for browsing.
* when click on profile -> (change name/mail/password).
* recreate an initialization script instead of having updates.
    * remove migration button
* Do a docker-compose down and re-test everything and remove useless scripts