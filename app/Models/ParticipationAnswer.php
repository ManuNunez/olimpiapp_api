<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ParticipationAnswer
 * 
 * @property int $id
 * @property int|null $contest_id
 * @property int|null $participation_id
 * @property string $answers
 * 
 * @property Contest|null $contest
 * @property Participation|null $participation
 *
 * @package App\Models
 */
class ParticipationAnswer extends Model
{
	protected $table = 'participation_answers';
	public $timestamps = false;

	protected $casts = [
		'contest_id' => 'int',
		'participation_id' => 'int',
		'answers' => 'binary'
	];

	protected $fillable = [
		'contest_id',
		'participation_id',
		'answers'
	];

	public function contest()
	{
		return $this->belongsTo(Contest::class);
	}

	public function participation()
	{
		return $this->belongsTo(Participation::class);
	}
}
