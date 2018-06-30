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
        $jobsCount = collect([
            'pending' => $member->jobs()->whereIn('status', ['queued', 'executing'])->count(),
            'finished' => $member->jobs()->whereIn('status', ['finished'])->count(),
            'failed' => $member->jobs()->whereIn('status', ['failed'])->count()
        ])->toArray();
        Log::info('Return Job Status Count for Member '. $id, [$jobsCount]);
        return response()->json($jobsCount, 200);
    }
}
