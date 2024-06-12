<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['review', 'rating'];

    public function book() : BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    
    /**
    *    Eloquent models dispatch several events, allowing you to hook into the following
    *    moments in a model's lifecycle: retrieved, creating, created, updating, updated,
    *    saving, saved, deleting, deleted, trashed, forceDeleting, forceDeleted, restoring,
    *    restored, and replicating.
    */
    protected static function booted()
    {   
        //to invalidate cache
        static::updated(fn(Review $review) => cache()->forget('book:' .$review->book_id));
        static::deleted(fn(Review $review) => cache()->forget('book:' .$review->book_id));
        static::created(fn(Review $review) => cache()->forget('book:' . $review->book_id));
    }
}
