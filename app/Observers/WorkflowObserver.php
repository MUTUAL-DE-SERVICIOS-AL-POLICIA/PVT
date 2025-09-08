<?php

namespace App\Observers;
use App\Workflow;
use App\Helpers\Util;
use App\User;
use App\Record;
use Illuminate\Support\Facades\Auth;


class WorkflowObserver
{
    /**
     * Handle the workflow "created" event.
     *
     * @param  \App\Workflow  $workflow
     * @return void
     */
    public function created(Workflow $workflow)
    {
        Record::withoutEvents(function () use ($workflow) {
            $record = new Record();
            $record->user_id = Auth::user()->id;
            $record->record_type_id = 19;
            $record->recordable_id = $workflow->id;
            $record->recordable_type = 'Workflow';
            $record->action = 'registró el flujo: '.$workflow->name;
            $record->save();
        });
    }

    /**
     * Handle the workflow "updated" event.
     *
     * @param  \App\Workflow  $workflow
     * @return void
     */
    public function updated(Workflow $workflow)
    {
        Record::withoutEvents(function () use ($workflow) {
            $record = new Record();
            $record->user_id = Auth::user()->id;
            $record->record_type_id = 19;
            $record->recordable_id = $workflow->id;
            $record->recordable_type = 'Workflow';
            $record->action = 'Actualizo el flujo: '.$workflow->name;
            $record->save();
        });
    }

    /**
     * Handle the workflow "deleted" event.
     *
     * @param  \App\Workflow  $workflow
     * @return void
     */
    public function deleted(Workflow $workflow)
    {
        Record::withoutEvents(function () use ($workflow) {
            $record = new Record();
            $record->user_id = Auth::user()->id;
            $record->record_type_id = 19;
            $record->recordable_id = $workflow->id;
            $record->recordable_type = 'Workflow';
            $record->action = 'elimino el flujo: '.$workflow->name;
            $record->save();
        });
    }

    /**
     * Handle the workflow "restored" event.
     *
     * @param  \App\Workflow  $workflow
     * @return void
     */
    public function restored(Workflow $workflow)
    {
        //
    }

    /**
     * Handle the workflow "force deleted" event.
     *
     * @param  \App\Workflow  $workflow
     * @return void
     */
    public function forceDeleted(Workflow $workflow)
    {
        //
    }
}
