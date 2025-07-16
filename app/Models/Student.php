<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Student
 * 
 * @property int $id
 * @property int $user_id
 * @property int $school_id
 * @property string $code
 * 
 * @property User $user
 * @property School $school
 * @property Collection|Participation[] $participations
 * @property Collection|TrainingAssistance[] $training_assistances
 *
 * @package App\Models
 */
class Student extends Model
{
	protected $table = 'students';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'school_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'school_id',
		'code'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function school()
	{
		return $this->belongsTo(School::class);
	}

	public function participations()
	{
		return $this->hasMany(Participation::class);
	}

	public function training_assistances()
	{
		return $this->hasMany(TrainingAssistance::class);
	}
}
