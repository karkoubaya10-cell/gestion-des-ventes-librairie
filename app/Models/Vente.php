<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vente extends Model
{
    protected $table = 'ventes';

    protected $fillable = ['numero_vente', 'date_vente', 'total', 'user_id'];

    protected function casts(): array
    {
        return ['date_vente' => 'datetime', 'total' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(VenteDetail::class, 'vente_id');
    }
}
