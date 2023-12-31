<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'nom_client',
        'prenom_client',
        'telephone_client',
        'email_client',
        'photo_client',
        'password_client',
        'otp_client',
        'status_client',
    ];

    protected $table = 'clients';
    protected $primaryKey = 'id_client';
}
