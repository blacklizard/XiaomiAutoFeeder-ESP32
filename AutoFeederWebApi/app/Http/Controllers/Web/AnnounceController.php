<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Models\FeederAnnounce;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnounceController extends Controller
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
        $announcements = $this->feederAnnounce->orderBy('id', 'desc')->with([
            'feeder' => function ($query) {
                return $query->select('name', 'id');
            }
        ])->paginate();

        return Inertia::render('Announcement', [
            'announcements' => AnnouncementResource::collection($announcements)
        ]);
    }
}
