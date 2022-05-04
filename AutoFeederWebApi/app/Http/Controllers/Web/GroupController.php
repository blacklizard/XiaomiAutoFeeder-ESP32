<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupSchedule;
use App\Services\FeederService;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    private GroupService $groupService;

    private FeederService $feederService;

    /**
     * @param GroupService $groupService
     * @param FeederService $feederService
     */
    public function __construct(GroupService $groupService, FeederService $feederService)
    {
        $this->groupService = $groupService;
        $this->feederService = $feederService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $groups = $this->groupService->groups();
        return Inertia::render('Group/Index', [
            'groups' => $groups
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     * @throws \Exception
     */
    public function create()
    {
        $feeders = $this->feederService->feedersWithoutGroup();
        if ($feeders->count() <= 0) {
            throw new \Exception('No feeder avaible to create new group');
        }
        return Inertia::render('Group/Create', [
            'feeders' => $feeders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'feeders' => 'required|array|min:1',
        ]);

        $this->groupService->createGroupAndAssignFeeder($request->name, $request->feeders);
        return redirect()->route('groups.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Group $group
     * @return \Inertia\Response
     */
    public function edit(Group $group)
    {
        $currentFeeders = $this->feederService->feedersForGroup($group);
        $availableFeeders = $this->feederService->feedersWithoutGroup();
        return Inertia::render('Group/Edit', [
            'feeders' => $availableFeeders,
            'current_feeders' => $currentFeeders,
            'group' => $group
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required',
            'feeders' => 'required|array|min:1',
        ]);

        $this->groupService->updateGroupAndAssignFeeder($group, $request->name, $request->feeders);
        return redirect()->route('groups.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Get schedule for given feeder
     *
     * @param Group $group
     * @return Response
     */
    public function showSchedule(Group $group)
    {
        $group->loadMissing('schedules');
        return Inertia::render('Group/Schedule', [
            'group' => $group
        ]);
    }

    /**
     * Add a time slot for a feeder
     *
     * @param Request $request
     * @param Group $group
     * @return RedirectResponse
     * @throws \Exception
     */
    public function addSchedule(Request $request, Group $group)
    {
        $request->validate([
            'unit' => 'required|digits_between:1,10',
            'time' => 'required|date_format:H:i'
        ]);

        $this->groupService->addSchedule($group, $request->time, $request->unit);
        return redirect()->route('groups.schedule.show', ['group' => $group->id]);
    }

    /**
     * Toggle time slot
     *
     * @param Group $group
     * @param GroupSchedule $schedule
     * @return RedirectResponse
     */
    public function toggleSchedule(Group $group, GroupSchedule $schedule)
    {
        $this->groupService->toggleState($group, $schedule);
        return redirect()->route('groups.schedule.show', ['group' => $group->id]);
    }

    /**
     * Remove a time slot for a feeder
     *
     * @param Group $group
     * @param GroupSchedule $schedule
     * @return RedirectResponse
     */
    public function removeSchedule(Group $group, GroupSchedule $schedule)
    {
        $this->groupService->removeSchedule($group, $schedule);
        return redirect()->route('groups.schedule.show', ['group' => $group->id]);
    }

    /**
     * Update schedule
     *
     * @param Request $request
     * @param Group $group
     * @param GroupSchedule $schedule
     * @return RedirectResponse
     * @throws \Exception
     */
    public function updateSchedule(Request $request, Group $group, GroupSchedule $schedule)
    {
        $request->validate([
            'unit' => 'required|digits_between:1,10',
            'time' => 'required|date_format:H:i'
        ]);
        $this->groupService->updateSchedule($group, $schedule, $request->time, $request->unit);
        return redirect()->route('groups.schedule.show', ['group' => $group->id]);
    }

    /**
     * Sync schedule to device
     *
     * @param Group $group
     * @return RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncSchedule(Group $group)
    {
        $this->groupService->syncToDevice($group);
        return redirect()->route('groups.schedule.show', ['group' => $group->id]);
    }

    /**
     * Manually dispense
     *
     * @param Group $group
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function dispenseFeeder(Group $group): RedirectResponse
    {
        $group->loadMissing('feeders');
        $this->feederService->dispense($group->feeders);
        return redirect()->route('groups.index');
    }
}
