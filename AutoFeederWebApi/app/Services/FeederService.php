<?php

namespace App\Services;

use App\Models\FeederSchedule;
use App\Models\Group;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use App\Models\Feeder;
use Illuminate\Database\Eloquent\Collection;

class FeederService
{
    private Feeder $feeder;

    /**
     * @param Feeder $feeder
     */
    public function __construct(Feeder $feeder)
    {
        $this->feeder = $feeder;
    }

    /**
     * Get a device or create if it does not exist
     *
     * @param string $mac
     * @param string $ip
     * @return Feeder
     */
    public function getOrCreateDevice(string $mac, string $ip): Feeder
    {
        $feeder = $this->feeder->where('mac_address', $mac)->first();
        if (!$feeder) {
            $feeder = $this->feeder->create([
                'ip_address' => $ip,
                'mac_address' => $mac,
                'drier_replaced_at' => Carbon::now(),
            ]);
        } else {
            $feeder->ip_address = $ip;
            $feeder->save();
        }

        return $feeder;
    }

    /**
     * Get all devices
     *
     * @return Collection
     */
    public function devices(): Collection
    {
        return $this->feeder->all();
    }

    /**
     * Add a time slot for a feeder
     *
     * @param Feeder $feeder
     * @param string $time
     * @param int $unit
     * @return void
     * @throws \Exception
     */
    public function addSchedule(Feeder $feeder, string $time, int $unit): void
    {
        $schedule = $feeder->schedules()->where('time', $time)->first();
        if (!$schedule) {
            $feeder->schedules()->create([
                'unit' => $unit,
                'time' => $time,
                'enable' => true
            ]);
            $feeder->schedule_synced = false;
            $feeder->save();
        } else {
            throw new \Exception('Schedule exist for given time');
        }
    }

    /**
     * Toggle time slot
     *
     * @param Feeder $feeder
     * @param FeederSchedule $schedule
     * @return void
     */
    public function toggleState(Feeder $feeder, FeederSchedule $schedule): void
    {
        $schedule = $feeder->schedules()->where('id', $schedule->id)->first();
        $schedule->enable = !($schedule->enable == true);
        $schedule->save();
        $feeder->schedule_synced = false;
        $feeder->save();
    }

    /**
     * Remove a time slot for a feeder
     *
     * @param Feeder $feeder
     * @param FeederSchedule $schedule
     * @return void
     */
    public function removeSchedule(Feeder $feeder, FeederSchedule $schedule): void
    {
        $feeder->schedules()->where('id', $schedule->id)->delete();
        $feeder->schedule_synced = false;
        $feeder->save();
    }

    /**
     * Get schedule for given feeder
     *
     * @param string $feederMacAdderess
     * @return string
     */
    public function getSchedule(string $feederMacAdderess): string
    {
        $feeder = $this->feeder->where('mac_address', $feederMacAdderess)->with('schedules')->first();
        if (!$feeder) {
            return '';
        } else {
            return $this->generateConfigFor($feeder);
        }
    }

    /**
     * Sync schedule to device
     *
     * @param Feeder $feeder
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncToDevice(Feeder $feeder): void
    {
        $config = $this->generateConfigFor($feeder);
        $client = new Client();
        $client->post('http://' . $feeder->ip_address . '/schedule?slots='.$config);
        $feeder->schedule_synced = true;
        $feeder->save();
    }

    /**
     * Generate schedule that can be undertand by the feeder
     *
     * @param Feeder $feeder
     * @return string
     */
    private function generateConfigFor(Feeder $feeder): string
    {
        $feeder->loadMissing('schedules');
        $items = [];
        foreach ($feeder->schedules as $schedule) {
            $timeComponents = explode(':', $schedule->time);
            $items[] = implode(':', array_values([
                'minute' => (int)$timeComponents[1],
                'hour' => (int)$timeComponents[0],
                'unit' => (int)$schedule->unit,
                'enabled' => $schedule->enable ? 1 : 0,
            ]));
        }
        return implode('|', $items);
    }

    /**
     * Dispense food
     *
     * @throws \Throwable
     */
    public function dispense(array|Collection $feeders): void
    {
        $client = new Client();
        $promises = [];
        foreach ($feeders as $feeder) {
            $promises[$feeder->ip_address] = $client->postAsync('http://' . $feeder->ip_address . '/dispense');
        }

        Promise\Utils::settle($promises)->wait();
    }

    /**
     * Identify this feeder
     *
     * @param Feeder $feeder
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function identify(Feeder $feeder): void
    {
        $client = new Client();
        $client->post('http://' . $feeder->ip_address . '/identify');
    }

    /**
     * Update name for this feeder
     *
     * @param Feeder $feeder
     * @param string $name
     * @return void
     */
    public function updateName(Feeder $feeder, string $name): void
    {
        $feeder->name = strlen($name) > 0 ? $name : $feeder->name;
        $feeder->save();
    }

    /**
     * Set when drier was replaced
     *
     * @param Feeder $feeder
     * @return void
     */
    public function replaceDrier(Feeder $feeder): void
    {
        $feeder->drier_replaced_at = Carbon::now();
        $feeder->save();
    }

    /**
     * Get feeders without group
     *
     * @return Collection
     */
    public function feedersWithoutGroup(): Collection
    {
        return $this->feeder->whereNull('group_id')->get();
    }

    /**
     * Assign feeder to a group
     *
     * @param Group $group
     * @param array $feederIds
     * @return void
     */
    public function assignToGroup(Group $group, array $feederIds): void
    {
        $this->feeder->whereIn('id', $feederIds)->update(['group_id' => $group->id]);
    }

    /**
     * Remove feeder form given group
     *
     * @param Group $group
     * @param array $feederIds
     * @return void
     */
    public function unassignFromGroup(Group $group, array $feederIds): void
    {
        $this
            ->feeder
            ->whereIn('id', $feederIds)
            ->where('group_id', $group->id)
            ->update(['group_id' => null]);
    }

    /**
     * Get feeders for a group
     *
     * @param Group $group
     * @return Collection
     */
    public function feedersForGroup(Group $group): Collection
    {
        return $this->feeder->where('group_id', $group->id)->get();
    }

    /**
     * Update a schedule
     *
     * @throws \Exception
     */
    public function updaeSchedule(Feeder $feeder, FeederSchedule $schedule, string $time, int $unit)
    {
        if ($schedule->time != $time) {
            $_schedule = $feeder->schedules()->where('time', $time)->first();
            if ($_schedule) {
                throw new \Exception('Schedule exist for given time');
            }
        }
        $schedule->time = $time;
        $schedule->unit = $unit;
        $schedule->save();
        $feeder->schedule_synced = false;
        $feeder->save();
    }
}
