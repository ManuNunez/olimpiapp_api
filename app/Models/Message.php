<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Message
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $content
 * @property Carbon $timestamp
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Message extends Model
{
	protected $table = 'messages';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'timestamp' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'content',
		'timestamp'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
