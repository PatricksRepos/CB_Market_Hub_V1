<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventUpsertRequest;
use App\Models\Event;
use App\Models\EventRsvp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->where('is_public', true)
            ->with('user')
            ->orderBy('starts_at', 'asc')
            ->paginate(20);

        return view('events.index', compact('events'));
    }

    public function show(Event $event, Request $request): View
    {
        $event->load(['user', 'rsvps.user']);

        $my = null;
        if ($request->user()) {
            $my = EventRsvp::query()
                ->where('event_id', $event->id)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return view('events.show', compact('event', 'my'));
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(EventUpsertRequest $request): RedirectResponse
    {
        $event = Event::create([
            ...$request->safe()->except(['image', 'remove_image']),
            'user_id' => $request->user()->id,
            'is_public' => true,
            'image_path' => $request->file('image')?->store('event-images', 'public'),
        ]);

        return redirect()->route('events.show', $event)->with('status', 'Event created.');
    }

    public function edit(Request $request, Event $event): View
    {
        $this->authorizeEventUpdate($request, $event);

        return view('events.edit', compact('event'));
    }

    public function update(EventUpsertRequest $request, Event $event): RedirectResponse
    {
        $this->authorizeEventUpdate($request, $event);

        $payload = $request->safe()->except(['image', 'remove_image']);

        if ((bool) $request->boolean('remove_image')) {
            $this->deleteEventImage($event);
            $payload['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteEventImage($event);
            $payload['image_path'] = $request->file('image')->store('event-images', 'public');
        }

        $event->update($payload);

        return redirect()->route('events.show', $event)->with('status', 'Event updated.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEventUpdate($request, $event);

        $this->deleteEventImage($event);
        $event->delete();

        return redirect()->route('events.index')->with('status', 'Event deleted.');
    }

    public function rsvp(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:yes,maybe,no'],
        ]);

        EventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $request->user()->id],
            ['status' => $data['status']]
        );

        return back()->with('status', 'RSVP saved.');
    }

    private function authorizeEventUpdate(Request $request, Event $event): void
    {
        abort_unless($event->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
    }

    private function deleteEventImage(Event $event): void
    {
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }
    }
}
