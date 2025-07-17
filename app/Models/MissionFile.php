<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size'
    ];

    public function mission()
    {
        return $this->belongsTo(MissionRequest::class, 'mission_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to get human readable file size
    public function getHumanReadableSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
} 