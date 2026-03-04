<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::query()
            ->where('is_public', true)
            ->with('user')
            ->orderBy('starts_at', 'asc')
            ->paginate(20);

        return view('events.index', compact('events'));
    }

    public function show(Event $event, Request $request)
    {
        $event->load(['user','rsvps.user']);

        $my = null;
        if ($request->user()) {
            $my = EventRsvp::where('event_id',$event->id)
                ->where('user_id',$request->user()->id)
                ->first();
        }

        return view('events.show', compact('event','my'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $event = Event::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'is_public' => true,
        ]);

        return redirect()->route('events.show', $event)->with('status','Event created.');
    }

    public function edit(Request $request, Event $event)
    {
        abort_unless($event->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        abort_unless($event->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $data = $this->validateData($request);

        $event->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        return redirect()->route('events.show', $event)->with('status','Event updated.');
    }

    public function destroy(Request $request, Event $event)
    {
        abort_unless($event->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $event->delete();

        return redirect()->route('events.index')->with('status', 'Event deleted.');
    }

    public function rsvp(Request $request, Event $event)
    {
        $data = $request->validate([
            'status' => ['required','in:yes,maybe,no'],
        ]);

        EventRsvp::updateOrCreate(
            ['event_id'=>$event->id, 'user_id'=>$request->user()->id],
            ['status'=>$data['status']]
        );

        return back()->with('status','RSVP saved.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required','string','max:160'],
            'description' => ['nullable','string','max:6000'],
            'location' => ['nullable','string','max:160'],
            'starts_at' => ['required','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
        ]);
    }
}
