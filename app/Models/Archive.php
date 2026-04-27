<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $fillable = [
        'original_record_id', 'record_type', 'source_subsystem',
        'archive_reason', 'data', 'scan_file_path',
        'archived_by', 'restored', 'restored_at',
    ];

    protected function casts(): array
    {
        return [
            'data'        => 'array',
            'restored'    => 'boolean',
            'restored_at' => 'datetime',
        ];
    }

    public function archivedByUser() { return $this->belongsTo(User::class, 'archived_by'); }

    public function scopeOfType($q, string $type)   { return $q->where('record_type', $type); }
    public function scopeFromSS($q, string $ss)     { return $q->where('source_subsystem', $ss); }
    public function scopeNotRestored($q)             { return $q->where('restored', false); }
}
