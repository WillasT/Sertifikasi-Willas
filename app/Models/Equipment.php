<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['code', 'name', 'category', 'status'])]
class Equipment extends Model
{
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class)
                    ->using(EquipmentReservation::class) // Points to your new weak entity
                    ->withTimestamps();
    }
}
