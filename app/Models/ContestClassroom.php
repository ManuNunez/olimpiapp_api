<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ContestClassroom
 * 
 * @property int $id
 * @property int|null $contest_id
 * @property int|null $classroom_id
 * 
 * @property Contest|null $contest
 * @property Classroom|null $classroom
 * @property Collection|Timer[] $timers
 * @property Collection|Participation[] $participations
 *
 * @package App\Models
 */
class ContestClassroom extends Model
{
	protected $table = 'contest_classrooms';
	public $timestamps = false;

	protected $casts = [
		'contest_id' => 'int',
		'classroom_id' => 'int'
	];

	protected $fillable = [
		'contest_id',
		'classroom_id'
	];

	public function contest()
	{
		return $this->belongsTo(Contest::class);
	}

	public function classroom()
	{
		return $this->belongsTo(Classroom::class);
	}

	public function timers()
	{
		return $this->hasMany(Timer::class);
	}

	public function participations()
	{
		return $this->hasMany(Participation::class, 'classroom_id');
	}
}
