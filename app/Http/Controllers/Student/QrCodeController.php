<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Schedule;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show QR Code (only for class leaders)
     */
    public function show(Request $request)
    {
        $student = Auth::guard('student')->user();

        if (!$student->is_class_leader) {
            return view('siswa.qrcode')->with('error', 'Hanya ketua kelas yang dapat mengakses QR Code');
        }

        if (!$student->classroom_id) {
            return view('siswa.qrcode')->with('error', 'Anda belum terdaftar di kelas manapun');
        }

        // Get or create QR code for today
        $qrCode = QrCode::where('classroom_id', $student->classroom_id)
            ->whereDate('valid_from', '<=', Carbon::now())
            ->whereDate('valid_until', '>=', Carbon::now())
            ->where('is_active', true)
            ->first();

        if (!$qrCode) {
            // Generate new QR code for today
            $qrCode = $this->qrCodeService->generateQrCode($student->classroom_id);
        }

        // Get today's schedules
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeekIso;

        $todaySchedules = Schedule::with(['subject', 'teacher', 'lessonPeriod'])
            ->where('classroom_id', $student->classroom_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('lesson_period_id')
            ->get();

        return view('siswa.qrcode', compact('qrCode', 'todaySchedules', 'student'));
    }

    /**
     * Show class schedule
     */
    public function schedule(Request $request)
    {
        $student = Auth::guard('student')->user();

        if (!$student->classroom_id) {
            return view('siswa.jadwal')->with('error', 'Anda belum terdaftar di kelas manapun');
        }

        $schedules = Schedule::with(['subject', 'teacher', 'lessonPeriod', 'academicYear'])
            ->where('classroom_id', $student->classroom_id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('lesson_period_id')
            ->get();

        // Group by day
        $groupedSchedules = $schedules->groupBy('day_of_week')->map(function ($daySchedules, $dayOfWeek) {
            return [
                'day' => Carbon::now()->startOfWeek()->addDays($dayOfWeek - 1)->format('l'),
                'day_of_week' => $dayOfWeek,
                'schedules' => $daySchedules,
            ];
        });

        return view('siswa.jadwal', compact('groupedSchedules', 'student'));
    }
}
