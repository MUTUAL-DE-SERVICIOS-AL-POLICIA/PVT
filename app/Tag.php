<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name','shortened','slug'];

    public function loans()
    {
        return $this->morphedByMany(Loan::class, 'taggable')->withPivot('user_id', 'date')->withTimestamps();
    }
}
