<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'room_id', 'submission_date', 'usage_date', 'duration_hours', 'status', 'actual_return_time', 'purpose'])]
class Reservation extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class)
                    ->using(EquipmentReservation::class)
                    ->withTimestamps();
    }
}
