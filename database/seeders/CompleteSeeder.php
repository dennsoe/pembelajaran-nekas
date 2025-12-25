<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Division;
use App\Models\Education;
use App\Models\JobTitle;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class CompleteSeeder extends Seeder
{
    /**
     * Seeder lengkap untuk semua data aplikasi.
     */
    public function run(): void
    {
        $this->seedEducations();
        $this->seedDivisions();
        $this->seedJobTitles();
        $this->seedShifts();
        $this->seedBarcodes();
        $this->seedUsers();
        $this->seedAttendances();

        $this->command->info('✅ Semua data berhasil di-seed!');
    }

    /**
     * Seed data pendidikan
     */
    private function seedEducations(): void
    {
        $educations = ['SD', 'SMP', 'SMA', 'SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

        foreach ($educations as $edu) {
            Education::firstOrCreate(['name' => $edu]);
        }

        $this->command->info('📚 Education seeded: ' . count($educations) . ' records');
    }

    /**
     * Seed data divisi
     */
    private function seedDivisions(): void
    {
        $divisions = [
            'Human Resources',
            'Finance & Accounting',
            'Information Technology',
            'Marketing',
            'Operations',
            'Sales',
            'Research & Development',
            'Customer Service',
        ];

        foreach ($divisions as $div) {
            Division::firstOrCreate(['name' => $div]);
        }

        $this->command->info('🏢 Division seeded: ' . count($divisions) . ' records');
    }

    /**
     * Seed data jabatan
     */
    private function seedJobTitles(): void
    {
        $jobTitles = [
            'Director',
            'Manager',
            'Supervisor',
            'Senior Staff',
            'Staff',
            'Junior Staff',
            'Intern',
            'Accounting',
            'HRD',
            'IT Support',
            'Developer',
            'Designer',
            'Marketing Executive',
            'Sales Representative',
        ];

        foreach ($jobTitles as $job) {
            JobTitle::firstOrCreate(['name' => $job]);
        }

        $this->command->info('💼 Job Titles seeded: ' . count($jobTitles) . ' records');
    }

    /**
     * Seed data shift kerja
     */
    private function seedShifts(): void
    {
        $shifts = [
            ['name' => 'Shift Pagi', 'start_time' => '08:00:00', 'end_time' => '17:00:00'],
            ['name' => 'Shift Siang', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Shift Malam', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }

        $this->command->info('⏰ Shifts seeded: ' . count($shifts) . ' records');
    }

    /**
     * Seed data barcode/lokasi absensi
     */
    private function seedBarcodes(): void
    {
        $barcodes = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'value' => 'BARCODE-KANTOR-PUSAT-001',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'radius' => 100,
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'value' => 'BARCODE-CABANG-BDG-002',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'radius' => 75,
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'value' => 'BARCODE-CABANG-SBY-003',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'radius' => 75,
            ],
        ];

        foreach ($barcodes as $barcode) {
            Barcode::firstOrCreate(['name' => $barcode['name']], $barcode);
        }

        $this->command->info('📍 Barcodes seeded: ' . count($barcodes) . ' records');
    }

    /**
     * Seed data user
     */
    private function seedUsers(): void
    {
        // Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'nip' => '0000000000000001',
                'name' => 'Super Admin',
                'password' => Hash::make('superadmin'),
                'raw_password' => 'superadmin',
                'group' => 'superadmin',
                'phone' => '081234567890',
                'gender' => 'male',
                'address' => 'Jakarta',
                'city' => 'Jakarta',
            ]
        );

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nip' => '0000000000000002',
                'name' => 'Admin',
                'password' => Hash::make('admin'),
                'raw_password' => 'admin',
                'group' => 'admin',
                'phone' => '081234567891',
                'gender' => 'female',
                'address' => 'Jakarta',
                'city' => 'Jakarta',
            ]
        );

        // Sample Users/Karyawan
        $users = [
            ['nip' => '2024001', 'name' => 'Budi Santoso', 'email' => 'budi@example.com', 'gender' => 'male'],
            ['nip' => '2024002', 'name' => 'Siti Rahayu', 'email' => 'siti@example.com', 'gender' => 'female'],
            ['nip' => '2024003', 'name' => 'Ahmad Hidayat', 'email' => 'ahmad@example.com', 'gender' => 'male'],
            ['nip' => '2024004', 'name' => 'Dewi Lestari', 'email' => 'dewi@example.com', 'gender' => 'female'],
            ['nip' => '2024005', 'name' => 'Eko Prasetyo', 'email' => 'eko@example.com', 'gender' => 'male'],
            ['nip' => '2024006', 'name' => 'Fitri Handayani', 'email' => 'fitri@example.com', 'gender' => 'female'],
            ['nip' => '2024007', 'name' => 'Gunawan Wibowo', 'email' => 'gunawan@example.com', 'gender' => 'male'],
            ['nip' => '2024008', 'name' => 'Hana Pertiwi', 'email' => 'hana@example.com', 'gender' => 'female'],
            ['nip' => '2024009', 'name' => 'Irfan Maulana', 'email' => 'irfan@example.com', 'gender' => 'male'],
            ['nip' => '2024010', 'name' => 'Julia Kusuma', 'email' => 'julia@example.com', 'gender' => 'female'],
        ];

        $educations = Education::all();
        $divisions = Division::all();
        $jobTitles = JobTitle::all();

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password'),
                    'raw_password' => 'password',
                    'group' => 'user',
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'birth_date' => Carbon::now()->subYears(rand(22, 45))->subDays(rand(1, 365)),
                    'birth_place' => fake()->city(),
                    'address' => fake()->address(),
                    'city' => fake()->city(),
                    'education_id' => $educations->random()->id,
                    'division_id' => $divisions->random()->id,
                    'job_title_id' => $jobTitles->random()->id,
                ])
            );
        }

        $this->command->info('👥 Users seeded: ' . (count($users) + 2) . ' records');
    }

    /**
     * Seed data absensi
     */
    private function seedAttendances(): void
    {
        $users = User::where('group', 'user')->get();
        $barcodes = Barcode::all();
        $shifts = Shift::all();

        if ($users->isEmpty() || $barcodes->isEmpty() || $shifts->isEmpty()) {
            $this->command->warn('⚠️ Skipping attendance seeding - missing required data');
            return;
        }

        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        $count = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) continue;

            foreach ($users as $user) {
                // Skip jika sudah ada attendance untuk tanggal ini
                if (Attendance::where('user_id', $user->id)->where('date', $date->toDateString())->exists()) {
                    continue;
                }

                $status = fake()->randomElement(['present', 'present', 'present', 'present', 'late', 'excused', 'sick', 'absent']);
                $barcode = $barcodes->random();
                $shift = $shifts->random();

                $attendanceData = [
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'status' => $status,
                ];

                if (in_array($status, ['present', 'late'])) {
                    $timeIn = Carbon::parse($shift->start_time);
                    if ($status === 'late') {
                        $timeIn->addMinutes(rand(5, 30));
                    } else {
                        $timeIn->subMinutes(rand(0, 15));
                    }

                    $attendanceData = array_merge($attendanceData, [
                        'barcode_id' => $barcode->id,
                        'shift_id' => $shift->id,
                        'time_in' => $timeIn->toTimeString(),
                        'time_out' => Carbon::parse($shift->end_time)->addMinutes(rand(0, 30))->toTimeString(),
                        'latitude' => $barcode->latitude,
                        'longitude' => $barcode->longitude,
                    ]);
                } elseif (in_array($status, ['excused', 'sick'])) {
                    $attendanceData['note'] = $status === 'sick' ? 'Sakit ' . fake()->randomElement(['demam', 'flu', 'batuk']) : fake()->sentence();
                }

                Attendance::create($attendanceData);
                $count++;
            }
        }

        $this->command->info('📋 Attendances seeded: ' . $count . ' records');
    }
}
