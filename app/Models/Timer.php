<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Timer
 * 
 * @property int $id
 * @property int|null $contest_classroom_id
 * @property string $phase
 * @property Carbon $start_time
 * @property Carbon $end_time
 * 
 * @property ContestClassroom|null $contest_classroom
 *
 * @package App\Models
 */
class Timer extends Model
{
	protected $table = 'timers';
	public $timestamps = false;

	protected $casts = [
		'contest_classroom_id' => 'int',
		'start_time' => 'datetime',
		'end_time' => 'datetime'
	];

	protected $fillable = [
		'contest_classroom_id',
		'phase',
		'start_time',
		'end_time'
	];

	public function contest_classroom()
	{
		return $this->belongsTo(ContestClassroom::class);
	}
}
