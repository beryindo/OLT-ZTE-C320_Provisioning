@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('script-templates.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Add New Script Template</h1>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-100 max-w-4xl">
    <form action="{{ route('script-templates.store') }}" method="POST" class="p-6">
        @csrf

        <div class="space-y-6">
            <div>
                <label for="merk" class="block text-sm font-medium text-gray-700">Merk / Brand <span class="text-red-500">*</span></label>
                <input type="text" name="merk" id="merk" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('merk') }}" placeholder="e.g. ZTE F609" required>
                @error('merk') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Gunakan placeholder berikut untuk injeksi data otomatis saat provision: 
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">{UP_PROFILE}</code>, 
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">{DOWN_PROFILE}</code>, 
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">{ACS_URL}</code>, 
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">{ACS_USER}</code>, 
                    <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-600">{ACS_PASS}</code>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- GPON ONU Script -->
                <div>
                    <label for="gpon_onu_script" class="block text-sm font-medium text-gray-700 mb-2">interface gpon-onu Script <span class="text-red-500">*</span></label>
                    <textarea name="gpon_onu_script" id="gpon_onu_script" rows="12" class="w-full p-4 text-xs font-mono text-gray-900 bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 whitespace-pre" spellcheck="false" required>{{ old('gpon_onu_script', "tcont 1 name TR069 profile {UP_PROFILE}\ntcont 2 name PPPoE profile {UP_PROFILE}\ntcont 3 name HOSTPOT profile {UP_PROFILE}\ngemport 1 tcont 1\ngemport 1 traffic-limit downstream {DOWN_PROFILE}\ngemport 2 tcont 2\ngemport 2 traffic-limit downstream {DOWN_PROFILE}\ngemport 3 tcont 3\ngemport 3 traffic-limit downstream {DOWN_PROFILE}\nservice-port 1 vport 1 user-vlan 100 vlan 100\nservice-port 2 vport 2 user-vlan 301 vlan 301\nservice-port 3 vport 3 user-vlan 302 vlan 302") }}</textarea>
                    @error('gpon_onu_script') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- PON ONU MNG Script -->
                <div>
                    <label for="pon_onu_mng_script" class="block text-sm font-medium text-gray-700 mb-2">pon-onu-mng Script <span class="text-red-500">*</span></label>
                    <textarea name="pon_onu_mng_script" id="pon_onu_mng_script" rows="12" class="w-full p-4 text-xs font-mono text-gray-900 bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 whitespace-pre" spellcheck="false" required>{{ old('pon_onu_mng_script', "service TR069 gemport 1 vlan 100\nservice PPPoE gemport 2 vlan 301\nservice HOSTPOT gemport 3 vlan 302\nvlan port veip_1 mode hybrid def-vlan 100\nvlan port veip_1 vlan 100,301,302\ntr069-mgmt 1 state unlock\ntr069-mgmt 1 acs {ACS_URL} validate basic username {ACS_USER} password {ACS_PASS}") }}</textarea>
                    @error('pon_onu_mng_script') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center">
                <input id="is_default" name="is_default" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('is_default') ? 'checked' : '' }}>
                <label for="is_default" class="ml-2 block text-sm text-gray-900">
                    Set as Default Script Template
                </label>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <a href="{{ route('script-templates.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3 transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Save Template
            </button>
        </div>
    </form>
</div>
@endsection
