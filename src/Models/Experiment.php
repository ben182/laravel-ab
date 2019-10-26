<?php

namespace Ben182\AbTesting\Models;

use Ben182\AbTesting\AbTestingFacade;
use Illuminate\Database\Eloquent\Model;
use Ben182\AbTesting\Events\ExperimentNewVisitor;

class Experiment extends Model
{
    protected $table = 'ab_experiments';

    protected $fillable = [
        'name',
        'visitors',
    ];

    protected $casts = [
        'visitors' => 'integer',
    ];

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function visit()
    {
        if (AbTestingFacade::isCrawler()) {
            return;
        }

        $this->increment('visitors');
        event(new ExperimentNewVisitor($this));
    }
}
