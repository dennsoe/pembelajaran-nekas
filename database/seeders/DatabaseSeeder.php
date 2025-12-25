<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\Classroom;
use App\Models\LessonPeriod;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Superadmin
        $superadmin = User::create([
            'nip' => '199001012020121001',
            'name' => 'Superadmin',
            'email' => 'superadmin@smkn-kasomalang.sch.id',
            'password' => Hash::make('password'),
            'raw_password' => 'password',
            'group' => 'superadmin',
            'phone' => '081234567890',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Jakarta',
            'address' => 'Jl. Raya No. 1',
            'city' => 'Jakarta',
            'is_active' => true,
        ]);

        // 2. Buat Admin
        $admin = User::create([
            'nip' => '199002022020122002',
            'name' => 'Admin TU',
            'email' => 'admin@smkn-kasomalang.sch.id',
            'password' => Hash::make('password'),
            'raw_password' => 'password',
            'group' => 'admin',
            'phone' => '081234567891',
            'gender' => 'female',
            'birth_date' => '1990-02-02',
            'birth_place' => 'Bandung',
            'address' => 'Jl. Raya No. 2',
            'city' => 'Bandung',
            'is_active' => true,
        ]);

        // 3. Buat Kepala Sekolah
        $kepalaSekolah = User::create([
            'nip' => '197505052000121001',
            'name' => 'Drs. H. Ahmad Yani, M.Pd',
            'email' => 'kepsek.smk@belajar.id',
            'password' => Hash::make('password'),
            'raw_password' => 'password',
            'group' => 'kepala_sekolah',
            'phone' => '081234567892',
            'gender' => 'male',
            'birth_date' => '1975-05-05',
            'birth_place' => 'Subang',
            'address' => 'Jl. Pendidikan No. 1',
            'city' => 'Subang',
            'is_active' => true,
        ]);

        // 4. Buat Kurikulum
        $kurikulum = User::create([
            'nip' => '198008082005122001',
            'name' => 'Siti Nurhaliza, S.Pd, M.Pd',
            'email' => 'kurikulum.smk@belajar.id',
            'password' => Hash::make('password'),
            'raw_password' => 'password',
            'group' => 'kurikulum',
            'phone' => '081234567893',
            'gender' => 'female',
            'birth_date' => '1980-08-08',
            'birth_place' => 'Subang',
            'address' => 'Jl. Pendidikan No. 2',
            'city' => 'Subang',
            'is_active' => true,
        ]);

        // 5. Buat Guru
        $teachers = [
            ['nip' => '198501012010121001', 'name' => 'Budi Santoso, S.Pd', 'email' => 'budi.santoso@belajar.id'],
            ['nip' => '198602022011122002', 'name' => 'Dewi Lestari, S.Pd', 'email' => 'dewi.lestari@belajar.id'],
            ['nip' => '198703032012121003', 'name' => 'Eko Prasetyo, S.Kom', 'email' => 'eko.prasetyo@belajar.id'],
            ['nip' => '198804042013122004', 'name' => 'Fitri Handayani, S.Pd', 'email' => 'fitri.handayani@belajar.id'],
            ['nip' => '198905052014121005', 'name' => 'Gunawan, S.Kom', 'email' => 'gunawan@belajar.id'],
        ];

        $teacherModels = [];
        foreach ($teachers as $teacher) {
            $teacherModels[] = User::create([
                'nip' => $teacher['nip'],
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'raw_password' => 'password',
                'group' => 'guru',
                'phone' => '0812345678' . rand(10, 99),
                'gender' => rand(0, 1) ? 'male' : 'female',
                'birth_date' => '1985-01-01',
                'birth_place' => 'Subang',
                'address' => 'Jl. Guru No. ' . rand(1, 50),
                'city' => 'Subang',
                'is_active' => true,
            ]);
        }

        // 6. Buat Tahun Ajaran
        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        // 7. Buat Mata Pelajaran
        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika', 'category' => 'wajib'],
            ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'category' => 'wajib'],
            ['code' => 'BING', 'name' => 'Bahasa Inggris', 'category' => 'wajib'],
            ['code' => 'PKN', 'name' => 'Pendidikan Kewarganegaraan', 'category' => 'wajib'],
            ['code' => 'SEJ', 'name' => 'Sejarah Indonesia', 'category' => 'wajib'],
            ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'category' => 'wajib'],
            ['code' => 'PWEB', 'name' => 'Pemrograman Web', 'category' => 'peminatan'],
            ['code' => 'BD', 'name' => 'Basis Data', 'category' => 'peminatan'],
            ['code' => 'JARKOM', 'name' => 'Jaringan Komputer', 'category' => 'peminatan'],
            ['code' => 'PBO', 'name' => 'Pemrograman Berorientasi Objek', 'category' => 'peminatan'],
            ['code' => 'BSUN', 'name' => 'Bahasa Sunda', 'category' => 'muatan_lokal'],
            ['code' => 'PRAM', 'name' => 'Pramuka', 'category' => 'ekstrakurikuler'],
        ];

        $subjectModels = [];
        foreach ($subjects as $subject) {
            $subjectModels[] = Subject::create($subject);
        }

        // 8. Buat Kelas
        $classrooms = [];
        $grades = [10, 11, 12];
        $sections = ['A', 'B', 'C'];
        
        foreach ($grades as $grade) {
            foreach ($sections as $section) {
                $romanGrade = ['10' => 'X', '11' => 'XI', '12' => 'XII'][$grade];
                $classrooms[] = Classroom::create([
                    'name' => "{$romanGrade}-RPL-{$section}",
                    'grade' => $grade,
                    'academic_year_id' => $academicYear->id,
                ]);
            }
        }

        // 9. Buat Siswa (1 ketua kelas per kelas + 5 siswa biasa)
        $counter = 1;
        foreach ($classrooms as $classroom) {
            Student::create([
                'nis' => '2025' . str_pad($counter++, 6, '0', STR_PAD_LEFT),
                'name' => 'Ketua Kelas ' . $classroom->name,
                'email' => 'ketua.' . strtolower(str_replace('-', '', $classroom->name)) . '@student.smkn-kasomalang.sch.id',
                'password' => Hash::make('password'),
                'classroom_id' => $classroom->id,
                'is_class_leader' => true,
                'phone' => '0812345678' . rand(10, 99),
            ]);

            for ($i = 1; $i <= 5; $i++) {
                Student::create([
                    'nis' => '2025' . str_pad($counter++, 6, '0', STR_PAD_LEFT),
                    'name' => 'Siswa ' . $i . ' - ' . $classroom->name,
                    'email' => 'siswa' . $i . '.' . strtolower(str_replace('-', '', $classroom->name)) . '@student.smkn-kasomalang.sch.id',
                    'password' => Hash::make('password'),
                    'classroom_id' => $classroom->id,
                    'is_class_leader' => false,
                    'phone' => '0812345678' . rand(10, 99),
                ]);
            }
        }

        // 10. Buat Jam Pelajaran
        $weekdays = [
            ['day' => 1, 'duration' => 45], // Senin
            ['day' => 2, 'duration' => 45], // Selasa
            ['day' => 3, 'duration' => 45], // Rabu
            ['day' => 4, 'duration' => 45], // Kamis
            ['day' => 5, 'duration' => 35], // Jumat
        ];

        foreach ($weekdays as $weekday) {
            $currentTime = strtotime('07:00:00');
            for ($period = 1; $period <= 8; $period++) {
                $startTime = date('H:i:s', $currentTime);
                $currentTime += $weekday['duration'] * 60;
                $endTime = date('H:i:s', $currentTime);

                LessonPeriod::create([
                    'period_number' => $period,
                    'day_of_week' => $weekday['day'],
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'duration_minutes' => $weekday['duration'],
                    'academic_year_id' => $academicYear->id,
                    'is_active' => true,
                ]);
            }
        }

        // 11. Buat Jadwal Pelajaran
        $scheduleData = [
            ['teacher_idx' => 0, 'subject_idx' => 0, 'classroom_idx' => 0, 'day' => 1, 'start' => '07:00', 'end' => '08:30'],
            ['teacher_idx' => 1, 'subject_idx' => 1, 'classroom_idx' => 0, 'day' => 1, 'start' => '08:30', 'end' => '10:00'],
            ['teacher_idx' => 2, 'subject_idx' => 6, 'classroom_idx' => 0, 'day' => 2, 'start' => '07:00', 'end' => '09:30'],
            ['teacher_idx' => 3, 'subject_idx' => 2, 'classroom_idx' => 1, 'day' => 1, 'start' => '07:00', 'end' => '08:30'],
            ['teacher_idx' => 4, 'subject_idx' => 7, 'classroom_idx' => 1, 'day' => 2, 'start' => '07:00', 'end' => '09:30'],
        ];

        foreach ($scheduleData as $schedule) {
            if (isset($teacherModels[$schedule['teacher_idx']]) && 
                isset($subjectModels[$schedule['subject_idx']]) && 
                isset($classrooms[$schedule['classroom_idx']])) {
                
                Schedule::create([
                    'teacher_id' => $teacherModels[$schedule['teacher_idx']]->id,
                    'subject_id' => $subjectModels[$schedule['subject_idx']]->id,
                    'classroom_id' => $classrooms[$schedule['classroom_idx']]->id,
                    'day_of_week' => $schedule['day'],
                    'start_time' => $schedule['start'],
                    'end_time' => $schedule['end'],
                    'academic_year_id' => $academicYear->id,
                    'semester' => 1,
                    'is_active' => true,
                ]);
            }
        }

        // 12. Buat Attendance Settings
        AttendanceSetting::create([
            'method' => 'both_optional',
            'tolerance_before' => 15,
            'tolerance_after' => 15,
            'qr_expiry_minutes' => 5,
            'school_latitude' => -6.4417800,
            'school_longitude' => 107.7610400,
            'location_radius' => 100,
            'face_match_threshold' => 0.6,
        ]);

        $this->command->info('✅ Seeder berhasil!');
        $this->command->info('Superadmin: superadmin@smkn-kasomalang.sch.id / password');
        $this->command->info('Kepala Sekolah: kepsek.smk@belajar.id / password');
        $this->command->info('Kurikulum: kurikulum.smk@belajar.id / password');
        $this->command->info('Guru: *.belajar.id / password');
    }
}
