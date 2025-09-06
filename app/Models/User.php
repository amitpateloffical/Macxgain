<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Models\WalletTransaction;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'status',
        'last_login_at',
        'bank_name',
        'account_no',
        'ifsc_code',
        'aadhar_number',
        'pan_number',
        'address',
        'aadhar_front_image',
        'aadhar_back_image',
        'pan_card_image',
        'mobile_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }
    protected $appends = ['total_balance'];


public function getTotalBalanceAttribute()
{
    return WalletTransaction::where('user_id', $this->id)
        ->orderBy('id', 'desc')
        ->value('running_balance') ?? 0;
}

}
