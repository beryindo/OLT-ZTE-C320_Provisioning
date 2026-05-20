<?php

namespace App\Http\Controllers;

use App\Models\ScriptTemplate;
use Illuminate\Http\Request;

class ScriptTemplateController extends Controller
{
    public function index()
    {
        $templates = ScriptTemplate::latest()->get();
        return view('script_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('script_templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'merk' => 'required|string|max:255',
            'gpon_onu_script' => 'required|string',
            'pon_onu_mng_script' => 'required|string',
            'is_default' => 'boolean'
        ]);

        if (!empty($validated['is_default'])) {
            ScriptTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        ScriptTemplate::create($validated);

        return redirect()->route('script-templates.index')->with('success', 'Script Template created successfully.');
    }

    public function edit(ScriptTemplate $scriptTemplate)
    {
        return view('script_templates.edit', compact('scriptTemplate'));
    }

    public function update(Request $request, ScriptTemplate $scriptTemplate)
    {
        $validated = $request->validate([
            'merk' => 'required|string|max:255',
            'gpon_onu_script' => 'required|string',
            'pon_onu_mng_script' => 'required|string',
            'is_default' => 'boolean'
        ]);

        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            ScriptTemplate::where('id', '!=', $scriptTemplate->id)->update(['is_default' => false]);
        }

        $scriptTemplate->update($validated);

        return redirect()->route('script-templates.index')->with('success', 'Script Template updated successfully.');
    }

    public function destroy(ScriptTemplate $scriptTemplate)
    {
        $scriptTemplate->delete();
        return redirect()->route('script-templates.index')->with('success', 'Script Template deleted successfully.');
    }
}
