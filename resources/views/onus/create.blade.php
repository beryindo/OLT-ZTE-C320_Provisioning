@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('onus.unconfigured') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Provision ONU</h1>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-100 max-w-3xl">
    <form action="{{ route('onus.store') }}" method="POST" class="p-6">
        @csrf

        @if ($errors->any())
        <div class="mb-6 bg-red-50 p-4 rounded-md border border-red-200">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 bg-red-50 p-4 rounded-md border border-red-200 text-sm text-red-600">
            {{ session('error') }}
        </div>
        @endif

        <!-- Info Bar untuk Hardware / ONU Index -->
        <div class="mb-6 bg-indigo-50 border border-indigo-100 rounded-lg p-4 flex flex-wrap justify-between items-center gap-4">
            <div class="flex flex-col">
                <span class="text-xs text-indigo-500 font-semibold uppercase tracking-wider">Target Port</span>
                <span class="font-mono text-sm text-indigo-900 mt-1">gpon-olt_{{ $prefill['board'] }}/{{ $prefill['slot'] }}/{{ $prefill['port'] }}</span>
            </div>
            <div class="flex flex-col items-center text-center">
                <span class="text-xs text-indigo-500 font-semibold uppercase tracking-wider">OLT Target</span>
                <span class="mt-1 font-mono text-sm font-bold text-indigo-900">{{ $olts->find($prefill['olt_id'])->name ?? $olts->find($prefill['olt_id'])->ip ?? 'N/A' }}</span>
            </div>
            <div class="flex flex-col text-right">
                <span class="text-xs text-indigo-500 font-semibold uppercase tracking-wider">Serial Number</span>
                <span class="font-mono text-sm text-indigo-900 mt-1">{{ $prefill['sn'] }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2 md:col-span-1">
                <label for="onu_index" class="block text-sm font-medium text-gray-700">ONU Index <span class="text-red-500">*</span></label>
                <select name="onu_index" id="onu_index" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border font-mono" required>
                    @if(isset($availableIndices) && count($availableIndices) > 0)
                    @foreach($availableIndices as $idx)
                    <option value="{{ $idx }}" {{ old('onu_index', $prefill['onu_index']) == $idx ? 'selected' : '' }}>Index {{ $idx }} (Available)</option>
                    @endforeach
                    @else
                    <option value="{{ $prefill['onu_index'] }}" selected>Index {{ $prefill['onu_index'] }} (Fallback)</option>
                    @endif
                </select>
                @error('onu_index') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label for="name" class="block text-sm font-medium text-gray-700">ONU Name / Description <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('name') }}" placeholder="e.g., Customer A" required>
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Hidden Hardware Information -->
            <input type="hidden" name="olt_id" value="{{ old('olt_id', $prefill['olt_id']) }}">
            <input type="hidden" name="board" id="hidden_board" value="{{ old('board', $prefill['board']) }}">
            <input type="hidden" name="slot" id="hidden_slot" value="{{ old('slot', $prefill['slot']) }}">
            <input type="hidden" name="port" id="hidden_port" value="{{ old('port', $prefill['port']) }}">
            <input type="hidden" name="sn" value="{{ old('sn', $prefill['sn']) }}">

            <div class="col-span-2 md:col-span-1">
                <label for="type" class="block text-sm font-medium text-gray-700">ONU Type <span class="text-red-500">*</span></label>
                <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" required>
                    <option value="ALL-5G" {{ old('type') == 'ALL-5G' ? 'selected' : '' }}>ALL-5G</option>
                    <option value="ZTE-F609" {{ old('type') == 'ZTE-F609' ? 'selected' : '' }}>ZTE-F609</option>
                    <option value="ZTE-F660" {{ old('type') == 'ZTE-F660' ? 'selected' : '' }}>ZTE-F660</option>
                    <option value="ZTE-F670" {{ old('type') == 'ZTE-F670' ? 'selected' : '' }}>ZTE-F670</option>
                </select>
                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label for="upstream_profile" class="block text-sm font-medium text-gray-700">Upstream Profile (TCONT) <span class="text-red-500">*</span></label>
                <select name="upstream_profile" id="upstream_profile" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" required>
                    <option value="">Select Upstream Profile</option>
                    @if(isset($tcontProfiles) && count($tcontProfiles) > 0)
                    @foreach($tcontProfiles as $p)
                    <option value="{{ $p }}" {{ old('upstream_profile', 'UP-1G') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                    @else
                    <option value="UP-1G" {{ old('upstream_profile') == 'UP-1G' ? 'selected' : '' }}>UP-1G (Fallback)</option>
                    @endif
                </select>
                @error('upstream_profile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label for="downstream_profile" class="block text-sm font-medium text-gray-700">Downstream Profile (Traffic Limit) <span class="text-red-500">*</span></label>
                <select name="downstream_profile" id="downstream_profile" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" required>
                    <option value="">Select Downstream Profile</option>
                    @if(isset($trafficProfiles) && count($trafficProfiles) > 0)
                    @foreach($trafficProfiles as $p)
                    <option value="{{ $p }}" {{ old('downstream_profile', 'DOWN-1G') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                    @else
                    <option value="DOWN-1G" {{ old('downstream_profile') == 'DOWN-1G' ? 'selected' : '' }}>DOWN-1G (Fallback)</option>
                    @endif
                </select>
                @error('downstream_profile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Custom Script / Manual Configuration</h3>
                <div class="flex items-center space-x-3">
                    @if(isset($scriptTemplates) && $scriptTemplates->count() > 0)
                    <div class="flex items-center space-x-2">
                        <label for="script_template_select" class="text-sm font-medium text-gray-700">Template:</label>
                        <select id="script_template_select" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-3 pr-8">
                            @foreach($scriptTemplates as $template)
                                <option value="{{ $template->id }}"
                                    data-gpon="{{ $template->gpon_onu_script }}"
                                    data-mng="{{ $template->pon_onu_mng_script }}"
                                    {{ (isset($defaultTemplate) && $defaultTemplate->id == $template->id) ? 'selected' : '' }}>
                                    {{ $template->merk }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Bisa diedit manual sebelum provision</span>
                </div>
            </div>

            <div class="space-y-6">
                <!-- ONU Interface Config -->
                <div class="bg-gray-800 rounded-md overflow-hidden shadow">
                    <div class="bg-gray-900 px-4 py-2 flex items-center justify-between">
                        <span class="text-xs font-mono text-gray-300" id="preview_onu_interface">interface gpon-onu_{{ $prefill['board'] }}/{{ $prefill['slot'] }}/{{ $prefill['port'] }}:{{ $prefill['onu_index'] }}</span>
                    </div>
                    <textarea name="raw_gpon_onu" id="raw_gpon_onu" rows="12" class="w-full p-4 text-xs font-mono text-green-400 bg-gray-800 border-0 focus:ring-2 focus:ring-indigo-500 whitespace-pre" spellcheck="false">tcont 1 name TR069 profile UP-1G
tcont 2 name PPPoE profile UP-1G
tcont 3 name HOSTPOT profile UP-1G
gemport 1 tcont 1
gemport 1 traffic-limit downstream DOWN-1G
gemport 2 tcont 2
gemport 2 traffic-limit downstream DOWN-1G
gemport 3 tcont 3
gemport 3 traffic-limit downstream DOWN-1G
service-port 1 vport 1 user-vlan 100 vlan 100
service-port 2 vport 2 user-vlan 301 vlan 301
service-port 3 vport 3 user-vlan 302 vlan 302</textarea>
                </div>


                <!-- ONU Management Config -->
                <div class="bg-gray-800 rounded-md overflow-hidden shadow">
                    <div class="bg-gray-900 px-4 py-2 flex items-center justify-between">
                        <span class="text-xs font-mono text-gray-300" id="preview_mng_interface">pon-onu-mng gpon-onu_{{ $prefill['board'] }}/{{ $prefill['slot'] }}/{{ $prefill['port'] }}:{{ $prefill['onu_index'] }}</span>
                        
                        @if(isset($acsProfiles) && $acsProfiles->count() > 0)
                        <div class="flex items-center space-x-2">
                            <label for="acs_profile_select" class="text-xs text-gray-400">ACS:</label>
                            <select id="acs_profile_select" class="text-xs bg-gray-700 text-white border-gray-600 rounded px-2 py-1 focus:ring-indigo-500">
                                @foreach($acsProfiles as $profile)
                                    <option value="{{ $profile->id }}" 
                                        data-url="{{ $profile->url }}" 
                                        data-user="{{ $profile->username }}" 
                                        data-pass="{{ $profile->password }}"
                                        {{ (isset($defaultAcs) && $defaultAcs->id == $profile->id) ? 'selected' : '' }}>
                                        {{ $profile->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                    <textarea name="raw_pon_onu_mng" id="raw_pon_onu_mng" rows="8" class="w-full p-4 text-xs font-mono text-green-400 bg-gray-800 border-0 focus:ring-2 focus:ring-indigo-500 whitespace-pre" spellcheck="false">service TR069 gemport 1 vlan 100
service PPPoE gemport 2 vlan 301
service HOSTPOT gemport 3 vlan 302
vlan port veip_1 mode hybrid def-vlan 100
vlan port veip_1 vlan 100,301,302
tr069-mgmt 1 state unlock
tr069-mgmt 1 acs {{ isset($defaultAcs) ? $defaultAcs->url : 'http://103.192.174.162:7547' }} validate basic username {{ isset($defaultAcs) ? $defaultAcs->username : 'acs' }} password {{ isset($defaultAcs) ? $defaultAcs->password : 'acsadmin12345' }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <a href="{{ route('onus.unconfigured') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3 transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                Provision ONU
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const onuIndexSelect = document.getElementById('onu_index');
        const hiddenBoard = document.getElementById('hidden_board').value;
        const hiddenSlot = document.getElementById('hidden_slot').value;
        const hiddenPort = document.getElementById('hidden_port').value;

        const upSelect = document.getElementById('upstream_profile');
        const downSelect = document.getElementById('downstream_profile');

        const rawGpon = document.getElementById('raw_gpon_onu');
        const rawMng = document.getElementById('raw_pon_onu_mng');
        const previewOnuInterface = document.getElementById('preview_onu_interface');
        const previewMngInterface = document.getElementById('preview_mng_interface');

        const acsSelect = document.getElementById('acs_profile_select');
        const templateSelect = document.getElementById('script_template_select');

        function updateScripts() {
            const up = upSelect.value || 'UP-1G';
            const down = downSelect.value || 'DOWN-1G';
            const onuIdx = onuIndexSelect.value || '1';
            
            let acsUrl = 'http://103.192.174.162:7547';
            let acsUser = 'acs';
            let acsPass = 'acsadmin12345';
            
            if (acsSelect && acsSelect.options.length > 0) {
                const opt = acsSelect.options[acsSelect.selectedIndex];
                acsUrl = opt.getAttribute('data-url');
                acsUser = opt.getAttribute('data-user') || '';
                acsPass = opt.getAttribute('data-pass') || '';
            }

            // Update interface titles
            previewOnuInterface.textContent = `interface gpon-onu_${hiddenBoard}/${hiddenSlot}/${hiddenPort}:${onuIdx}`;
            previewMngInterface.textContent = `pon-onu-mng gpon-onu_${hiddenBoard}/${hiddenSlot}/${hiddenPort}:${onuIdx}`;

            let gponTemplate = `tcont 1 name TR069 profile {UP_PROFILE}
tcont 2 name PPPoE profile {UP_PROFILE}
tcont 3 name HOSTPOT profile {UP_PROFILE}
gemport 1 tcont 1
gemport 1 traffic-limit downstream {DOWN_PROFILE}
gemport 2 tcont 2
gemport 2 traffic-limit downstream {DOWN_PROFILE}
gemport 3 tcont 3
gemport 3 traffic-limit downstream {DOWN_PROFILE}
service-port 1 vport 1 user-vlan 100 vlan 100
service-port 2 vport 2 user-vlan 301 vlan 301
service-port 3 vport 3 user-vlan 302 vlan 302`;

            let mngTemplate = `service TR069 gemport 1 vlan 100
service PPPoE gemport 2 vlan 301
service HOSTPOT gemport 3 vlan 302
vlan port veip_1 mode hybrid def-vlan 100
vlan port veip_1 vlan 100,301,302
tr069-mgmt 1 state unlock
tr069-mgmt 1 acs {ACS_URL} validate basic username {ACS_USER} password {ACS_PASS}`;

            if (templateSelect && templateSelect.options.length > 0) {
                const opt = templateSelect.options[templateSelect.selectedIndex];
                gponTemplate = opt.getAttribute('data-gpon');
                mngTemplate = opt.getAttribute('data-mng');
            }

            rawGpon.value = gponTemplate
                .replace(/{UP_PROFILE}/g, up)
                .replace(/{DOWN_PROFILE}/g, down);

            rawMng.value = mngTemplate
                .replace(/{ACS_URL}/g, acsUrl)
                .replace(/{ACS_USER}/g, acsUser)
                .replace(/{ACS_PASS}/g, acsPass);
        }

        onuIndexSelect.addEventListener('change', updateScripts);
        upSelect.addEventListener('change', updateScripts);
        downSelect.addEventListener('change', updateScripts);
        if (acsSelect) {
            acsSelect.addEventListener('change', updateScripts);
        }
        if (templateSelect) {
            templateSelect.addEventListener('change', updateScripts);
        }
        
        // Initial execution to populate templates correctly
        updateScripts();
    });
</script>
@endsection