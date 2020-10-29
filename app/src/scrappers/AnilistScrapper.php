<?php

namespace mangaslib\scrappers;

// Visit : https://anilist.co/graphiql
use mangaslib\models\SeriesModel;

class AnilistScrapper extends BaseScrapper {

    const BASE_URI = "https://graphql.anilist.co/";
    const ID = "anilist";

    public function createSeriesFromId(string $id) : SeriesModel {
        $query = "{
            Media(id: $id, type: MANGA) {
              id,
              title {
                romaji
                english
                native
              },
              idMal,
              format,
              volumes,
              countryOfOrigin,
              genres,
              isAdult,
              siteUrl,
              staff {
                edges {
                  id,
                  role,
                  node {
                    id,
                    name {
                      first
                      last
                      full
                      native
                    }
                  }
                }
              },
              tags {
                id,
                name,
                rank,
                isAdult,
                description,
                category
              },
              chapters,
              coverImage {
                extraLarge
                large
                medium
                color
              },
              bannerImage,
              status,
              type,
              description
            }
          }";

        $result = $this->doRequest($query);
        $json = json_decode($result, true);


        $themes = [];
        foreach ($json['data']['Media']['tags'] as $tag) {
            $themes[] = $tag['name'];
        }

        $series = new SeriesModel();
        $series->id = $id;
        $series->genres = join(',', $json['data']['Media']['genres']);
        $series->synopsis = $json['data']['Media']['description'];
        $series->thumbnail = $json['data']['Media']['coverImage']['large'];
        $series->banner = $json['data']['Media']['bannerImage'];
        $series->cover = $json['data']['Media']['coverImage']['extraLarge'];
        $series->chapters = intval($json['data']['Media']['chapters']);
        $series->volumes = intval($json['data']['Media']['volumes']);
        $series->series_status = $json['data']['Media']['status'];

        $themes = [];
        foreach ($json['data']['Media']['tags'] as $tag) {
            $themes[] = $tag['name'];
        }
        $series->themes = join(',', $themes);

        $alternateTitles = $json['data']['Media']['title'];
        if ($alternateTitles && array_key_exists('userPreferred', $alternateTitles)) {
            unset($alternateTitles['userPreferred']);
        }
        $series->alternate_titles = json_encode($alternateTitles);

        // TODO: decode that:
        /*
         "staff": {
        "edges": [
          {
            "id": 61911,
            "role": "Story & Art",
            "node": {
              "id": 97623,
              "name": {
                "first": "Tsukasa",
                "last": "Houjou",
                "full": "Tsukasa Houjou",
                "native": "北条司"
              }
            }
          }
        ]
         */
        /*if ($result['data']['Media']['staff']) {
            foreach ($result['data']['Media']['staff'] as $staff) {
                $item['staff'][] = [
                    'id' => $staff['id'],
                    'role' => $staff['role'],
                    'name' => $staff['name']['full']
                ];
            }
        }*/
        return $series;
    }

    public function searchByTitle($title) {
        $query = "query {
            Page(page: 1, perPage: 20) {
            pageInfo {
              currentPage
            },
            media(search:\"$title\", type: MANGA) {
              id,
              title {
                romaji
                english
                native
              },
                coverImage {
                medium
                color
              }
            }
          }
        }";

        $request = $this->doRequest($query);
        $result =  json_decode($request, true);
        $items = [];
        foreach ($result['data']['Page']['media'] as $media) {
            $items[] = [
                'id' => $media['id'],
                'titles' => $media['title'],
                'image' => $media['coverImage']['medium']
            ];
        }

        return $items;
    }

    private function doRequest($query) {
        $data = [
            'query' => $query,
            'variables' => null         // Example if using $var in query => variables: {page: 1, type: "MANGA", sort: "SEARCH_MATCH", search: "goddess"} or (null)
        ];

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => json_encode($data)
            ]
        ];

        $context  = stream_context_create($opts);

        return file_get_contents(self::BASE_URI, false, $context);
    }
}