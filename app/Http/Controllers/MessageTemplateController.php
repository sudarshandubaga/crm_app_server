<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = MessageTemplate::where('firm_id', $request->user()->firm_id)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $templates]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template = MessageTemplate::create(array_merge($data, [
            'firm_id' => $request->user()->firm_id,
        ]));

        return response()->json(['message' => 'Template created.', 'data' => $template], 201);
    }

    public function show(Request $request, MessageTemplate $messageTemplate)
    {
        $this->authorizeTemplate($request, $messageTemplate);
        return response()->json(['data' => $messageTemplate]);
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $this->authorizeTemplate($request, $messageTemplate);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $messageTemplate->update($data);

        return response()->json(['message' => 'Template updated.', 'data' => $messageTemplate]);
    }

    public function destroy(Request $request, MessageTemplate $messageTemplate)
    {
        $this->authorizeTemplate($request, $messageTemplate);
        $messageTemplate->delete();

        return response()->json(['message' => 'Template deleted.']);
    }

    private function authorizeTemplate(Request $request, MessageTemplate $template)
    {
        if ($template->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
