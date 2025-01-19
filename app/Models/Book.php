<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'LIKE', '%' . $title . '%');
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withCount(['reviews' => fn(Builder $query) => $this->dateRangeFilter($query, $from, $to)])
            ->orderBy('reviews_count', 'DESC');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder|QueryBuilder
    {
        return $query->withAvg(['reviews' => fn(Builder $query) => $this->dateRangeFilter($query, $from, $to)], 'rating')
            ->orderBy('reviews_avg_rating', 'DESC');

    }

    public function scopeMinReview(Builder $query, int $minReviews): Builder|QueryBuilder
    {
        return $query->having('reviews_count', '>=', $minReviews);

    }

    private function dateRangeFilter(Builder $query, $from, $to)
    {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $query->where('created_at', '>=', $from);
        } elseif ($to) {
            $query->where('created_at', '<=', $to);
        } else {
            $query->where('created_at', '>=', now()->subDays(30));
        }

    }
    public function scopePopularLastMonth(Builder $query): Builder|QueryBuilder
    {
        return $query->HighestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReview(2);
    }


    public function scopePopularLast6Month(Builder $query): Builder|QueryBuilder
    {
        return $query->popular(now()->subMonth(6), now())
            ->highestRated(now()->subMonth(6), now())
            ->minReview(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query): Builder|QueryBuilder
    {
        return $query->HighestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReview(2);
    }

    public
    function scopeHighestRatedLast6Month(Builder $query): Builder|QueryBuilder
    {
        return $query->highestRated(now()->subMonth(6), now())
            ->popular(now()->subMonth(6), now())
            ->minReview(5);
    }

}
