<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\RoleSequence;
use App\Loan;
use App\WfState;

class LoanRole implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($loan_id)
    {
        if (!is_array($loan_id)) {
            $loan_id = [$loan_id];
        }
        $this->loans = Loan::whereIn('id', $loan_id)->get();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user_roles = Auth::user()->roles->pluck('id');
        foreach ($this->loans as $loan) {
            $wfStates = WfState::whereIn('id', function ($query) use ($user_roles) {
                $query->select('wf_states_id') // area_id hace referencia a wf_states.id
                      ->from('roles')
                      ->whereIn('id', $user_roles);
            })->get();
            //$roles = RoleSequence::flow($loan->modality->procedure_type->id, $loan->role_id);
            $wf_sequences = WfSequences::where($loan->wf_states_id)->get();
            // Derivar sólo trámites pertenecientes al usuario
            if ($user_roles->search($loan->role_id) === false) return false;
            // Derivar a otro rol
            if ($loan->current_state == $value) continue;
            // Derivar si está validado o devolver si no está validado
            if (($loan->validated && $roles->next->search($value) === false) || (!$loan->validated && $roles->previous->search($value) === false)) return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Derivación inválida';
    }
}