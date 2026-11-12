<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public const COLUMNS = ['media_key', 'property_id', 'media_type', 'source_id', 'source_url', 'local_path', 'download_status'];

    protected $table = 'media';

    protected $primaryKey = 'media_key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getVideoMimeType(): string
    {
        if (!$this->local_path) {
            return 'video/mp4';
        }

        $path = (string) $this->local_path;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            default => 'video/mp4',
        };
    }
}

