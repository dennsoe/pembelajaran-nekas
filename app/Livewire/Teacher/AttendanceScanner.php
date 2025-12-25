<?php

namespace App\Livewire\Teacher;

use App\Models\Schedule;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceScanner extends Component
{
    public $schedule;
    public $attendanceMethod = 'qr_code'; // qr_code, face_recognition, gps
    public $qrCode;
    public $facePhoto;
    public $latitude;
    public $longitude;
    public $isCheckingIn = false;
    public $attendance;

    protected $attendanceService;
    protected $qrCodeService;
    protected $faceService;

    protected $rules = [
        'attendanceMethod' => 'required|in:qr_code,face_recognition,gps',
        'qrCode' => 'required_if:attendanceMethod,qr_code',
        'facePhoto' => 'required_if:attendanceMethod,face_recognition',
        'latitude' => 'required_if:attendanceMethod,gps|nullable|numeric',
        'longitude' => 'required_if:attendanceMethod,gps|nullable|numeric',
    ];

    public function boot(
        AttendanceService $attendanceService,
        QrCodeService $qrCodeService,
        FaceRecognitionService $faceService
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
        $this->faceService = $faceService;
    }

    public function mount($scheduleId)
    {
        $this->schedule = Schedule::with(['subject', 'classroom', 'lessonPeriod'])
            ->findOrFail($scheduleId);

        $user = Auth::user();
        
        // Check if already has attendance today
        $this->attendance = Attendance::where('user_id', $user->id)
            ->where('schedule_id', $this->schedule->id)
            ->whereDate('date', Carbon::today())
            ->first();
    }

    public function checkIn()
    {
        $this->validate();

        $user = Auth::user();

        if ($this->attendance && $this->attendance->time_in) {
            session()->flash('error', 'Anda sudah melakukan check-in');
            return;
        }

        $this->isCheckingIn = true;

        try {
            DB::beginTransaction();

            $qrCodeId = null;
            $faceMatchScore = null;

            // Validate based on method
            if ($this->attendanceMethod === 'qr_code') {
                $qrValidation = $this->qrCodeService->validateQrCode($this->qrCode);
                if (!$qrValidation['valid']) {
                    session()->flash('error', $qrValidation['message']);
                    $this->isCheckingIn = false;
                    return;
                }
                $qrCodeId = $qrValidation['qr_code_id'] ?? null;
            }

            if ($this->attendanceMethod === 'face_recognition') {
                $faceValidation = $this->faceService->verifyFace($user, $this->facePhoto);
                if (!$faceValidation['match']) {
                    session()->flash('error', 'Verifikasi wajah gagal');
                    $this->isCheckingIn = false;
                    return;
                }
                $faceMatchScore = $faceValidation['score'] ?? null;
            }

            // GPS validation
            $gpsValid = true;
            if ($this->latitude && $this->longitude) {
                $gpsValid = $this->attendanceService->validateGPS(
                    $this->latitude,
                    $this->longitude
                );
            }

            // Create attendance
            $this->attendance = $this->attendanceService->checkIn([
                'user_id' => $user->id,
                'schedule_id' => $this->schedule->id,
                'method_in' => $this->attendanceMethod,
                'latitude_in' => $this->latitude,
                'longitude_in' => $this->longitude,
                'qr_code_id' => $qrCodeId,
                'face_photo_in' => $this->attendanceMethod === 'face_recognition' ? $this->facePhoto : null,
                'face_match_score_in' => $faceMatchScore,
                'is_valid_gps_in' => $gpsValid,
            ]);

            DB::commit();

            session()->flash('success', 'Check-in berhasil! Status: ' . $this->attendance->status);
            
            // Reset form
            $this->reset(['qrCode', 'facePhoto', 'latitude', 'longitude']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Check-in gagal: ' . $e->getMessage());
        } finally {
            $this->isCheckingIn = false;
        }
    }

    public function checkOut()
    {
        $this->validate();

        $user = Auth::user();

        if (!$this->attendance || !$this->attendance->time_in) {
            session()->flash('error', 'Anda belum melakukan check-in');
            return;
        }

        if ($this->attendance->time_out) {
            session()->flash('error', 'Anda sudah melakukan check-out');
            return;
        }

        $this->isCheckingIn = true;

        try {
            DB::beginTransaction();

            $faceMatchScore = null;

            // Validate based on method
            if ($this->attendanceMethod === 'qr_code') {
                $qrValidation = $this->qrCodeService->validateQrCode($this->qrCode);
                if (!$qrValidation['valid']) {
                    session()->flash('error', $qrValidation['message']);
                    $this->isCheckingIn = false;
                    return;
                }
            }

            if ($this->attendanceMethod === 'face_recognition') {
                $faceValidation = $this->faceService->verifyFace($user, $this->facePhoto);
                if (!$faceValidation['match']) {
                    session()->flash('error', 'Verifikasi wajah gagal');
                    $this->isCheckingIn = false;
                    return;
                }
                $faceMatchScore = $faceValidation['score'] ?? null;
            }

            // GPS validation
            $gpsValid = true;
            if ($this->latitude && $this->longitude) {
                $gpsValid = $this->attendanceService->validateGPS(
                    $this->latitude,
                    $this->longitude
                );
            }

            // Update attendance
            $this->attendance = $this->attendanceService->checkOut($this->attendance, [
                'method_out' => $this->attendanceMethod,
                'latitude_out' => $this->latitude,
                'longitude_out' => $this->longitude,
                'face_photo_out' => $this->attendanceMethod === 'face_recognition' ? $this->facePhoto : null,
                'face_match_score_out' => $faceMatchScore,
                'is_valid_gps_out' => $gpsValid,
            ]);

            DB::commit();

            session()->flash('success', 'Check-out berhasil! Durasi: ' . $this->attendance->duration_minutes . ' menit');
            
            // Reset form
            $this->reset(['qrCode', 'facePhoto', 'latitude', 'longitude']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Check-out gagal: ' . $e->getMessage());
        } finally {
            $this->isCheckingIn = false;
        }
    }

    public function requestLocation()
    {
        $this->dispatch('requestLocation');
    }

    public function setLocation($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    public function render()
    {
        return view('livewire.teacher.attendance-scanner');
    }
}
