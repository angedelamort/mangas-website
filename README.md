# Mangas library

Simple personal mangas library.

more than 20 years ago I developed a simple site for my personal library. Since it was kind of out of date because
of some deprecated features, I decided to refactor it in this small project. 
I also wanted to test my Framework (php-sun-framework) I make a couple of years ago and see if it was worth it. 

## Prerequisites

* Docker or equivalent (php and mysql configured)

## Running

```Shell
docker-compose up
```

### Initialization

When the ``docker-compose`` starts, it will use the script(s) in ``data/sql/*`` to
initialize the database. By default, the database is empty.

When you launch the website for the first time, you will be prompted to set up the database and create a new admin user. 

## Deploying

Requirements:
* The script is windows specific and was made using power shell.
* You will need to generate a ssh key pair and upload the public one on your server. Usually located in your ``.ssh/authorized_keys``.
* Double click on the script or type directly in a powershell

```Shell
cd deploy
update
```

## Using the site

As an anonymous user, it's quite simple. you just have to navigate the site through the different simple pages.

As an administrator, you can add, edit or delete mangas series. You can also edit the wishlist when you click on the
user once logged in.

## TODO

* Remove all FORM-SUBMIT and replace with real API calls.
    * all APIs should return a JSON object
* uploader assets manually (in the edit windows)
* make categories per language? with sections? Might be more interesting for browsing.
* when click on profile -> (change name/mail/password).
* Make theming more easily.