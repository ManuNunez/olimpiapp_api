<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContestCertificate
 * 
 * @property int $id
 * @property int|null $certificate_id
 * @property int|null $contest_id
 * 
 * @property Certificate|null $certificate
 * @property Contest|null $contest
 *
 * @package App\Models
 */
class ContestCertificate extends Model
{
	protected $table = 'contest_certificates';
	public $timestamps = false;

	protected $casts = [
		'certificate_id' => 'int',
		'contest_id' => 'int'
	];

	protected $fillable = [
		'certificate_id',
		'contest_id'
	];

	public function certificate()
	{
		return $this->belongsTo(Certificate::class);
	}

	public function contest()
	{
		return $this->belongsTo(Contest::class);
	}
}
