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
                'chrAncestries',
                'chrBloodlines',
                'chrRaces',
                'invGroups',
                'invCategories',
                'mapRegions',
                'mapConstellations',
            ]
        ],
        'scopes'=> [
            [
                'key' => "readCharacterClones",
                'title' => "Read Character Clones",
                'scope' => "esi-clones.read_clones.v1",
                'desc' => "Scope required to access this characters clones."
            ],
            [
                'key' => "readCharacterImplants",
                'title' => "Read Character Implants",
                'scope' => "esi-clones.read_implants.v1",
                'desc' => "Returns your current clones implants. Requried to calculate skill training time."
            ],
            [
                'key' => "readCharacterSkillz",
                'title' => "Read Character Skillz",
                'scope' => "esi-skills.read_skills.v1",
                'desc' => "Scope required to access this characters skills and current attributes."
            ],
            [
                'key' => "readCharacterSkillQueue",
                'title' => "Read Character Skill Queue",
                'scope' => "esi-skills.read_skillqueue.v1",
                'desc' => "Scope required to read this character skill queue."
            ],
            [
                'key' => "readCharacterFittings",
                'title' => "Read Character Fittings",
                'scope' => "esi-fittings.read_fittings.v1",
                'desc' => "Scope required to access this characters fittings."
            ],
            [
                'key' => "writeCharacterFittings",
                'title' => "Write Character Fittings",
                'scope' => "esi-fittings.write_fittings.v1",
                'desc' => "Scope required to write a fitting to this character in game fitting manager."
            ],
            [
                'key' => "readUniverseStructures",
                'title' => "Read Universe Structures",
                'scope' => "esi-universe.read_structures.v1",
                'desc' => "Scope required to parse structure Id to human readable name. Without this scope, structure names will display as Unknown Structure <StructureId> unless we learned about that structure from another character."
            ],
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
            "overview" => "https://bitbucket.org/devoverlord/levelv",
            "issues" => "https://bitbucket.org/devoverlord/levelv/issues",
            "commit" => "https://bitbucket.org/devoverlord/levelv/commits",
            "branches" => "https://bitbucket.org/devoverlord/levelv/branches"
        ]
    ]

];
