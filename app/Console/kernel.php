// routes/console.php
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\TutupSesiAbsensi;

Schedule::command('sesi:tutup')->everyMinute();