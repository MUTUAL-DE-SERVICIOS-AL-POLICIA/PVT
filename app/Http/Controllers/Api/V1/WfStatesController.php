<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WfState;
use App\WfSequence;

class WfStatesController extends Controller
{
    public function index(Request $request)
    {
        $query = WfState::where('module_id',6)->orderBy('name');
        if ($request->has('name')) $query = $query->whereName($request->name);
        return $query->get();
    }

    public function show(WfState $wf_state)
    {
        return $wf_state;
    }

    public function wf_states_filtered(Request $request)
    {
        $request->validate([
            'workflow_id' => 'integer|exists:workflows,id',
        ]);
        $usedStates = WfSequence::where('workflow_id', $request->workflow_id)
        ->pluck('wf_state_next_id');
        $availableStates = WfState::whereNotIn('id', $usedStates)
            ->whereModuleId(6)
            ->get();
        return $availableStates;
    }
}