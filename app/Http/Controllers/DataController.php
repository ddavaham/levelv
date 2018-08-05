<?php

namespace LevelV\Http\Controllers;

use Carbon, Request, Session;
use LevelV\Models\{JobStatus, Member};
use LevelV\Models\ESI\{Alliance, Character, Corporation, Station, Structure, System, Type};

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

    public function getMemberAttributes(Member $member)
    {
        $request = $this->httpCont->getCharactersCharacterIdAttributes($member->id, $member->access_token);
        if (!$request->get('status')) {
            return $request;
        }
        $response = collect($request->get('payload')->get('response'));

        $member->attributes = $response->toJson();
        $member->save();

        return $request;
    }

    public function getMemberClones(Member $member)
    {
        $request = $this->httpCont->getCharactersCharacterIdClones($member->id, $member->access_token);
        if (!$request->get('status')) {
            return $request;
        }
        $response = collect($request->get('payload')->get('response'))->recursive();

        $homeLocation = $response->get('home_location');
        $now = now(); $x = 0; $dispatchedJobs = collect();
        if ($homeLocation->get('location_type') === "structure") {
            $class = \LevelV\Jobs\ESI\GetStructure::class;
            $shouldDispatch = $this->shouldDispatchJob($class, ['memberId' => $member->id, 'id' => $homeLocation->get('location_id')]);
            if ($shouldDispatch) {
                $job = new $class($member->id, $homeLocation->get('location_id'));
                $job->delay(now());
                $this->dispatch($job);
                $dispatchedJobs = $dispatchedJobs->push($job->getJobStatusId());
            }
        } elseif ($homeLocation->get('location_type') === "station") {
            $class = \LevelV\Jobs\ESI\GetStation::class;
            $shouldDispatch = $this->shouldDispatchJob($class, ['id' => $homeLocation->get('location_id')]);
            if ($shouldDispatch) {
                $job = new $class($homeLocation->get('location_id'));
                $job->delay(now());
                $this->dispatch($job);
                $dispatchedJobs = $dispatchedJobs->push($job->getJobStatusId());
            }
        }
        $member->fill([
            'clone_location_id' => $homeLocation->get('location_id'),
            'clone_location_type' => $homeLocation->get('location_type')
        ]);
        $member->save();

        if ($response->get('jump_clones')->isNotEmpty()) {
            $clones = collect();
            $response->get('jump_clones')->keyBy('jump_clone_id')->each(function ($clone) use ($member, $clones, $dispatchedJobs, &$now, &$x) {
                $clones->push([
                    'clone_id' => $clone->get('jump_clone_id'),
                    'location_id' => $clone->get('location_id'),
                    'location_type' => $clone->get('location_type'),
                    'implants' => $clone->get('implants')->toJson()
                ]);
                if ($clone->get('location_type') === "structure") {
                    $structure = Structure::firstOrNew(['id' => $clone->get('location_id')]);
                    if (!$structure->exists || $structure->name === "Unknown Structure ". $clone->get('location_id')) {
                        if ($member->scopes->contains('esi-universe.read_structures.v1')) {
                            $class = \LevelV\Jobs\ESI\GetStructure::class;
                            $shouldDispatch = $this->shouldDispatchJob($class, ['memberId' => $member->id, 'id' => $clone->get('location_id')]);
                            if ($shouldDispatch) {
                                $job = new $class($member->id, $clone->get('location_id'));
                                $job->delay($now);
                                $this->dispatch($job);
                                $dispatchedJobs = $dispatchedJobs->push($job->getJobStatusId());
                            }
                        } else {
                            $structure->fill(['name' => "Unknown Structure " . $clone->get('location_id')]);
                            $structure->save();
                        }
                    }
                } elseif ($clone->get('location_type') === "station") {
                    $station = Station::firstOrNew(['id' => $clone->get('location_id')]);
                    if (!$station->exists) {
                        $class = \LevelV\Jobs\ESI\GetStation::class;
                        $shouldDispatch = $this->shouldDispatchJob($class, ['id' => $clone->get('location_id')]);
                        if ($shouldDispatch) {
                            $job = new $class($clone->get('location_id'));
                            $job->delay($now);
                            $this->dispatch($job);
                            $dispatchedJobs = $dispatchedJobs->push($job->getJobStatusId());
                        }
                    }
                }
            });
            $member->clones()->delete();
            $member->clones()->createMany($clones->toArray());
        }
        $member->jobs()->attach($dispatchedJobs->toArray());

        return $request;
    }

    public function getMemberImplants(Member $member)
    {
        $request = $this->httpCont->getCharactersCharacterIdImplants($member->id, $member->access_token);
        if (!$request->get('status')) {
            return $request;
        }
        $response = collect($request->get('payload')->get('response'));
        $response->each(function ($implant) {
            $this->getType($implant);
        });
        $member->implants = $response->toJson();
        $member->save();

        return $request;
    }

    // Method from the Character Skils Namepsace

    /**
    * Fetches and Parses the current skills of the member
    *
    * @param LevelV\Models\Member $member Instance of Eloquent Member Model. This model contains the id and token we need to make the call.
    * @return mixed
    */
    public function getMemberSkillz (Member $member)
    {
        $request = $this->httpCont->getCharactersCharacterIdSkillz($member->id, $member->access_token);
        if (!$request->get('status')) {
            return $request;
        }
        $response = collect($request->get('payload')->get('response'))->recursive();
        $skills = $response->get('skills')->keyBy('skill_id');
        $skillIds = $skills->keys();
        $knownTypes = Type::whereIn('id', $skillIds->toArray())->get()->keyBy('id');
        $now = now(); $x = 0; $dispatchedJobs = collect();
        $skills->diffKeys($knownTypes)->each(function ($skill) use ($dispatchedJobs, &$now, &$x) {
            $class = \LevelV\Jobs\ESI\GetType::class;
            $shouldDispatch = $this->shouldDispatchJob($class, ['id' => $skill->get('skill_id')]);
            if ($shouldDispatch) {
                $job = new $class($skill->get('skill_id'));
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs = $dispatchedJobs->push($job->getJobStatusId());
            }
            if ($x%10==0) {
                $now->addSecond();
            }
            $x++;
        });
        $member->jobs()->attach($dispatchedJobs->toArray());
        $member->skillz()->detach();
        $member->skillz()->attach($skills->toArray());
        $member->total_sp = $response->get('total_sp');
        $member->save();

        return $request;
    }

    public function getMemberSkillQueue(Member $member)
    {
        $request = $this->httpCont->getCharactersCharacterIdSkillqueue($member->id, $member->access_token);
        if (!$request->get('status')) {
            return $request;
        }
        $response = collect($request->get('payload')->get('response'))->recursive()->keyBy('queue_position');
        $skillIds = $response->pluck('skill_id')->unique()->values();
        $knownTypes = Type::whereIn('id', $skillIds->toArray())->get();
        $knownTypeIds = $knownTypes->pluck('id');

        $now = now(); $x = 0; $dispatchedJobs = collect();
        $skillIds->diff($knownTypeIds)->each(function ($skill) use ($dispatchedJobs, &$now, &$x){
            $class = \LevelV\Jobs\ESI\GetType::class;
            $shouldDispatch = $this->shouldDispatchJob($class, ['id' => $skill]);
            if ($shouldDispatch) {
                $job = new $class($skill);
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs = $dispatchedJobs->push($job->getJobStatusId());
            }
            if ($x%10==0) {
                $now->addSecond();
            }
            $x++;
        });

        $queue = collect();

        $response->each(function ($queue_item) use ($queue) {
            if ($queue_item->has('finish_date') && Carbon::parse($queue_item->get('finish_date'))->lt(Carbon::now())) {
                return true;
            }
            $queue->put($queue_item->get('queue_position'), collect([
                'skill_id' => $queue_item->get('skill_id'),
                'queue_position' => $queue_item->get('queue_position'),
                'finished_level' => $queue_item->get('finished_level'),
                'level_start_sp' => $queue_item->get('level_start_sp'),
                'level_end_sp' => $queue_item->get('level_end_sp'),
                'training_start_sp' => $queue_item->get('training_start_sp'),
                'start_date' => $queue_item->has('start_date') ? Carbon::parse($queue_item->get('start_date')) : null,
                'finish_date' => $queue_item->has('finish_date') ? Carbon::parse($queue_item->get('finish_date')) : null
            ]));
        });
        $member->jobs()->attach($dispatchedJobs->toArray());
        $member->queue()->detach();
        $member->queue()->attach($queue->toArray());

        return $request;
    }

    public function getSearch ($category, $string, $strict=false)
    {
        return $this->httpCont->getSearch($string, $category, $strict);
    }

    public function postUniverseNames(Collection $ids)
    {
        return $this->httpCont->postUniverseNames($ids->toArray());
    }

    /**
    * Queries Database to see if the structure exists, if it doesn't a GET HTTP Request is made to ESI /universe/structure/{structure_id} to get the structure data
    *
    * @param LevelV\Models\Member $member Member to use when performing query
    * @param int $id ID of the station/outpost to retrieve data for.
    *
    * @return mixed
    **/
    public function getStructure(Member $member, int $id)
    {
        $structure = Structure::firstOrNew(['id' => $id]);
        if (!$structure->exists || $structure->cached_until < Carbon::now()) {
            $request = $this->httpCont->getUniverseStructuresStructureId($id, $member->access_token);
            if (!$request->get('status')) {
                if (!$structure->exists) {

                    $structure->fill(['name' => "Unknown Structure " . $structure->id]);

                }
                $structure->fill(['cached_until' => now()->addDay()]);
                $structure->save();
                return collect([
                    'status' => false,
                    'payload' => $structure
                ]);
            }
            $response = $request->get('payload')->get('response');
            $structure->fill([
                'name' => $response->name,
                'solar_system_id' => $response->solar_system_id,
                'pos_x' => $response->position->x,
                'pos_y' => $response->position->y,
                'pos_z' => $response->position->z,
                'cached_until' => isset($responseHeaders['Expires']) ? Carbon::parse($responseHeaders['Expires'])->toDateTimeString() : Carbon::now()->addHour()->toDateTimeString()
            ]);
            $structure->save();
        }
        return collect([
            'status' => true,
            'payload' => $structure
        ]);
    }

    /**
    * Queries Database to see if the station exists, if it doesn't a GET HTTP Request is made to ESI /universe/stations/{station_id} to get the station data
    *
    * @param int $id ID of the station/outpost to retrieve data for.
    *
    * @return mixed
    **/
    public function getStation(int $id)
    {
        $station = Station::firstOrNew(['id' => $id]);
        if (!$station->exists || $station->cached_until < Carbon::now()) {
            $request = $this->httpCont->getUniverseStationsStationId($id);
            if (!$request->get('status')) {
                return $request;
            }
            $response = $request->get('payload')->get('response');
            $station->fill([
                'name' => $response->name,
                'system_id' => $response->system_id,
                'owner_id' => $response->owner,
                'type_id' => $response->type_id,
                'pos_x' => $response->position->x,
                'pos_y' => $response->position->y,
                'pos_z' => $response->position->z,
                'cached_until' => isset($responseHeaders['Expires']) ? Carbon::parse($responseHeaders['Expires'])->toDateTimeString() : Carbon::now()->addHour()->toDateTimeString()
            ])->save();
        }
        return collect([
            'status' => true,
            'payload' => $station
        ]);
    }

    /**
    * Queries Database to see if the system exists, if it doesn't a GET HTTP Request is made to ESI /universe/systems/{system_id} to get the system data
    *
    * @param int $id ID of the system to retrieve data for.
    *
    * @return mixed
    **/
    public function getSystem(int $id)
    {
        $system = System::firstOrNew(['id' => $id]);
        if (!$system->exists || $system->cached_until < Carbon::now()) {
            $request = $this->httpCont->getUniverseSystemsSystemId($id);
            if (!$request->get('status')) {
                return $request;
            }
            $response = $request->get('payload')->get('response');
            $system->fill([
                'name' => $response->name,
                'star_id' => $response->star_id,
                'pos_x' => $response->position->x,
                'pos_y' => $response->position->y,
                'pos_z' => $response->position->z,
                'security_status' => $response->security_status,
                'constellation_id' => $response->constellation_id,
                'cached_until' => isset($responseHeaders['Expires']) ? Carbon::parse($responseHeaders['Expires'])->toDateTimeString() : Carbon::now()->addHour()->toDateTimeString()
            ]);
            $system->save();
        }
        return collect([
            'status' => true,
            'payload' => $system
        ]);
    }

    public function getType (int $id, $ane=false)
    {
        $type = Type::firstOrNew(['id'=>$id]);
        if (!$type->exists || ($ane && $type->attributes()->count() == 0)) {
            $request = $this->httpCont->getUniverseTypesTypeId($id);
            if (!$request->get('status')) {
                return $request;
            }
            $response = collect($request->get('payload')->get('response'))->recursive();

            $type->fill([
                'name' => $response->get('name'),
                'description' => $response->get('description'),
                'published' => $response->get('published'),
                'group_id' => $response->get('group_id'),
                'volume' => $response->get('volume')
            ]);
            $type->load('group');
            $type->fill(['category_id' => $type->group->category_id]);
            $type->save();

            if ($response->has('dogma_attributes')) {
                $attributes = $response->get('dogma_attributes');
                $dbAttributes = $type->attributes()->whereIn('attribute_id', $attributes->pluck('attribute_id')->toArray())->get()->keyBy('attribute_id');
                $missingAttributes = collect();
                $attributes->each(function ($attribute) use ($dbAttributes, $type, $missingAttributes) {
                    // Ignore the mass attribute for right now.
                    if ($attribute->get('attribute_id') == 4) {
                        return true;
                    }
                    if (!$dbAttributes->has($attribute->get('attribute_id'))) {
                        $missingAttributes->push($attribute->toArray());
                    }
                });
                $type->attributes()->createMany($missingAttributes->toArray());
            }
        }
        return collect([
            'status' => true,
            'payload' => $type
        ]);
    }

    public function getGroup($id)
    {
        return $this->httpCont->getUniverseGroupsGroupId($id);
    }

    // Methods related to importing the SDE from zzeve
    public function getChrAncestries()
    {
        return $this->httpCont->getChrAncestries();
    }

    public function getChrBloodlines()
    {
        return $this->httpCont->getChrBloodlines();
    }

    public function getChrFactions()
    {
        return $this->httpCont->getChrFactions();
    }

    public function getChrRaces()
    {
        return $this->httpCont->getChrRaces();
    }

    public function getInvCategories()
    {
        return $this->httpCont->getInvCategories();
    }

    public function getInvGroups()
    {
        return $this->httpCont->getInvGroups();
    }

    public function getMapConstellations()
    {
        return $this->httpCont->getMapConstellations();
    }

    public function getMapRegions()
    {
        return $this->httpCont->getMapRegions();
    }

    public function shouldDispatchJob($class, $args) {
        $check = JobStatus::where('type', $class);
        foreach ($args as $key=>$value) {
            $check=$check->where('input->'.$key, $value);
        }
        $check = $check->whereIn('status',[JobStatus::STATUS_EXECUTING, JobStatus::STATUS_QUEUED]);
        $check = $check->first();

        return is_null($check);
    }
}
