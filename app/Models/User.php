<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $hidden = [
        'remember_token',
        'password',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'date_of_birth',
        'curp',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'datetime',
        'status' => 'integer',
    ];

    // Relaciones existentes
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

    public function posts()
    {
        return $this->hasMany(Post::class, 'moderator_id');
    }
}