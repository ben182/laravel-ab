<?php

namespace Ben182\AbTesting\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $table = 'ab_goals';

    protected $fillable = [
        'name',
        'hit',
    ];

    protected $casts = [
        'hit' => 'integer',
    ];

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function incrementHit()
    {
        $this->increment('hit');
    }
}
