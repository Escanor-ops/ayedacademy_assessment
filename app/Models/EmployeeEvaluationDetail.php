<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEvaluationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_evaluation_id',
        'standard_id',
        'score',
    ];

    public function employeeEvaluation()
    {
        return $this->belongsTo(EmployeeEvaluation::class);
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
}
