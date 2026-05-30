<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlagiarismCheck extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'title',
        'file_path',
        'similarity_score',
        'similarity_category',
        'status',
        'report_data',
        'error_message',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'similarity_score' => 'decimal:2',
        'report_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plagiarismParaphrase(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PlagiarismParaphrase::class);
    }

    /**
     * Process the plagiarism check.
     */
    public function processCheck(): void
    {
        $this->update(['status' => 'processing', 'error_message' => null]);

        try {
            $plagiarismService = app('plagiarism')->driver();
            $results = $plagiarismService->check($this);

            $this->update([
                'title' => $results['detected_title'] ?? $this->title,
                'similarity_score' => $results['similarity_score'] ?? 0,
                'similarity_category' => $results['similarity_category'] ?? 'rendah',
                'report_data' => $results,
                'status' => 'completed',
            ]);

            // Consume Quota
            app(\App\Services\PlagiarismQuotaService::class)->consumeQuota($this->user);

            // Send Email
            \Illuminate\Support\Facades\Mail::to($this->email ?? $this->user->email)
                ->send(new \App\Mail\PlagiarismCheckResultMail($this));

            \Filament\Notifications\Notification::make()
                ->title('Analisis Berhasil')
                ->body('Pengecekan plagiasi telah selesai diproses. Hasil analisis dan laporan lengkap telah dikirimkan ke email Anda.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $errorMessage = (config('app.env') === 'local' || env('APP_ENV') === 'local') 
                ? $e->getMessage() 
                : 'Server turnitin sedang high traffic silakan cek ulang dalam beberapa menit dengan menekan tombol "Re-Check"';

            \Filament\Notifications\Notification::make()
                ->title('Gagal Memproses')
                ->body($errorMessage)
                ->danger()
                ->send();
        }
    }

    /**
     * Get category based on score.
     */
    public static function getCategoryForScore(?float $score): string
    {
        if ($score === null) return 'N/A';
        
        if ($score < 20) return 'rendah';
        if ($score < 50) return 'sedang';
        return 'tinggi';
    }
}
