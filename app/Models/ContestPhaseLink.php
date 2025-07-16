<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContestPhaseLink
 * 
 * @property int $id
 * @property int|null $contest_id
 * @property string $phase
 * 
 * @property Contest|null $contest
 *
 * @package App\Models
 */
class ContestPhaseLink extends Model
{
	protected $table = 'contest_phase_links';
	public $timestamps = false;

	protected $casts = [
		'contest_id' => 'int'
	];

	protected $fillable = [
		'contest_id',
		'phase'
	];

	public function contest()
	{
		return $this->belongsTo(Contest::class);
	}
}
