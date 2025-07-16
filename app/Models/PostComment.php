<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PostComment
 * 
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property string $content
 * @property Carbon $created_at
 * 
 * @property Post $post
 * @property User $user
 *
 * @package App\Models
 */
class PostComment extends Model
{
	protected $table = 'post_comments';
	public $timestamps = false;

	protected $casts = [
		'post_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'post_id',
		'user_id',
		'content'
	];

	public function post()
	{
		return $this->belongsTo(Post::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
