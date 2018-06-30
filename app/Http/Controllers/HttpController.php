<?php

namespace LevelV\Http\Controllers;

use Curl;

class HttpController extends Controller
{
    public function request (array $headers, string $method, string $url, string $path, $data, boolean $logData=null)
    {
        $curl = new Curl();
        foreach ($headers as $key=>$value) {
            $curl->setHeader($key, $value);
        }
        $curl->{$method}($url.$path, $data);

        $code = $curl->httpStatusCode;
        $status = $code >= 200 && $code <=299 ? true : false;

        $message = "";
        $requestHeaders = collect($curl->requestHeaders);
        if ($logData){
            $requestHeaders->put('data', $data);
        }
        $response = collect([
            'status' => $status,
            'payload' => collect([
                'message' => $message,
                'url' => $curl->url,
                'code' => $code,
                'headers' => collect([
                    'request' => $requestHeaders,
                    'response' => collect($curl->responseHeaders)
                ]),
                'response' => $curl->response
            ])
        ]);
        if (!$status){
            $message = "Failed HTTP Request ". strtoupper($method). " " . $path . " : Http Status ". $curl->httpStatusCode;
            if ($url === config('services.eve.urls.sso')) {
                if (property_exists($curl->response, 'error') && property_exists($curl->response, 'error_description')) {
                    $message .= " || Error: ". $curl->response->error . " || Error Description: ". $curl->response->error_description;
                }

            }
            if ($url === config('services.eve.urls.esi')) {
                if (property_exists($curl->response, 'error')) {
                    $message .= " || Error: ". $curl->response->error;
                }
            }
            $response->get('payload')->put('message', $message);
            activity((new \ReflectionClass($this))->getShortName())->withProperties($response->toArray())->log($message);
        }

        return $response;
    }

    public function oauthVerifyAuthCode (string $code, string $authorization = null)
    {
        return $this->request([
            "Authorization" => "Basic ". (!is_null($authorization) ?  $authorization : base64_encode(config("services.eve.sso.id").":".config("services.eve.sso.secret"))),
            "Content-Type" => "application/x-www-form-urlencoded",
            "Host" => "login.eveonline.com",
            "User-Agent" => config("services.eve.userAgent")
        ], 'post', config('services.eve.urls.sso'),"/oauth/token", [
            'grant_type' => "authorization_code",
            'code' => $code
        ]);
    }

    public function oauthVerifyAccessToken (string $token)
    {
        return $this->request([
            "Authorization" => "Bearer ".$token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/verify/", []);
    }

    public function postRefreshToken (string $token)
    {
        return $this->request([
            "Authorization" => "Basic ".base64_encode(config("services.eve.sso.id").":".config("services.eve.sso.secret")),
            "Content-Type" => "application/json",
            "Host" => "login.eveonline.com",
            "User-Agent" => config("services.eve.userAgent")
        ], 'post', config("services.eve.urls.sso"),"/oauth/token", json_encode([
            "grant_type" => "refresh_token",
            "refresh_token" => $token
        ]));
    }

    public function postRevokeToken (string $token, string $hint = "refresh_token")
    {
        return $this->request([
            "Authorization" => "Basic ".base64_encode(config("services.eve.sso.id").":".config("services.eve.sso.secret")),
            "Content-Type" => "application/json",
            "Host" => "login.eveonline.com",
            "User-Agent" => config("services.eve.userAgent")
        ], 'post', config("services.eve.urls.sso"),"/oauth/revoke", json_encode([
            "token_type_hint" => $hint,
            "token" => $token
        ]));
    }

    public function getCharactersCharacterId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'), "/v4/characters/{$id}/", []);
    }

    public function getCorporationsCorporationId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v4/corporations/{$id}/", []);
    }

    public function getAlliancesAllianceId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v3/alliances/{$id}/", []);
    }

    public function getStatus ()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/status/", []);
    }

    public function getCharactersCharacterIdClones($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v3/characters/{$id}/clones/", []);
    }

    public function getCharactersCharacterIdFittings ($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/characters/{$id}/fittings/", []);
    }

    public function getCharactersCharacterIdImplants($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/characters/{$id}/implants/", []);
    }

    public function getCharactersCharacterIdWallet ($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/characters/{$id}/wallet/", []);
    }

    public function getCharactersCharacterIdSkillz ($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v4/characters/{$id}/skills/", []);
    }

    public function getCharactersCharacterIdSkillqueue ($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v2/characters/{$id}/skillqueue/", []);
    }

    public function getCharactersCharacterIdAttributes ($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/characters/{$id}/attributes/", []);
    }

    public function getUniverseStationsStationId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v2/universe/stations/{$id}/", []);
    }

    public function getUniverseStructuresStructureId ($id, $token)
    {
        return $this->request([
            "Authorization" => "Bearer ". $token,
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/universe/structures/{$id}/", []);
    }

    public function getUniverseSystemsSystemId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v2/universe/systems/{$id}/", []);
    }

    public function getUniverseTypesTypeId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v3/universe/types/{$id}/", []);
    }

    public function getDogmaAttributesAttributeId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v1/dogma/attributes/{$id}/", []);
    }

    public function getDogmaEffectsEffectId ($id)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.esi'),"/v2/dogma/effects/{$id}/", []);
    }

    public function postUniverseNames ($ids)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'post', config('services.eve.urls.esi'),"/v2/universe/names/", json_encode($ids));
    }

    public function postUniverseIds ($names)
    {
        return $this->request([
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'post', config('services.eve.urls.esi'),"/v1/universe/ids/", json_encode($names));
    }

    public function getChrAncestries()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/chrAncestries.json", []);
    }
    public function getChrBloodlines()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/chrBloodlines.json", []);
    }

    public function getChrFactions()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/chrFactions.json", []);
    }

    public function getChrRaces()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/chrRaces.json", []);
    }
    public function getInvCategories()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/invCategories.json", []);
    }
    public function getInvGroups()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/invGroups.json", []);
    }

    public function getMapConstellations()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/mapConstellations.json", []);
    }

    public function getMapRegions()
    {
        return $this->request([
            "Content-Type" => "application/json",
            "User-Agent" => config("services.eve.userAgent")
        ], 'get', config('services.eve.urls.sde'),"/mapRegions.json", []);
    }
}
