<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CertificateIssued
 * 
 * @property int $id
 * @property int|null $certificate_id
 * @property int|null $user_id
 * @property Carbon $date_issued
 * 
 * @property Certificate|null $certificate
 * @property User|null $user
 *
 * @package App\Models
 */
class CertificateIssued extends Model
{
	protected $table = 'certificate_issued';
	public $timestamps = false;

	protected $casts = [
		'certificate_id' => 'int',
		'user_id' => 'int',
		'date_issued' => 'datetime'
	];

	protected $fillable = [
		'certificate_id',
		'user_id',
		'date_issued'
	];

	public function certificate()
	{
		return $this->belongsTo(Certificate::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
