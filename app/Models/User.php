<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;
    public $timestamps = false;
    protected $table = "users";
    protected $primaryKey = "id";
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'no_telp',
        'status',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function generateUserId()
    {
        $year = now()->format('y');
        $month = now()->format('m');

        $lastIndex = self::whereRaw('SUBSTRING(id, 1, 2) = ? and SUBSTRING(id, 4, 2) = ?', [$year, $month])
            ->max('id');

        list($lastYear, $lastMonth, $lastIndex) = $lastIndex
            ? array_map('intval', explode('.', $lastIndex))
            : [$year, $month, 0];

        if ($lastYear == $year && $lastMonth == $month) {
            $index = $lastIndex + 1;
        } else {
            $index = 1;
        }

        return $year . '.' . $month . '.' . $index;
    }

    public function isSubscribed()
    {
        return $this->status === 1;
    }
}
