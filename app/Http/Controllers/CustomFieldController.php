<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\CustomFieldCategory;
use App\Models\CustomFieldValue;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    // ── Categories ──────────────────────────────────────────────────────────

    public function categories(Request $request)
    {
        $categories = CustomFieldCategory::where('firm_id', $request->user()->firm_id)
            ->with('customFields')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'for'  => 'required|in:lead,contact',
        ]);

        $category = CustomFieldCategory::create(array_merge($data, [
            'firm_id' => $request->user()->firm_id,
        ]));

        return response()->json(['message' => 'Category created.', 'category' => $category], 201);
    }

    public function updateCategory(Request $request, CustomFieldCategory $customFieldCategory)
    {
        $this->authorizeCategory($request, $customFieldCategory);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'for'  => 'sometimes|in:lead,contact',
        ]);

        $customFieldCategory->update($data);

        return response()->json(['message' => 'Category updated.', 'category' => $customFieldCategory]);
    }

    public function destroyCategory(Request $request, CustomFieldCategory $customFieldCategory)
    {
        $this->authorizeCategory($request, $customFieldCategory);
        $customFieldCategory->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    // ── Fields ───────────────────────────────────────────────────────────────

    public function storeField(Request $request, CustomFieldCategory $customFieldCategory)
    {
        $this->authorizeCategory($request, $customFieldCategory);

        $data = $request->validate([
            'label'         => 'required|string|max:150',
            'type'          => 'required|string|in:text,number,date,url,email,textarea,radio,checkbox,select,list',
            'is_required'   => 'boolean',
            'options'       => 'nullable|array',
            'default_value' => 'nullable|string|max:255',
            'extra_attr'    => 'nullable|array',
        ]);

        $field = CustomField::create([
            'custom_field_category_id' => $customFieldCategory->id,
            'firm_id'                  => $request->user()->firm_id,
            'label'                    => $data['label'],
            'type'                     => $data['type'],
            'is_required'              => $data['is_required'] ?? false,
            'options'                  => isset($data['options']) ? json_encode($data['options']) : null,
            'default_value'            => $data['default_value'] ?? null,
            'extra_attr'               => isset($data['extra_attr']) ? json_encode($data['extra_attr']) : null,
        ]);

        return response()->json(['message' => 'Field created.', 'field' => $field], 201);
    }

    public function updateField(Request $request, CustomField $customField)
    {
        if ($customField->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }

        $data = $request->validate([
            'label'         => 'sometimes|string|max:150',
            'type'          => 'sometimes|string',
            'is_required'   => 'boolean',
            'options'       => 'nullable|array',
            'default_value' => 'nullable|string|max:255',
            'extra_attr'    => 'nullable|array',
        ]);

        if (isset($data['options'])) {
            $data['options'] = json_encode($data['options']);
        }
        if (isset($data['extra_attr'])) {
            $data['extra_attr'] = json_encode($data['extra_attr']);
        }

        $customField->update($data);

        return response()->json(['message' => 'Field updated.', 'field' => $customField]);
    }

    public function destroyField(Request $request, CustomField $customField)
    {
        if ($customField->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }

        $customField->delete();

        return response()->json(['message' => 'Field deleted.']);
    }

    // ── Values (save field values for a lead/contact) ───────────────────────

    public function saveValues(Request $request)
    {
        $data = $request->validate([
            'model_type'         => 'required|in:lead,contact',
            'model_id'           => 'required|integer',
            'values'             => 'required|array',
            'values.*.field_id'  => 'required|exists:custom_fields,id',
            'values.*.value'     => 'nullable|string',
        ]);

        $modelType = $data['model_type'] === 'lead'
            ? \App\Models\Lead::class
            : \App\Models\Contact::class;

        foreach ($data['values'] as $item) {
            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $item['field_id'],
                    'model_type'      => $modelType,
                    'model_id'        => $data['model_id'],
                ],
                ['value' => $item['value'] ?? null]
            );
        }

        return response()->json(['message' => 'Custom field values saved.']);
    }

    private function authorizeCategory(Request $request, CustomFieldCategory $category): void
    {
        if ($category->firm_id !== $request->user()->firm_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
