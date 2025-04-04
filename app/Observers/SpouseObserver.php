<?php

namespace App\Observers;

use App\Helpers\Util;
use App\Spouse;

class SpouseObserver
{
    /**
     * Handle the spouse "created" event.
     *
     * @param  \App\Spouse  $spouse
     * @return void
     */
    public function created(Spouse $spouse)
    {
        Util::save_record($spouse->affiliate, 'datos-personales', 'registró (CÓNYUGE)');
    }

    /**
     * Handle the spouse "updated" event.
     *
     * @param  \App\Spouse  $spouse
     * @return void
     */
    public function updating(Spouse $spouse)
    {
        Util::save_record($spouse->affiliate, 'datos-personales', Util::concat_action($spouse).' (CÓNYUGE)');
    }

    /**
     * Handle the spouse "deleted" event.
     *
     * @param  \App\Spouse  $spouse
     * @return void
     */
    public function deleted(Spouse $spouse)
    {
        Util::save_record($spouse->affiliate, 'datos-personales', 'eliminó cónyuge: ' . $spouse->id);
    
    }
}
