<?php

namespace App\Http\Controllers\API;

use App\Enums\AnnounceStatus;
use App\Http\Controllers\Controller;
use App\Models\Feeder;
use App\Services\FeederService;
use Illuminate\Http\Request;

class FeederController extends Controller
{
    private FeederService $feederService;

    /**
     * @param FeederService $feederService
     */
    public function __construct(FeederService $feederService)
    {
        $this->feederService = $feederService;
    }

    /**
     * Handle announce
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function announce(Request $request)
    {
        /** @var Feeder $feeder */
        $feeder = $this->feederService->getOrCreateDevice($request->mac, $request->ip);
        if ($status = AnnounceStatus::tryFrom((int)$request->status)) {
            $feeder->announcements()->create([
                'status' => $status->value
            ]);
        }

        return response()->json([
            'status' => true
        ]);
    }

    /**
     * Return schedule for a device
     *
     * @param $mac
     * @return string
     */
    public function schedule($mac): string
    {
        return $this->feederService->getSchedule($mac);
    }

    public function feed(Feeder $feeder) {
      $this->feederService->dispense([$feeder]);
    }
}
