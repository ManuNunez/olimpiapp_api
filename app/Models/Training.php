<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Training
 * 
 * @property int $id
 * @property int|null $course_id
 * @property Carbon $date
 * @property int|null $campus_id
 * @property int|null $coach_id
 * 
 * @property Course|null $course
 * @property Campus|null $campus
 * @property Coach|null $coach
 * @property Collection|TrainingAssistance[] $training_assistances
 *
 * @package App\Models
 */
class Training extends Model
{
	protected $table = 'trainings';
	public $timestamps = false;

	protected $casts = [
		'course_id' => 'int',
		'date' => 'datetime',
		'campus_id' => 'int',
		'coach_id' => 'int'
	];

	protected $fillable = [
		'course_id',
		'date',
		'campus_id',
		'coach_id'
	];

	public function course()
	{
		return $this->belongsTo(Course::class);
	}

	public function campus()
	{
		return $this->belongsTo(Campus::class);
	}

	public function coach()
	{
		return $this->belongsTo(Coach::class);
	}

	public function training_assistances()
	{
		return $this->hasMany(TrainingAssistance::class);
	}
}
