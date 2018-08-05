<?php

namespace LevelV\Http\Controllers;

use Log;
use LevelV\Models\Member;

class ApiController extends Controller
{
    public function status($id)
    {
        $member = Member::find($id);
        if (is_null($member)) {
            return response()->json([], 404);
        }
        $member->load('alts.jobs');
        $jobsCount = collect([
            'pending' => $member->alts->pluck('jobs')->flatten()->whereIn('status', ['queued', 'executing'])->count(),
        ])->toArray();
        Log::info('Return Job Status Count for Member '. $id, [$jobsCount]);
        return response()->json($jobsCount, 200);
    }
}
