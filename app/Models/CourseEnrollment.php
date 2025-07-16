<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseEnrollment
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int|null $course_id
 * 
 * @property User|null $user
 * @property Course|null $course
 *
 * @package App\Models
 */
class CourseEnrollment extends Model
{
	protected $table = 'course_enrollments';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'course_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'course_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function course()
	{
		return $this->belongsTo(Course::class);
	}
}
