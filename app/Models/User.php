<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;


/**
 * Class User
 * 
 * @property int $id
 * @property string $full_name
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property Carbon $date_of_birth
 * @property string|null $curp
 * @property int $role_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Role $role
 * @property Collection|Student[] $students
 * @property Collection|Coach[] $coaches
 * @property Collection|CourseEnrollment[] $course_enrollments
 * @property Collection|CertificateIssued[] $certificate_issueds
 * @property Collection|Message[] $messages
 * @property Collection|PostComment[] $post_comments
 * @property Collection|Role[] $roles
 * @property Collection|Post[] $posts
 *
 * @package App\Models
 */
class User extends Authenticatable
{
		Use HasApiTokens, Notifiable;

	protected $table = 'users';

	protected $casts = [
		'date_of_birth' => 'datetime',
		'role_id' => 'int'
	];

	protected $hidden = [
		'remember_token',
		'password_hash'
	];

	protected $fillable = [
		'full_name',
		'username',
		'email',
		'password_hash',
		'date_of_birth',
		'curp',
		'role_id',
		'remember_token'
	];

	public function role()
	{
		return $this->belongsTo(Role::class);
	}

	public function students()
	{
		return $this->hasMany(Student::class);
	}

	public function coaches()
	{
		return $this->hasMany(Coach::class);
	}

	public function course_enrollments()
	{
		return $this->hasMany(CourseEnrollment::class);
	}

	public function certificate_issueds()
	{
		return $this->hasMany(CertificateIssued::class);
	}

	public function messages()
	{
		return $this->hasMany(Message::class);
	}

	public function post_comments()
	{
		return $this->hasMany(PostComment::class);
	}

	public function roles()
	{
		return $this->belongsToMany(Role::class, 'user_roles')
					->withPivot('id');
	}

	public function posts()
	{
		return $this->hasMany(Post::class, 'moderator_id');
	}
}
