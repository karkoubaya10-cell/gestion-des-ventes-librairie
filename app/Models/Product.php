<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'produits';

    protected $fillable = ['nom', 'code_barres', 'prix'];

    public function venteDetails(): HasMany
    {
        return $this->hasMany(VenteDetail::class, 'produit_id');
    }
}
