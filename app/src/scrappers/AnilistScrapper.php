<?php

namespace mangaslib\scrappers;

// Visit : https://anilist.co/graphiql
class AnilistScrapper extends BaseScrapper {

    const BASE_URI = "https://graphql.anilist.co/";
    const ID = "anilist";

    public function getMangasInfoFromId($id) {
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

        return [
            'genres' => join(',', $json['data']['Media']['genres']),
            'themes' => join(',', $themes),
            'description' => $json['data']['Media']['description'],
            'comment' => $result,
            'rating' => 0, // TODO: could get the MAL rating
            'thumbnail' => $json['data']['Media']['coverImage']['large'],
            'scrapper_id' => AnilistScrapper::ID,
            'scrapper_mapping' => $id
        ];
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

    // TODO: remove me!!
    public static function AddExtraData(&$series, $json) {
      $result = json_decode($json, true);

      // TODO: create a place where we can see all those properties so they are not magic? maybe create a class?
      $series['banner'] = $result['data']['Media']['bannerImage'];
      $series['cover'] = $result['data']['Media']['coverImage']['extraLarge'];
      $series['chapters'] = $result['data']['Media']['chapters'];
      $series['volumes'] = $result['data']['Media']['volumes'];
      $series['status'] = $result['data']['Media']['status'];
      $series['tags'] = $result['data']['Media']['tags'];
      $series['titles'] = $result['data']['Media']['title'];

      return $series;
    }

    public function JsonToModel($json) {
        $result = json_decode($json, true);
        $item = [];

        // TODO: create a place where we can see all those properties so they are not magic? maybe create a class?
        $item['banner'] = $result['data']['Media']['bannerImage'];
        $item['cover'] = $result['data']['Media']['coverImage']['extraLarge'];
        $item['color'] = $result['data']['Media']['coverImage']['color'];
        $item['cover'] = $result['data']['Media']['coverImage']['extraLarge'];
        $item['chapters'] = $result['data']['Media']['chapters'];
        $item['volumes'] = $result['data']['Media']['volumes'];
        $item['status'] = $result['data']['Media']['status'];
        $item['tags'] = $result['data']['Media']['tags'];
        $item['titles'] = $result['data']['Media']['title'];
        $item['countryOfOrigin'] = $result['data']['Media']['countryOfOrigin'];
        $item['genres'] = $result['data']['Media']['genres'];
        $item['isAdult'] = $result['data']['Media']['isAdult'];
        $item['siteUrl'] = $result['data']['Media']['siteUrl'];
        $item['description'] = $result['data']['Media']['description'];
        $item['alternate_titles'] = $result['data']['Media']['title'];

        if (is_array($item['alternate_titles']) && array_key_exists('userPreferred', $item['alternate_titles'])) {
            unset($item['alternate_titles']['userPreferred']);
        }

        $item['staff'] = [];
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

        return $item;
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