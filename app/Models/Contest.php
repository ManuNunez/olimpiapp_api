<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $table = 'contests';
    public $timestamps = false;

    protected $casts = [
        'date' => 'datetime',
        'duration' => 'int',
        'number_of_questions' => 'int',
        'status' => 'int', // nuevo campo
    ];

    protected $fillable = [
        'name',
        'description',
        'date',
        'duration',
        'number_of_questions',
        'status', // agregado al fillable
    ];

    public function contest_phase_links()
    {
        return $this->hasMany(ContestPhaseLink::class);
    }

    public function campuses()
    {
        return $this->belongsToMany(Campus::class, 'contest_campuses')
                    ->withPivot('id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'contest_classrooms')
                    ->withPivot('id');
    }

    public function participations()
    {
        return $this->hasMany(Participation::class);
    }

    public function participation_answers()
    {
        return $this->hasMany(ParticipationAnswer::class);
    }

    public function certificates()
    {
        return $this->belongsToMany(Certificate::class, 'contest_certificates')
                    ->withPivot('id');
    }
}
