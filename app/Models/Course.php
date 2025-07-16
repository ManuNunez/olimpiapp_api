<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Course
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $course_type_id
 * @property int|null $coach_id
 * 
 * @property CourseType|null $course_type
 * @property Coach|null $coach
 * @property Collection|CourseEnrollment[] $course_enrollments
 * @property Collection|Training[] $trainings
 * @property Collection|Certificate[] $certificates
 *
 * @package App\Models
 */
class Course extends Model
{
	protected $table = 'courses';
	public $timestamps = false;

	protected $casts = [
		'course_type_id' => 'int',
		'coach_id' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'course_type_id',
		'coach_id'
	];

	public function course_type()
	{
		return $this->belongsTo(CourseType::class);
	}

	public function coach()
	{
		return $this->belongsTo(Coach::class);
	}

	public function course_enrollments()
	{
		return $this->hasMany(CourseEnrollment::class);
	}

	public function trainings()
	{
		return $this->hasMany(Training::class);
	}

	public function certificates()
	{
		return $this->belongsToMany(Certificate::class, 'course_certificates')
					->withPivot('id');
	}
}
