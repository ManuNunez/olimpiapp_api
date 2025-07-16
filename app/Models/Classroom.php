<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Classroom
 * 
 * @property int $id
 * @property string $name
 * @property int|null $campus_id
 * 
 * @property Campus|null $campus
 * @property Collection|Contest[] $contests
 *
 * @package App\Models
 */
class Classroom extends Model
{
	protected $table = 'classrooms';
	public $timestamps = false;

	protected $casts = [
		'campus_id' => 'int'
	];

	protected $fillable = [
		'name',
		'campus_id'
	];

	public function campus()
	{
		return $this->belongsTo(Campus::class);
	}

	public function contests()
	{
		return $this->belongsToMany(Contest::class, 'contest_classrooms')
					->withPivot('id');
	}
}
