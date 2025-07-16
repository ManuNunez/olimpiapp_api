<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contest
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property Carbon $date
 * @property int $duration
 * @property int $number_of_questions
 * 
 * @property Collection|ContestPhaseLink[] $contest_phase_links
 * @property Collection|Campus[] $campuses
 * @property Collection|Classroom[] $classrooms
 * @property Collection|Participation[] $participations
 * @property Collection|ParticipationAnswer[] $participation_answers
 * @property Collection|Certificate[] $certificates
 *
 * @package App\Models
 */
class Contest extends Model
{
	protected $table = 'contests';
	public $timestamps = false;

	protected $casts = [
		'date' => 'datetime',
		'duration' => 'int',
		'number_of_questions' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'date',
		'duration',
		'number_of_questions'
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
