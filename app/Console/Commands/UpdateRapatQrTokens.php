<?php

namespace App\Console\Commands;

use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateRapatQrTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rapat:update-qr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update QR code tokens untuk rapat yang sedang berlangsung';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info('Mengecek rapat aktif untuk update QR token...');

        $activeMeetings = Rapat::whereIn('id_status', [4]) // Hanya rapat dengan status "Sedang Berlangsung"
            ->whereRaw('? BETWEEN CONCAT(tanggal, " ", waktu_start) AND CONCAT(tanggal, " ", waktu_end)', [$now])
            ->get();

        if ($activeMeetings->isEmpty()) {
            $this->info('Tidak ada rapat berlangsung.');

            return 0;
        }

        foreach ($activeMeetings as $rapat) {
            // 1. Ambil token 'current' yang sekarang
            $old_current_token = $rapat->current_qr_token;

            // 2. Buat token baru
            $new_token = (string) Str::uuid(); // UUID lebih baik dari Str::random()

            // 3. Update database
            $rapat->previous_qr_token = $old_current_token;
            $rapat->current_qr_token = $new_token;
            $rapat->qr_token_expires_at = $now->copy()->addSeconds(30);
            $rapat->save();

            $this->info("Token untuk Rapat [{$rapat->judul}] telah diupdate.");
        }

        $this->info('Semua token rapat aktif telah diupdate.');

        return 0;
    }
}
