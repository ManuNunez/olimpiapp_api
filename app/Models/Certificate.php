<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Certificate
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * 
 * @property Collection|CertificateIssued[] $certificate_issueds
 * @property Collection|Contest[] $contests
 * @property Collection|Course[] $courses
 *
 * @package App\Models
 */
class Certificate extends Model
{
	protected $table = 'certificates';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'description'
	];

	public function certificate_issueds()
	{
		return $this->hasMany(CertificateIssued::class);
	}

	public function contests()
	{
		return $this->belongsToMany(Contest::class, 'contest_certificates')
					->withPivot('id');
	}

	public function courses()
	{
		return $this->belongsToMany(Course::class, 'course_certificates')
					->withPivot('id');
	}
}
