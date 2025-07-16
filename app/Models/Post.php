<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property USER-DEFINED $status
 * @property int|null $moderator_id
 * @property string|null $moderation_notes
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User|null $user
 * @property Collection|PostComment[] $post_comments
 *
 * @package App\Models
 */
class Post extends Model
{
	protected $table = 'posts';

	protected $casts = [
		'user_id' => 'int',
		'status' => 'USER-DEFINED',
		'moderator_id' => 'int',
		'published_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'title',
		'content',
		'status',
		'moderator_id',
		'moderation_notes',
		'published_at'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'moderator_id');
	}

	public function post_comments()
	{
		return $this->hasMany(PostComment::class);
	}
}
