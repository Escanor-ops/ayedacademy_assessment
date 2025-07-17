<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MissionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'status'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', 0);
        });
    }

    /**
     * Get the department that owns the mission type.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
