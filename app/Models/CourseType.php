<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseType
 * 
 * @property int $id
 * @property string $name
 * 
 * @property Collection|Course[] $courses
 *
 * @package App\Models
 */
class CourseType extends Model
{
	protected $table = 'course_types';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];

	public function courses()
	{
		return $this->hasMany(Course::class);
	}
}
