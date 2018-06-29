<?php

namespace LevelV\Http\Controllers;

use Carbon, Request, Session;
use LevelV\Http\Controllers\HttpController;
use LevelV\Models\ESI\{Alliance, Character, Corporation};

use Illuminate\Support\Collection;

class DataController extends Controller
{
    public function __construct()
    {
        $this->httpCont = new HttpController;
    }

    /**
    * Makes an HTTP GET request to CCP SSO with an Authorization Code to verify the code is valid
    *
    * @param string $code Authorization Code received in the callback from CCP
    * @return Illuminate\Support\Collection
    */
    public function verifyAuthCode(string $code, string $authorization = null)
    {
        return $this->httpCont->oauthVerifyAuthCode($code, $authorization);
    }

    /**
    * Makes an HTTP GET request to ESI's verify endpoint to verify an access token.
    *
    * @param string $token Access Token from CCP
    * @return Illuminate\Support\Collection
    */
    public function verifyAccessToken(string $token)
    {
        return $this->httpCont->oauthVerifyAccessToken($token);
    }


    /**
    * Retreive the current public information, corporation, and if applicable, alliance that a character is in.
    *
    * @param int $id The Character ID of the character in question
    * @return Illuminate\Support\Collection
    */
    public function getMemberData(int $id)
    {
        $data = collect();
        //Member ID is valid, Make a request ESI /characters/{character_id} to grab some additional character info.
        $getCharacter = $this->getCharacter($id, true);
        if (!$getCharacter->get('status')) {
            return $getCharacter;
        }
        //Request for Additional Member Data was successful. Let break it down and store what we need an a property.
        $data = $data->merge(collect($getCharacter->get('payload')->getAttributes())->forget('cached_until')->forget('created_at')->forget('updated_at'));

        //No need to verify that the corporation ID is valid since we are using the value that we received from CCP.
        //Here we are requesting information about the corporation the character is in.
        $getCorporation = $this->getCorporation($data->get('corporation_id'));
        if (!$getCorporation->get('status')) {
            return $getCorporation;
        }
        //The request for corporation information was successful. Lets store what we need and move on.
        $data->put('corporation', collect($getCorporation->get('payload')->getAttributes())->forget('cached_until')->forget('created_at')->forget('updated_at'));
        if ($data->get('corporation')->has('alliance_id') && !is_null($data->get('corporation')->get('alliance_id'))) {
            $data->put('alliance_id', $data->get('corporation')->get('alliance_id'));
            $getAlliance = $this->getAlliance($data->get('alliance_id'));
            if (!$getAlliance->get('status')) {
                return $getAlliance;
            }
            $data->put('alliance', collect($getAlliance->get('payload')->getAttributes())->forget('cached_until')->forget('created_at')->forget('updated_at'));
        }

        return collect([
            'status' => true,
            'payload' => $data
        ]);
    }

    /**
    * For an ID, query ESI /characters/{character_id} for character data and return.
    *
    * @param int $id The id of the Character to query ESI for.
    * @return Illuminate\Support\Collection
    **/
    public function getCharacter($id)
    {
        $character = Character::firstOrNew(['id' => $id]);
        if (!$character->exists || $character->cached_until < Carbon::now()) {
            $request = $this->httpCont->getCharactersCharacterId($id);
            if (!$request->get('status')) {
                return $request;
            }
            $response = $request->get('payload')->get('response');

            $responseHeaders = $request->get('payload')->get('headers')->get('response');
            $data = collect([
                'name' => $response->name,
                'birthday' => Carbon::parse($response->birthday),
                'gender' => $response->gender,
                'ancestry_id' => $response->ancestry_id,
                'bloodline_id' => $response->bloodline_id,
                'race_id' => $response->race_id,
                'sec_status' => $response->security_status,
                'corporation_id' => $response->corporation_id,
                'cached_until' => isset($responseHeaders['Expires']) ? Carbon::parse($responseHeaders['Expires'])->toDateTimeString() : Carbon::now()->addHour()->toDateTimeString()
            ]);
            property_exists($response, 'alliance_id') ? $data->put('alliance_id', $response->alliance_id) : null;

            $character->fill($data->toArray());
            $character->save();
        }
        return collect([
            'status' => true,
            'payload' => $character
        ]);
    }

    /**
    * For an ID, query ESI /corporations/{corporation_id} for corporation data and return.
    *
    * @param int $id The id of the Corporation to query ESI for.
    * @return Illuminate\Support\Collection
    **/
    public function getCorporation(int $id)
    {
        $corporation = Corporation::firstOrNew(["id" => $id]);
        if (!$corporation->exists || $corporation->cached_until < Carbon::now()) {
            $request = $this->httpCont->getCorporationsCorporationId($id);
            if (!$request->get('status')) {
                return $request;
            }
            $response = $request->get('payload')->get('response');
            $responseHeaders = $request->get('payload')->get('headers')->get('response');
            $data = collect([
                'name' => $response->name,
                'ticker' => $response->ticker,
                'member_count' => $response->member_count,
                'ceo_id' => $response->ceo_id,
                'creator_id' => $response->creator_id,
                'home_station_id' => $response->home_station_id,
                'cached_until' => isset($responseHeaders['Expires']) ? Carbon::parse($responseHeaders['Expires'])->toDateTimeString() : Carbon::now()->addHour()->toDateTimeString()
            ]);
            property_exists($response, 'alliance_id') ? $data->put('alliance_id', $response->alliance_id) : null;
            property_exists($response, 'date_founded') ? $data->put('date_founded', Carbon::parse($response->date_founded)->toDateTimeString()) : null;
            $corporation->fill($data->toArray());
            $corporation->save();
        }
        return collect([
            'status' => true,
            'payload' => $corporation
        ]);
    }

    /**
    * For an ID, query ESI /alliances/{alliance_id} for alliance data and return.
    *
    * @param int $id The id of the Alliance to query ESI for.
    * @return Illuminate\Support\Collection
    **/
    public function getAlliance(int $id)
    {
        $alliance = Alliance::firstOrNew(['id' => $id]);
        if (!$alliance->exists || $alliance->cached_until < Carbon::now()) {
            $request = $this->httpCont->getAlliancesAllianceId($id);
            if (!$request->get('status')) {
                return $request;
            }
            $response = $request->get('payload')->get('response');
            $responseHeaders = $request->get('payload')->get('headers')->get('response');
            $data = collect([
                'name' => $response->name,
                'ticker' => $response->ticker,
                'creator_id' => $response->creator_id,
                'creator_corporation_id' =>$response->creator_corporation_id,
                'executor_corporation_id' => $response->executor_corporation_id,
                'cached_until' => isset($responseHeaders['Expires']) ? Carbon::parse($responseHeaders['Expires'])->toDateTimeString() : Carbon::now()->addHour()->toDateTimeString()
            ]);
            $alliance->fill($data->toArray());
            $alliance->save();
        }
        return collect([
            'status' => true,
            'payload' => $alliance
        ]);
    }
}
