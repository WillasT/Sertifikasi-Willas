<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Equipment;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'identity_number' => 'ADMIN',
            'phone_number' => '080000000000',
            'account_type' => 'admin',
        ]);

        User::create([
            'name' => 'Dr. Evan Gunawan',
            'email' => 'evan@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '2019012210012',
            'phone_number' => '083456432323',
            'account_type' => 'lecturer',
        ]);

        User::create([
            'name' => 'Prof. Jason Prionggo',
            'email' => 'jason@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '2022012310042',
            'phone_number' => '082456432323',
            'account_type' => 'lecturer',
        ]);

        User::create([
            'name' => 'Richard Lienardi',
            'email' => 'richard@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '2029011210052',
            'phone_number' => '086756434141',
            'account_type' => 'lecturer',
        ]);

        User::create([
            'name' => 'Willas Tobing',
            'email' => 'willas@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '0706012210069',
            'phone_number' => '082259672632',
            'account_type' => 'student',
        ]);

        User::create([
            'name' => 'Dicks Gunawan',
            'email' => 'dicks@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '0706012210050',
            'phone_number' => '081154672632',
            'account_type' => 'student',
        ]);

        User::create([
            'name' => 'Reyhan Wuwung',
            'email' => 'reyhan@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '0706012210011',
            'phone_number' => '083359672632',
            'account_type' => 'student',
        ]);

        User::create([
            'name' => 'Mario Jose',
            'email' => 'mario@gmail.com',
            'password' => Hash::make('password'),
            'identity_number' => '0206011210069',
            'phone_number' => '081234672632',
            'account_type' => 'student',
        ]);

        $rooms = [
            ['name' => 'Plaza', 'building' => 'Main Building', 'floor' => '1', 'capacity' => 30, 'availability_status' => 'available'],
            ['name' => 'Lounge', 'building' => 'Main Building', 'floor' => '2', 'capacity' => 60, 'availability_status' => 'available'],
            ['name' => 'Laboratorium', 'building' => 'Main Building', 'floor' => '3', 'capacity' => 200, 'availability_status' => 'available'],
            ['name' => 'Foundation', 'building' => 'Tower Building', 'floor' => '7', 'capacity' => 100, 'availability_status' => 'maintenance'],
            ['name' => 'Theater', 'building' => 'Tower Building', 'floor' => '12', 'capacity' => 50, 'availability_status' => 'available'],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }

        $equipmentTypes = [
            ['prefix' => 'UCSIPJ25000', 'name' => 'Epson Projector', 'count' => 10, 'category' => 'Projector'],
            ['prefix' => 'UCSICM25000',  'name' => 'Sony Mirrorless Camera', 'count' => 5, 'category' => 'Camera'],
            ['prefix' => 'UCSIMC25000',  'name' => 'LG Wireless Microphone', 'count' => 20, 'category' => 'Microphone'],
            ['prefix' => 'UCSIAC25000',  'name' => 'Sony Camera Tripod', 'count' => 8, 'category' => 'Accessories'],
            ['prefix' => 'UCSICB25000',  'name' => 'HDMI Cable 5m', 'count' => 50, 'category' => 'Cables'],
        ];

        foreach ($equipmentTypes as $type) {

            // Loop from 1 up to the total count (e.g., 1 to 10)
            for ($i = 1; $i <= $type['count']; $i++) {

                // str_pad adds the leading zero. So 1 becomes "01", 10 stays "10"
                $code = $type['prefix'] . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                Equipment::create([
                    'code' => $code,
                    'name' => $type['name'],
                    'category' => $type['category'],
                    'status' => 'available',
                ]);
            }
        }
    }
}
