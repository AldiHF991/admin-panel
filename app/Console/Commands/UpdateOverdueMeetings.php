<?php

namespace App\Console\Commands;

use App\Events\DashboardUpdate;
use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateOverdueMeetings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rapat:update-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status rapat menjadi Overdue jika masih Menunggu setelah waktu selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Cari rapat yang statusnya 'Menunggu' (3) dan waktu selesainya sudah lewat
        // Asumsi: tanggal + waktu_end < sekarang
        $overdueMeetings = Rapat::where('id_status', 3)
            ->where(function ($query) use ($now) {
                $query->where('tanggal', '<', $now->toDateString())
                      ->orWhere(function ($q) use ($now) {
                          $q->where('tanggal', '=', $now->toDateString())
                            ->where('waktu_end', '<', $now->toTimeString());
                      });
            })
            ->get();

        if ($overdueMeetings->isEmpty()) {
            $this->info('Tidak ada rapat yang overdue.');
            return;
        }

        foreach ($overdueMeetings as $rapat) {
            $rapat->update(['id_status' => 6]); // 6 = Overdue
            $this->info("Rapat ID {$rapat->id_rapat} ({$rapat->judul}) diubah menjadi Overdue.");
            
            // Trigger update dashboard
            DashboardUpdate::dispatch($rapat->id_rapat, 6);
        }

        $this->info('Update status overdue selesai.');
    }
}
