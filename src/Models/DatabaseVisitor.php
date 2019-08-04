<?php

namespace Ben182\AbTesting\Models;

use Illuminate\Database\Eloquent\Model;
use Ben182\AbTesting\Contracts\VisitorInterface;

class DatabaseVisitor extends Model implements VisitorInterface
{
    protected $primaryKey = 'visitor_id';
    protected $table = 'ab_visitors';
    protected $fillable = [
        'visitor_id',
        'experiment_id',
    ];

    public function experiment()
    {
        return $this->belongsTo(Experiment::class);
    }

    public function hasExperiment()
    {
        return ! is_null($this->experiment_id) && $this->experiment_id;
    }

    public function getExperiment()
    {
        return $this->experiment;
    }

    public function setExperiment(Experiment $next) {
        $this->experiment_id = $next->id;
        $this->save();
    }
}
