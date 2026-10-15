<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenteDetail extends Model
{
    protected $table = 'vente_details';

    protected $fillable = ['vente_id', 'produit_id', 'quantite', 'prix', 'sous_total'];

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class, 'vente_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produit_id');
    }
}
