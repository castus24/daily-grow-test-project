<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $name
 * @property string $phone
 * @property string $email
 * @property string $birth_date
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public static function getAllowedFilters(): array //todo
    {
        return [
            AllowedFilter::exact('name'),
            AllowedFilter::exact('phone'),
            AllowedFilter::exact('email'),
            AllowedFilter::exact('birth_date'),
        ];
    }

    public static function getAllowedSorts(): array
    {
        return [
            'name',
            'email',
            'birth_date'
        ];
    }
}
