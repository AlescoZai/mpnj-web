<?php

namespace App\Console\Commands;

use App\Models\Transaksi;
use Illuminate\Console\Command;

class TransaksiCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transaksi Akan Dicek Perhari Pada Jam 00.00';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Database logic
        $dateTime = date('Y-m-d H:i:s');
        $cek['transaksi'] = Transaksi::with('pembeli')->where('batas_transaksi', $dateTime)->get();
        if ($cek) {
            Transaksi::where('proses_pembayaran', 'belum')->update(['proses_pembayaran' => 'tolak']);
            echo "Data Updated";
        }
    }
}
