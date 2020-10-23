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

* Refactor the databases
    * [x] Merge 2 tables (mangas_title + mangas_resource in manga_title)
    * [x] Modify the mangas_resources table in order to contains all the required fields for each page.
    * [x] rename the mangas_info to mangas_volume and mangas_titles to mangas_series
    * [x] Modify the library to use the Mangas_resources table instead of the scrapper
    * [ ] When scrapping, fill those fields if they are empty and download images as well
    * [ ] Make an "Edit Screen" with a way to merge the data (could be: 
        left side  = mangas_resources fields &
        right side = mangas_scrappers tabs with button [<-])
        or dropdown with scrapper info.

### Next

* Make Models (Series, Volume, etc) so we can remove "library.php" and have self contained objects.
* Add edit flag Completed!
* uploader assets manually
* make categories per language? with sections? Might be more interesting for browsing.
* when click on profile -> go to profile (change name/mail/password).
* Add les tags (table?)
* add an error_log with the request mysql in debug.

### Wish list
* One Punch Man
* Demon Slayer - Kimestu no Yaiba
* Berserk
* X
* Jojos (new series)
* Hikaru no Go
* Hunter x Hunter
* Naruto
* Yu-gi-Oh