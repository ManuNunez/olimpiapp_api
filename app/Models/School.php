<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class School
 * 
 * @property int $id
 * @property string $name
 * @property string $cct
 * @property string $turno
 * @property string $type
 * @property float|null $latitude
 * @property float|null $longitude
 * 
 * @property Collection|Student[] $students
 *
 * @package App\Models
 */
class School extends Model
{
	protected $table = 'schools';
	public $timestamps = false;

	protected $casts = [
		'latitude' => 'float',
		'longitude' => 'float'
	];

	protected $fillable = [
		'name',
		'cct',
		'turno',
		'type',
		'latitude',
		'longitude'
	];

	public function students()
	{
		return $this->hasMany(Student::class);
	}
}
