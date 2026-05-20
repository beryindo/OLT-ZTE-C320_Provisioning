<?php

namespace App\Http\Controllers;

use App\Models\AcsProfile;
use Illuminate\Http\Request;

class AcsProfileController extends Controller
{
    public function index()
    {
        $profiles = AcsProfile::latest()->get();
        return view('acs_profiles.index', compact('profiles'));
    }

    public function create()
    {
        return view('acs_profiles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'is_default' => 'boolean'
        ]);

        if (!empty($validated['is_default'])) {
            AcsProfile::where('is_default', true)->update(['is_default' => false]);
        }

        AcsProfile::create($validated);

        return redirect()->route('acs-profiles.index')->with('success', 'ACS Profile created successfully.');
    }

    public function edit(AcsProfile $acsProfile)
    {
        return view('acs_profiles.edit', compact('acsProfile'));
    }

    public function update(Request $request, AcsProfile $acsProfile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'is_default' => 'boolean'
        ]);

        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            AcsProfile::where('id', '!=', $acsProfile->id)->update(['is_default' => false]);
        }

        $acsProfile->update($validated);

        return redirect()->route('acs-profiles.index')->with('success', 'ACS Profile updated successfully.');
    }

    public function destroy(AcsProfile $acsProfile)
    {
        $acsProfile->delete();
        return redirect()->route('acs-profiles.index')->with('success', 'ACS Profile deleted successfully.');
    }
}
