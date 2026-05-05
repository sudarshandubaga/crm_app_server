<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);

        return response()->json([
            'notes' => $lead->notes()->with('user')->latest()->get(),
        ]);
    }

    public function store(Request $request, Lead $lead)
    {
        $this->authorizeLead($request, $lead);

        $data = $request->validate(['note' => 'required|string']);

        $note = Note::create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()->id,
            'note'    => $data['note'],
        ]);

        return response()->json(['message' => 'Note added.', 'note' => $note->load('user')], 201);
    }

    public function update(Request $request, Lead $lead, Note $note)
    {
        $this->authorizeLead($request, $lead);

        $data = $request->validate(['note' => 'required|string']);
        $note->update($data);

        return response()->json(['message' => 'Note updated.', 'note' => $note]);
    }

    public function destroy(Request $request, Lead $lead, Note $note)
    {
        $this->authorizeLead($request, $lead);
        $note->delete();

        return response()->json(['message' => 'Note deleted.']);
    }

    private function authorizeLead(Request $request, Lead $lead): void
    {
        if ($lead->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
