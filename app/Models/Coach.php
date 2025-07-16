<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coach
 * 
 * @property int $id
 * @property int|null $user_id
 * 
 * @property User|null $user
 * @property Collection|Course[] $courses
 * @property Collection|Training[] $trainings
 *
 * @package App\Models
 */
class Coach extends Model
{
	protected $table = 'coaches';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'user_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function courses()
	{
		return $this->hasMany(Course::class);
	}

	public function trainings()
	{
		return $this->hasMany(Training::class);
	}
}
