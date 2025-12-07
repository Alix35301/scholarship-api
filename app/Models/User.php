<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'commission_rate',
        'notes',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'password' => 'hashed',
            'commission_rate' => 'decimal:2',
            'status' => 'string',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function scholarshipApplications()
    {
        return $this->hasMany(ScholarshipApplication::class, 'student_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function getTotalSalesAttribute()
    {
        return $this->sales()->where('status', 'completed')->count();
    }

    public function getTotalRevenueAttribute()
    {
        return $this->sales()->where('status', 'completed')->sum('total_amount');
    }

    public function getTotalCommissionAttribute()
    {
        return $this->total_revenue * ($this->commission_rate / 100);
    }

    public function isAdmin()
    {
        return $this->roles()->where('slug', 'admin')->exists();
    }

    public function isStudent()
    {
        return $this->roles()->where('slug', 'student')->exists();
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasPermission(string $routeName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($routeName) {
                $query->where('route_name', $routeName);
            })
            ->exists();
    }

    public function permissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }
}
