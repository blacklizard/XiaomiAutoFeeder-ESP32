<?php

namespace App\Services;

use App\Models\Feeder;
use App\Models\Group;
use App\Models\GroupSchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupService
{
    private Group $group;
    private FeederService $feederService;

    /**
     * @param Group $group
     * @param FeederService $feederService
     */
    public function __construct(Group $group, FeederService $feederService)
    {
        $this->group = $group;
        $this->feederService = $feederService;
    }

    /**
     * @return Collection|array
     */
    public function groups(): Collection|array
    {
        return $this->group->with('feeders')->get();
    }

    /**
     * Create a group and assign feeder
     *
     * @param string $name
     * @param array $feederIds
     * @return void
     */
    public function createGroupAndAssignFeeder(string $name, array $feederIds): void
    {
        DB::transaction(function () use ($name, $feederIds) {
            $group = $this->group->create([
                'name' => $name
            ]);

            $this->feederService->assignToGroup($group, $feederIds);
        });
    }

    /**
     * Update a group and assign feeder
     *
     * @param Group $group
     * @param string $name
     * @param array $feederIds
     * @return void
     */
    public function updateGroupAndAssignFeeder(Group $group, string $name, array $feederIds): void
    {
        DB::transaction(function () use ($group, $name, $feederIds) {
            $group->name = $name;
            $group->save();
            $group->loadMissing('feeders');
            if ($group->feeders->count() > 0) {
                $this->feederService->unassignFromGroup($group, $group->feeders->pluck('id')->all());
            }
            $this->feederService->assignToGroup($group, $feederIds);
        });
    }

    /**
     * Add a time slot for a group
     *
     * @param Group $group
     * @param string $time
     * @param int $unit
     * @return void
     * @throws \Exception
     */
    public function addSchedule(Group $group, string $time, int $unit): void
    {
        $schedule = $group->schedules()->where('time', $time)->first();
        if (!$schedule) {
            $group->schedules()->create([
                'unit' => $unit,
                'time' => $time,
                'enable' => true
            ]);
            $group->schedule_synced = false;
            $group->save();
        } else {
            throw new \Exception('Schedule exist for given time');
        }
    }

    /**
     * Toggle time slot
     *
     * @param Group $group
     * @param GroupSchedule $schedule
     * @return void
     */
    public function toggleState(Group $group, GroupSchedule $schedule): void
    {
        $schedule = $group->schedules()->where('id', $schedule->id)->first();
        $schedule->enable = !($schedule->enable == true);
        $schedule->save();
        $group->schedule_synced = false;
        $group->save();
    }

    /**
     * Remove a time slot for a group
     *
     * @param Group $group
     * @param GroupSchedule $schedule
     * @return void
     */
    public function removeSchedule(Group $group, GroupSchedule $schedule): void
    {
        $group->schedules()->where('id', $schedule->id)->delete();
        $group->schedule_synced = false;
        $group->save();
    }

    /**
     * Sync group schedule to device
     *
     * @param Group $group
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncToDevice(Group $group)
    {
        $group->loadMissing('feeders', 'schedules');
        $schedules = [];
        foreach ($group->schedules as $schedule) {
            $schedules[] = [
                'unit' => $schedule->unit,
                'time' => $schedule->time,
                'enable' => $schedule->enable,
            ];
        }
        foreach ($group->feeders as $feeder) {
            /** @var Feeder $feeder */
            $feeder->schedules()->delete();
            $feeder->schedules()->createMany($schedules);
            $this->feederService->syncToDevice($feeder);
        }

        $group->schedule_synced = true;
        $group->save();
    }

    /**
     * Update schedule
     *
     * @throws \Exception
     */
    public function updateSchedule(Group $group, GroupSchedule $schedule, string $time, int $unit)
    {
        if ($schedule->time != $time) {
            $_schedule = $group->schedules()->where('time', $time)->first();
            if ($_schedule) {
                throw new \Exception('Schedule exist for given time');
            }
        }
        $schedule->time = $time;
        $schedule->unit = $unit;
        $schedule->save();
        $group->schedule_synced = false;
        $group->save();
    }
}
