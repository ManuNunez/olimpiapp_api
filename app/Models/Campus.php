<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Campus
 * 
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $ubication
 * 
 * @property Collection|Classroom[] $classrooms
 * @property Collection|Contest[] $contests
 * @property Collection|Training[] $trainings
 *
 * @package App\Models
 */
class Campus extends Model
{
	protected $table = 'campuses';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'address',
		'ubication'
	];

	public function classrooms()
	{
		return $this->hasMany(Classroom::class);
	}

	public function contests()
	{
		return $this->belongsToMany(Contest::class, 'contest_campuses')
					->withPivot('id');
	}

	public function trainings()
	{
		return $this->hasMany(Training::class);
	}
}
