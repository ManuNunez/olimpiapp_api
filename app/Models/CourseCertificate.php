<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CourseCertificate
 * 
 * @property int $id
 * @property int|null $certificate_id
 * @property int|null $course_id
 * 
 * @property Certificate|null $certificate
 * @property Course|null $course
 *
 * @package App\Models
 */
class CourseCertificate extends Model
{
	protected $table = 'course_certificates';
	public $timestamps = false;

	protected $casts = [
		'certificate_id' => 'int',
		'course_id' => 'int'
	];

	protected $fillable = [
		'certificate_id',
		'course_id'
	];

	public function certificate()
	{
		return $this->belongsTo(Certificate::class);
	}

	public function course()
	{
		return $this->belongsTo(Course::class);
	}
}
