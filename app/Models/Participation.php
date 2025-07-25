<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Participation
 * 
 * @property int $id
 * @property int|null $student_id
 * @property int|null $contest_id
 * @property int|null $classroom_id
 * @property float|null $score
 * 
 * @property Student|null $student
 * @property Contest|null $contest
 * @property ContestClassroom|null $contest_classroom
 * @property Collection|ParticipationAnswer[] $participation_answers
 *
 * @package App\Models
 */
class Participation extends Model
{
	protected $table = 'participations';
	public $timestamps = false;

	protected $casts = [
		'student_id' => 'int',
		'contest_id' => 'int',
		'classroom_id' => 'int',
		'score' => 'float'
	];

	protected $fillable = [
		'student_id',
		'contest_id',
		'classroom_id',
		'score'
	];

	public function student()
	{
		return $this->belongsTo(Student::class);
	}

	public function contest()
	{
		return $this->belongsTo(Contest::class);
	}

	public function contest_classroom()
	{
		return $this->belongsTo(ContestClassroom::class, 'classroom_id');
	}

	public function participation_answers()
	{
		return $this->hasMany(ParticipationAnswer::class);
	}
}
