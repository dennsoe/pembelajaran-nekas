<?php

namespace App\Livewire\Student;

use App\Models\QrCode;
use App\Services\QrCodeService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class QrCodeGenerator extends Component
{
    public $qrCode;
    public $student;
    public $refreshInterval = 300; // 5 minutes

    protected $qrCodeService;

    public function boot(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function mount()
    {
        $this->student = Auth::guard('student')->user();
        
        if (!$this->student->is_class_leader) {
            session()->flash('error', 'Hanya ketua kelas yang dapat mengakses QR Code');
            return;
        }

        $this->generateQrCode();
    }

    public function generateQrCode()
    {
        if (!$this->student || !$this->student->classroom_id) {
            session()->flash('error', 'Anda belum terdaftar di kelas manapun');
            return;
        }

        // Check if QR code for today exists and is still valid
        $this->qrCode = QrCode::where('classroom_id', $this->student->classroom_id)
            ->whereDate('valid_from', '<=', Carbon::now())
            ->whereDate('valid_until', '>=', Carbon::now())
            ->where('is_active', true)
            ->first();

        if (!$this->qrCode) {
            // Generate new QR code
            $this->qrCode = $this->qrCodeService->generateQrCode($this->student->classroom_id);
        }

        session()->flash('success', 'QR Code berhasil di-generate!');
    }

    public function refreshQrCode()
    {
        // Deactivate old QR code
        if ($this->qrCode) {
            $this->qrCode->update(['is_active' => false]);
        }

        // Generate new QR code
        $this->qrCode = $this->qrCodeService->generateQrCode($this->student->classroom_id);
        
        session()->flash('success', 'QR Code baru berhasil di-generate!');
    }

    public function render()
    {
        return view('livewire.student.qr-code-generator');
    }
}
