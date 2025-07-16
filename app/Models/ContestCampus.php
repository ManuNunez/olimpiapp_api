<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContestCampus
 * 
 * @property int $id
 * @property int|null $contest_id
 * @property int|null $campus_id
 * 
 * @property Contest|null $contest
 * @property Campus|null $campus
 *
 * @package App\Models
 */
class ContestCampus extends Model
{
	protected $table = 'contest_campuses';
	public $timestamps = false;

	protected $casts = [
		'contest_id' => 'int',
		'campus_id' => 'int'
	];

	protected $fillable = [
		'contest_id',
		'campus_id'
	];

	public function contest()
	{
		return $this->belongsTo(Contest::class);
	}

	public function campus()
	{
		return $this->belongsTo(Campus::class);
	}
}
