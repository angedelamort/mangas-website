## personal migration script because of old database version

## Alter the current table in order to be able to merge.
ALTER TABLE mangas_title
    CHARACTER SET = utf8 ,
    CHANGE COLUMN `title` `title` CHAR(255) NOT NULL COMMENT 'title of the series' ,
    CHANGE COLUMN `is_complete` `library_status` TINYINT(2) NOT NULL DEFAULT '0' COMMENT 'status in the library (completed:1, abandoned:2 or in progress:0)' ,
    ADD COLUMN `rating` DECIMAL NULL AFTER `library_status`,
    ADD COLUMN `series_status` TINYINT NOT NULL DEFAULT '0' COMMENT 'status of the series oficially' AFTER `rating`,
    ADD COLUMN `short_name` VARCHAR(20) NOT NULL COMMENT 'short name' AFTER `series_status`,
    ADD COLUMN `volumes` INT(11) NULL COMMENT 'total number of volumes this series has' AFTER `short_name`,
    ADD COLUMN `chapters` INT(11) NULL COMMENT 'number of chapters' AFTER `volumes`,
    ADD COLUMN `editors` TEXT NULL AFTER `chapters`,
    ADD COLUMN `authors` TEXT NULL COMMENT 'list of authors, coma separated' AFTER `editors`,
    ADD COLUMN `genres` TEXT NULL COMMENT 'list of genres, coma separated' AFTER `authors`,
    ADD COLUMN `themes` TEXT NULL AFTER `genres`,
    ADD COLUMN `synopsis` TEXT NULL AFTER `genres`,
    ADD COLUMN `comments` TEXT NULL AFTER `synopsis`,
    ADD COLUMN `cover` TEXT NULL COMMENT 'url of the cover' AFTER `comments`,
    ADD COLUMN `banner` TEXT NULL COMMENT 'url of the banner' AFTER `cover`,
    ADD COLUMN `thumbnail` TEXT NULL COMMENT 'url of the thumbnail' AFTER `banner`,
    ADD COLUMN `alternate_titles` TEXT NULL COMMENT 'json format of the alternate titles' AFTER `thumbnail`;



## Migrate existing data to the newly modified table.
UPDATE mangas_title t, mangas_ressources r
    SET t.short_name = r.short_name, t.editors = r.editor, t.authors = r.author, t.comments = r.comments
    WHERE t.id = r.title_id;


## Using scrapper, fill the remaining fields.
UPDATE mangas_title t, mangas_scrapper s
    SET t.genres = s.genres, t.themes = s.themes, t.synopsis = s.description, t.thumbnail = s.thumbnail
    WHERE t.id = s.id and s.scrapper_id = 'anilist';

## Rename tables
ALTER TABLE mangas_title RENAME TO mangas_series;
ALTER TABLE mangas_info CHARACTER SET = utf8, RENAME TO mangas_volume;

# Remove unused table
DROP TABLE mangas_ressources;