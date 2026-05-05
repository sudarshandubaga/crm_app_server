<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * List all contacts for the authenticated user's firm.
     */
    public function index(Request $request)
    {
        $contacts = Contact::where('firm_id', $request->user()->firm_id)
            ->with(['customFieldValues.customField'])
            ->latest()
            ->paginate(20);

        return response()->json($contacts);
    }

    /**
     * Store a new contact.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Contact store request:', $request->all());
            $data = $request->validate([
                'first_name'  => 'required|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name'   => 'required|string|max:100',
                'email'       => 'nullable|email|max:150',
                'mobile'      => 'nullable|string|max:15',
                'gender'      => 'nullable|string|max:20',
                'dob'         => 'nullable|string',
                'phone'       => 'nullable|string|max:15',
                'address'     => 'nullable|string|max:255',
                'city'        => 'nullable|string|max:100',
                'state'       => 'nullable|string|max:100',
                'zip'         => 'nullable|string|max:20',
                'country'     => 'nullable|string|max:100',
                'notes'       => 'nullable|string|max:500',
                'custom_fields' => 'nullable|array',
            ]);

            $contact = Contact::create(array_merge($data, [
                'firm_id'     => $request->user()->firm_id,
            ]));

            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $cf) {
                    CustomFieldValue::updateOrCreate(
                        [
                            'custom_field_id' => $cf['field_id'],
                            'model_type'      => Contact::class,
                            'model_id'        => $contact->id,
                        ],
                        ['value' => $cf['value']]
                    );
                }
            }

            return response()->json(['message' => 'Contact created.', 'contact' => $contact], 201);
        } catch (\Exception $e) {
            Log::error('Store Contact Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show a single contact.
     */
    public function show(Request $request, Contact $contact)
    {
        $this->authorizeContact($request, $contact);

        return response()->json([
            'contact' => $contact->load([
                'leads.stage',
                'leads.pipeline',
                'leads.activities.user',
                'customFieldValues.customField'
            ])
        ]);
    }

    /**
     * Update a contact.
     */
    public function update(Request $request, Contact $contact)
    {
        try {
            $this->authorizeContact($request, $contact);

            Log::info('Contact update request:', $request->all());
            $data = $request->validate([
                'first_name'  => 'sometimes|string|max:100',
                'middle_name' => 'nullable|string|max:100',
                'last_name'   => 'sometimes|string|max:100',
                'email'       => 'nullable|email|max:150',
                'mobile'      => 'nullable|string|max:15',
                'gender'      => 'nullable|string|max:20',
                'dob'         => 'nullable|string',
                'phone'       => 'nullable|string|max:15',
                'address'     => 'nullable|string|max:255',
                'city'        => 'nullable|string|max:100',
                'state'       => 'nullable|string|max:100',
                'zip'         => 'nullable|string|max:20',
                'country'     => 'nullable|string|max:100',
                'notes'       => 'nullable|string|max:500',
                'custom_fields' => 'nullable|array',
            ]);

            $contact->update($data);

            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $cf) {
                    CustomFieldValue::updateOrCreate(
                        [
                            'custom_field_id' => $cf['field_id'],
                            'model_type'      => Contact::class,
                            'model_id'        => $contact->id,
                        ],
                        ['value' => $cf['value']]
                    );
                }
            }

            return response()->json(['message' => 'Contact updated.', 'contact' => $contact]);
        } catch (\Exception $e) {
            Log::error('Update Contact Error: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Soft delete a contact.
     */
    public function destroy(Request $request, Contact $contact)
    {
        $this->authorizeContact($request, $contact);
        $contact->delete();

        return response()->json(['message' => 'Contact deleted.']);
    }

    private function authorizeContact(Request $request, Contact $contact): void
    {
        if ($contact->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
