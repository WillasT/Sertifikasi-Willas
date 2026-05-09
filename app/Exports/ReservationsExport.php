<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReservationsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Fetch all reservations with their related user and room data
        return Reservation::with(['user', 'room'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'User', 'Email', 'Room', 'Usage Date',
            'Duration (Hrs)', 'Purpose', 'Status', 'Requested At', 'Returned At'
        ];
    }

    public function map($reservation): array
    {
        return [
            $reservation->id,
            $reservation->user->name ?? 'N/A',
            $reservation->user->email ?? 'N/A',
            $reservation->room->name ?? 'N/A',
            $reservation->usage_date ? $reservation->usage_date->format('Y-m-d H:i') : 'N/A',
            $reservation->duration_hours,
            $reservation->purpose,
            strtoupper($reservation->status),
            $reservation->created_at ? $reservation->created_at->format('Y-m-d H:i') : 'N/A',
            $reservation->returned_at ? $reservation->returned_at->format('Y-m-d H:i') : 'N/A',
        ];
    }
}