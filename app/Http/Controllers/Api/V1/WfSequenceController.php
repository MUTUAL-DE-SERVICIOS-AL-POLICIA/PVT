<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WfSequence;
use App\WfState;

class WfSequenceController extends Controller
{
    public static function append_data(WfSequence $wf_sequence)
    {
        $wf_sequence->current_state = $wf_sequence->current_state;
        $wf_sequence->next_state = $wf_sequence->next_state;
        return $wf_sequence;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wf_sequences = WfSequence::with(['current_state', 'next_state'])->get();
        $wf_sequences->transform(function ($wf_sequence) {
            return self::append_data($wf_sequence);
        });
        return $wf_sequences;
    }

    public function get_sequence(Request $request)
    {
        $request->validate([
            'workflow_id' => 'required|integer|exists:workflows,id'
        ]);

        $wf_sequences = WfSequence::whereWorkflowId($request->workflow_id)
            ->with(['current_state', 'next_state']) // Relación con wf_state
            ->get();

        $ordered_sequences = $this->sortWorkflowSequences($wf_sequences);
        $ordered_sequences->transform(function ($wf_sequence) {
            return self::append_data($wf_sequence);
        });
        return $ordered_sequences;
    }

    private function sortWorkflowSequences($sequences)
    {
        $sequences = collect($sequences);
        $lookup = $sequences->keyBy('wf_state_current_id');
        $start = null;
        foreach ($sequences as $seq) {
            $isStart = true;
            foreach ($sequences as $otherSeq) {
                if ($otherSeq->wf_state_next_id === $seq->wf_state_current_id) {
                    $isStart = false;
                    break;
                }
            }
            if ($isStart) {
                $start = $seq;
                break;
            }
        }
        if (!$start) {
            return collect();
        }
        $sorted = collect();
        $current = $start;
        $sequenceNumber = 1;
        while ($current) {
            $current->number_sequence = $sequenceNumber;
            $sorted->push($current);
            $sequenceNumber++;
            $current = $lookup->get($current->wf_state_next_id);
        }
        return $sorted;
    }    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(request $request)
    {
        $request->validate([
            'workflow_id' => 'required|integer|exists:workflows,id',
            'wf_state_current_id' => 'required|integer|exists:wf_states,id',
            'wf_state_next_id' => 'required|integer|exists:wf_states,id',
        ]);
        $state = false;
        if(WfSequence::whereWorkflowId($request->workflow_id)->count() == 0)
            $state = true;
        elseif(WfSequence::whereWorkflowId($request->workflow_id)->whereWfStateNextId($request->wf_state_current_id)->count() > 0 && WfState::whereId($request->wf_state_next_id)->whereModuleId(6)->count() > 0 && $request->wf_state_current_id != $request->wf_state_next_id)
            $state = true;
        if($state)
        {
            $wf_sequence = WfSequence::create([
                'workflow_id' => $request->workflow_id,
                'wf_state_current_id' => $request->wf_state_current_id,
                'wf_state_next_id' => $request->wf_state_next_id,
                'action' => "Aprobar"
            ]);
            return response()->json(['message' => 'Registro creado exitosamente', 'data' => $wf_sequence], 201);
        }
        else
            return response()->json(['message' => 'El estado actual ya tiene una secuencia asignada o no es valido'], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(WfSequence $wf_sequence)
    {
        return $this->append_data($wf_sequence);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WfSequence $wf_sequence)
    {
        $request->validate([
            'workflow_id' => 'integer|exists:workflows,id',
            'wf_state_current_id' => 'integer|exists:wf_states,id',
            'wf_state_next_id' => 'integer|exists:wf_states,id',
        ]);
        $wf_sequence->fill($request->all());
        $wf_sequence->save();
        return response()->json(self::append_data($wf_sequence), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(WfSequence $wf_sequence)
    {
        // validar que no sea un registro intermedio del flujo
        if(WfSequence::whereWorkflowId($wf_sequence->workflow_id)->whereWfStateCurrentId($wf_sequence->wf_state_next_id)->count() > 0)
            return response()->json([
                'message' => 'No se puede eliminar el flujo porque tiene secuencias intermedias'
            ], 409);
        $wf_sequence->delete();
        return response()->json([
            'message' => 'Registro eliminado correctamente'
        ], 200);
    }
}
