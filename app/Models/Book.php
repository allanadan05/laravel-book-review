<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'title', 'author'];

    //Relationship
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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
        static::updated(fn(Book $book) => cache()->forget('book' .$book->book->id));
        static::deleted(fn(Book $book) => cache()->forget('book' .$book->book->id));
    }

    //local query scope, starts with scope_
    //To test in tinker, \App\Models\Book::title('delectus')->get();
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' .$title .'%');
    }

    //local query scope, starts with scope_
    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withCount([
                'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
        ]);
    }

    public function scopeWithReviewsAvgRating(Builder $query, $from = null, $to = null):Builder|QueryBuilder
    {
        return $query->withAvg([
                        'reviews' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
                     ], 'rating');
    }

    //local query scope, starts with scope_
    //This returns popular books based on reviews created at the date specified
    //To test in tinker, \App\Models\Book::popularBetween("2024-01-01", "2024-02-01")->get();
    // \App\Models\Book::popular()->get();
    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withReviewsCount()
                     ->orderBy('reviews_count', 'desc');
    }

    //local query scope, starts with scope_
    //To test in tinker, \App\Models\Book::highestRated()->get();
    //\App\Models\Book::highestRated("2024-01-01", "2024-02-01")->get();
    public function scopeHighestRated(Builder $query, $from = null, $to = null):Builder|QueryBuilder
    {
        return $query->withReviewsAvgRating()
                     ->orderBy('reviews_avg_rating', 'desc');
    }


    //local query scope
    //To test in tinker, \App\Models\Book::highestRated("2024-01-01", "2024-02-01")->popular("2024-01-01", "2024-02-01")->minReviews(2)->get();
    public function scopeMinReviews(Builder $query, int $minReviews): Builder|QueryBuilder
    {
        return $query->having('reviews_count', '>=', $minReviews);
    }


    public function scopePopularLastMonth(Builder $query): Builder | QueryBuilder
    {
        return $query->popular (now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLast6Months(Builder $query): Builder| QueryBuilder
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReviews (5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder| QueryBuilder
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLast6Months(Builder $query): Builder| QueryBuilder
    {
        return $query->highestRated(now()->subMonth(6), now())
            ->popular(now()->subMonth(6), now())
            ->minReviews(5);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null)
    {
        if($from  && !$to){
            $query->where('created_at', '>=', $from);
        }else if(!$from  && $to){
            $query->where('created_at', '<=', $to);
        }else if($from  && $to){
            $query->whereBetween('created_at', [$from, $to]);
        }                        
    }
}
