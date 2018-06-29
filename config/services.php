<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'eve' => [
        'userAgent' => "LevelV (LV) || David Davaham (David Douglas) || ddouglas@douglaswebdev.net",
        'sso' => [
            'id' => env('EVESSO_CLIENT_ID'),
            'secret' => env('EVESSO_CLIENT_SECRET'),
            'callback' => 'sso.callback', // name of the route that generates the sso call back url
            'admin' => [
                'id' => env('EVESSO_CLIENT_ID_ADMIN'),
                'secret' => env('EVESSO_CLIENT_SECRET_ADMIN'),
                'callback' => env('EVESSO_CLIENT_CALLBACK_ADMIN')
            ]
        ],
        'urls' => [
            'sso' => "https://login.eveonline.com",
            'esi' => "https://esi.evetech.net",
            'img' => "https://imageserver.eveonline.com",
            'km' => "https://zkillboard.com/",
            'dotlan' => "https://evemaps.dotlan.net/",
            'who' => "https://evewho.com/",
            'sde' => "http://sde.zzeve.com"
        ],
        'sde' => [
            'import' => [
                'invGroups','invCategories','mapRegions',
                'mapConstellations', 'chrAncestries', 'chrBloodlines',
                'chrRaces', 'chrFactions'
            ]
        ],
        'scopes'=>[
            'readCharacterClones' => "esi-clones.read_clones.v1",
            'readCharacterImplants' => "esi-clones.read_implants.v1",
            'readCharacterSkills' => "esi-skills.read_skills.v1",
            'readCharacterSkillQueue' => "esi-skills.read_skillqueue.v1",
            'readUniverseStructures' => "esi-universe.read_structures.v1",
        ],
        'dogma' => [
            'attributes' => [
                'skillz' => [
                    'all' => [
                        182,183,184,1285,1289,1290,277,278,279,1286,1287,1288
                    ],
                    'indicators' => [
                        182,183,184,1285,1289,1290
                    ],
                    'levels' => [
                        277,278,279,1286,1287,1288
                    ],
                    'map' => [
                        182 => 277,
                        183 => 278,
                        184 => 279,
                        1285 => 1286,
                        1289 => 1287,
                        1290 => 1288
                    ]
                ]
            ]
        ],
        'updateInterval' => env('JOB_STATUS_REFRESH_INTERVAL', 10)
    ],

    "bitbucket" => [
        "urls" => [
            "issues" => "https://bitbucket.org/devoverlord/levelv/issues",
            "commit" => "https://bitbucket.org/devoverlord/levelv/commits",
            "branches" => "https://bitbucket.org/devoverlord/levelv/branches"
        ]
    ]

];
