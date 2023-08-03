<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feeder;
use App\Models\FeederSchedule;
use App\Models\Group;
use App\Models\GroupSchedule;
use App\Services\FeederService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

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
     * Display all devices
     *
     * @return Response
     */
    public function index(): Response
    {
        $devices = $this->feederService->devices();
        return Inertia::render('Device', [
            'devices' => $devices
        ]);
    }

    /**
     * Get schedule for given feeder
     *
     * @param Feeder $feeder
     * @return Response
     */
    public function showSchedule(Feeder $feeder)
    {
        $feeder->loadMissing('schedules');
        return Inertia::render('Schedule', [
            'device' => $feeder
        ]);
    }

    /**
     * Add a time slot for a feeder
     *
     * @param Request $request
     * @param Feeder $feeder
     * @return RedirectResponse
     * @throws \Exception
     */
    public function addSchedule(Request $request, Feeder $feeder)
    {
        $request->validate([
            'unit' => 'required|digits_between:1,10',
            'time' => 'required|date_format:H:i'
        ]);

        $this->feederService->addSchedule($feeder, $request->time, $request->unit);
        return redirect()->route('feeders.schedule.show', ['feeder' => $feeder->id]);
    }

    /**
     * Toggle time slot
     *
     * @param Feeder $feeder
     * @param FeederSchedule $schedule
     * @return RedirectResponse
     */
    public function toggleSchedule(Feeder $feeder, FeederSchedule $schedule)
    {
        $this->feederService->toggleState($feeder, $schedule);
        return redirect()->route('feeders.schedule.show', ['feeder' => $feeder->id]);
    }

    /**
     * Remove a time slot for a feeder
     *
     * @param Feeder $feeder
     * @param FeederSchedule $schedule
     * @return RedirectResponse
     */
    public function removeSchedule(Feeder $feeder, FeederSchedule $schedule)
    {
        $this->feederService->removeSchedule($feeder, $schedule);
        return redirect()->route('feeders.schedule.show', ['feeder' => $feeder->id]);
    }

    /**
     * Update schedule
     *
     * @param Request $request
     * @param Feeder $feeder
     * @param FeederSchedule $schedule
     * @return RedirectResponse
     * @throws \Exception
     */
    public function updateSchedule(Request $request, Feeder $feeder, FeederSchedule $schedule)
    {
        $request->validate([
            'unit' => 'required|digits_between:1,10',
            'time' => 'required|date_format:H:i'
        ]);

        $this->feederService->updaeSchedule($feeder, $schedule, $request->time, $request->unit);
        return redirect()->route('feeders.schedule.show', ['feeder' => $feeder->id]);
    }
    /**
     * Sync schedule to device
     *
     * @param Feeder $feeder
     * @return RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncSchedule(Feeder $feeder)
    {
        $this->feederService->syncToDevice($feeder);
        return redirect()->route('feeders.schedule.show', ['feeder' => $feeder->id]);
    }

    /**
     * Manually dispense
     *
     * @param Feeder $feeder
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function dispenseFeeder(Feeder $feeder): RedirectResponse
    {
        Log::info($feeder);
        $this->feederService->dispense([$feeder]);
        return redirect()->route('feeders.index');
    }

    /**
     * Identify a feeder
     *
     * @param Feeder $feeder
     * @return RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function identify(Feeder $feeder)
    {
        $this->feederService->identify($feeder);
        return redirect()->route('feeders.index');
    }

    /**
     * Update name for this feeder
     *
     * @param Request $request
     * @param Feeder $feeder
     * @return RedirectResponse
     */
    public function name(Request $request, Feeder $feeder)
    {
        $this->feederService->updateName($feeder, $request->name);
        return redirect()->route('feeders.index');
    }

    /**
     * Set drier replaced date
     *
     * @param Request $request
     * @param Feeder $feeder
     * @return RedirectResponse
     */
    public function replaceDrier(Request $request, Feeder $feeder)
    {
        $this->feederService->replaceDrier($feeder);
        return redirect()->route('feeders.index');
    }
}
