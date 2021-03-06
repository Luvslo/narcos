<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

use App\Character;
use App\OrganizedCrime;
use App\Property;

use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    private $oneDayInMinutes = 1440;
    private $oneWeekInMinutes = 10080;
    private $oneHourInMinutes = 60;
    private $maxYieldStore = 10;
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            Property::all()->each(function ($property) {
                if ($property->inProduction()) {
                    $property->yield = min($this->maxYieldStore, $property->yield + generateLabYield());
                    $property->save();
                }
            });
        })->hourly();

        $schedule->call(function () {
            // todo: optimize this bit, db is normalized into char -> bank. So querying all banks
            // then for each bank decide if it should get any payout. iff thats true, then populate
            // the character and update all columns. Now ALL characters with their banks are queried
            // which is unnecessary since most characters dont have a payout pending.
            Character::all()->each(function ($char) {
                if ($char->bank->money > 0 && $char->bank->hoursSinceLastAction() === 0) {
                    $char->money += $char->bank->moneyWithInterest();
                    $char->save();
                    $char->bank->money = 0;
                    $char->bank->save();
                }
            });
        })->everyThirtyMinutes();

        $schedule->call(function () {
            // heal all alive players whom its life is lower than 100
            Character::where('life', '<', 100)->where('life', '>', 0)->each(function ($char) {
                // heal with 20% daily
                $char->life = min(100, $char->life + 20);
                $char->save();
            });
        })->dailyAt('6:00');

        $schedule->call(function () {
            OrganizedCrime::all()->each(function ($oc) {
                // kill every inactive party (24hr timeout)
                if (Carbon::parse($oc->updated_at)->diffInMinutes(Carbon::now()) > 1440) {
                    $oc->delete();
                }
            });
        })->everyThirtyMinutes();

        $schedule->call(function () {
            $bulletsAndCost = generateBulletsAndCost();
            $bullets = $bulletsAndCost[0];
            $cost = $bulletsAndCost[1];
            // todo: this section should be based on cache locks, therefore we should
            // switch over to memcached or redis to make use of such locking, for now
            // ordinary hacking in cache with possibilities for race conditions
            // use something like this:
            // Cache::lock('bullets-quantity')->get(function () {
            //     Cache::put('bullets-quantity', $bullets, $this->oneDayInMinutes);
            // });
            // Cache::lock('bullets-cost')->get(function () {
            //     Cache::put('bullets-cost', $cost, $this->oneDayInMinutes);
            // });
            Cache::put('bullets-quantity', $bullets, $this->oneWeekInMinutes + $this->oneHourInMinutes);
            Cache::put('bullets-cost', $cost, $this->oneWeekInMinutes + $this->oneHourInMinutes);
        })->weekly()->saturdays()->at('11:00');

        // Daily at 6 in the morning randomize the drugroute
        $schedule->call(function () {
            Cache::put('contraband-prices', generateContrabandPrices(), $this->oneDayInMinutes + $this->oneHourInMinutes);
        })->dailyAt('6:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
