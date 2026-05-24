<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'pending_email', 'password', 'must_change_password',
        'role', 'is_active',
        'phone', 'address', 'city', 'postal_code', 'country',
        'invitation_token', 'invitation_sent_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_active'             => 'boolean',
            'must_change_password'  => 'boolean',
            'invitation_sent_at'    => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool   { return $this->role === 'super_admin'; }
    public function isAdmin(): bool        { return in_array($this->role, ['super_admin', 'admin']); }
    public function isClient(): bool       { return $this->role === 'client'; }
    public function isFournisseur(): bool  { return $this->role === 'fournisseur'; }

    public function orders()        { return $this->hasMany(Order::class); }
    public function createdOrders() { return $this->hasMany(Order::class, 'created_by'); }
    public function cartItems()     { return $this->hasMany(Cart::class); }
    public function credits()       { return $this->hasMany(Credit::class); }

    public function availableCredit(): float
    {
        return (float) Credit::availableFor($this->id)->sum(\Illuminate\Support\Facades\DB::raw('amount - used_amount'));
    }

    public function anonymize(): void
    {
        $this->forceFill([
            'name'                 => 'Utilisateur supprimé',
            'email'                => 'deleted-' . $this->id . '-' . time() . '@anonymized.invalid',
            'pending_email'        => null,
            'phone'                => null,
            'address'              => null,
            'city'                 => null,
            'postal_code'          => null,
            'must_change_password' => false,
            'invitation_token'     => null,
            'invitation_sent_at'   => null,
            'password'             => Str::random(40),
        ])->save();
    }
}
