<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBookChapter extends Model
{
    use HasFactory;

    protected $table = 'project_book_chapters';

    protected $fillable = [
        'project_book_id',
        'chapter_no',
        'chapter_title',
        'chapter_description',
    ];

    public function projectBook(): BelongsTo
    {
        return $this->belongsTo(ProjectBook::class);
    }
}
