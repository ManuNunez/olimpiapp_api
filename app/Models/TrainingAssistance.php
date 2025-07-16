<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TrainingAssistance
 * 
 * @property int $id
 * @property int|null $training_id
 * @property int|null $student_id
 * @property string|null $notes
 * 
 * @property Training|null $training
 * @property Student|null $student
 *
 * @package App\Models
 */
class TrainingAssistance extends Model
{
	protected $table = 'training_assistance';
	public $timestamps = false;

	protected $casts = [
		'training_id' => 'int',
		'student_id' => 'int'
	];

	protected $fillable = [
		'training_id',
		'student_id',
		'notes'
	];

	public function training()
	{
		return $this->belongsTo(Training::class);
	}

	public function student()
	{
		return $this->belongsTo(Student::class);
	}
}
