<?php

namespace App\Http\Controllers\Web;

use App\Enums\AnnounceStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\StatisticResource;
use App\Models\FeederAnnounce;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StatisticController extends Controller
{
    private FeederAnnounce $feederAnnounce;

    public function __construct(FeederAnnounce $feederAnnounce)
    {
        $this->feederAnnounce = $feederAnnounce;
    }

    /**
     * Feeder announcements
     *
     * @return \Inertia\Response
     */
    public function __invoke(Request $request)
    {
        $statistics = $this
            ->feederAnnounce
            ->select('feeder_id', \DB::raw('count(*) as total'))
            ->with([
              'feeder' => function ($query) {
                  return $query->select('name', 'id');
              }
          ])
            ->whereIn('status', [AnnounceStatus::DISPENSE, AnnounceStatus::MANUAL_FEEDING])
            ->groupBy('feeder_id')
            ->where('created_at', '>=', Carbon::today())
            ->get();

        return Inertia::render('Statistic', [
            'statistics' => StatisticResource::collection($statistics)
        ]);
    }
}
