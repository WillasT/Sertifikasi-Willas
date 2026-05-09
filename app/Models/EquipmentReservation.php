<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['reservation_id', 'equipment_id'])]
class EquipmentReservation extends Pivot
{
    // Explicitly tell Laravel which table this weak entity belongs to
    protected $table = 'equipment_reservation';

    // You can also define relationships back to the main entities here if needed!
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}