@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-[#0f2852]">OLT Management</h1>
    <a href="{{ route('olts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add New OLT
    </a>
</div>

<div class="bg-white shadow-sm border border-gray-100 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead>
                <tr class="bg-white border-b border-gray-100">
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider w-16">
                        #
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider min-w-[250px]">
                        DEVICE
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider min-w-[360px]">
                        INFORMATION
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-[#0f2852] uppercase tracking-wider min-w-[180px]">
                        SYNCHRONIZATION
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-[#0f2852] uppercase tracking-wider min-w-[180px]">
                        CONNECTION
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-[#0f2852] uppercase tracking-wider min-w-[120px]">
                        ACTION
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($olts as $index => $olt)
                <tr class="hover:bg-gray-50 transition-colors relative" data-olt-id="{{ $olt->id }}">
                    <!-- Loading Overlay inside the row -->
                    <td id="loader-{{ $olt->id }}" colspan="6" class="absolute inset-0 bg-white/70 backdrop-blur-sm z-10 flex items-center justify-center hidden">
                        <div class="flex items-center space-x-3 text-indigo-600">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium">Memeriksa status perangkat...</span>
                        </div>
                    </td>

                    <td class="px-6 py-6 whitespace-nowrap text-sm font-bold text-gray-900">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-6 py-6 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-20 bg-gray-50 rounded-lg border border-gray-100 flex items-center justify-center overflow-hidden">
                                <!-- Network switch SVG icon as placeholder -->
                                <svg class="h-10 w-14 text-gray-400" viewBox="0 0 64 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="4" width="60" height="24" rx="2" fill="#1F2937" />
                                    <rect x="6" y="8" width="8" height="2" fill="#4B5563" />
                                    <rect x="6" y="12" width="8" height="2" fill="#4B5563" />
                                    <rect x="6" y="16" width="8" height="2" fill="#4B5563" />
                                    <circle cx="24" cy="12" r="2" fill="#10B981" />
                                    <circle cx="28" cy="12" r="2" fill="#10B981" />
                                    <circle cx="32" cy="12" r="2" fill="#10B981" />
                                    <circle cx="36" cy="12" r="2" fill="#4B5563" />
                                    <circle cx="24" cy="18" r="2" fill="#10B981" />
                                    <circle cx="28" cy="18" r="2" fill="#10B981" />
                                    <circle cx="32" cy="18" r="2" fill="#10B981" />
                                    <circle cx="36" cy="18" r="2" fill="#4B5563" />
                                    <circle cx="44" cy="12" r="2" fill="#10B981" />
                                    <circle cx="48" cy="12" r="2" fill="#10B981" />
                                    <circle cx="52" cy="12" r="2" fill="#4B5563" />
                                    <circle cx="56" cy="12" r="2" fill="#4B5563" />
                                    <circle cx="44" cy="18" r="2" fill="#10B981" />
                                    <circle cx="48" cy="18" r="2" fill="#10B981" />
                                    <circle cx="52" cy="18" r="2" fill="#4B5563" />
                                    <circle cx="56" cy="18" r="2" fill="#4B5563" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-[15px] font-bold text-[#0f2852]">{{ $olt->name ?: 'OLT-C320' }}</div>
                                <div class="text-sm text-gray-500 my-0.5">{{ $olt->ip }}</div>
                                <div class="text-xs text-gray-400">
                                    <span class="status-version-{{ $olt->id }}">--</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-6 whitespace-nowrap">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-3">
                            <!-- Suhu -->
                            <div class="flex items-center text-sm text-[#0f2852]">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="status-temp-{{ $olt->id }}">--</span>
                            </div>
                            <!-- Tipe OLT -->
                            <div class="flex items-center text-sm text-[#0f2852]">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                                <span class="status-model-{{ $olt->id }}">--</span>
                            </div>
                            <!-- Total ONT -->
                            <div class="flex items-center text-sm text-[#0f2852]">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                </svg>
                                <span class="status-onus-{{ $olt->id }}">--</span>
                            </div>
                            <!-- Uptime -->
                            <div class="flex items-center text-sm text-[#0f2852]">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                </svg>
                                <span class="status-uptime-{{ $olt->id }}">--</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-6 whitespace-nowrap">
                        <div class="flex items-center justify-center space-x-3">
                            <!-- Circular Progress -->
                            <div class="relative w-12 h-12">
                                <svg class="w-full h-full" viewBox="0 0 36 36">
                                    <path class="text-gray-200" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                                    <path class="text-[#059669] status-sync-circle-{{ $olt->id }}" stroke-dasharray="0, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[10px] font-bold text-[#059669] status-sync-text-{{ $olt->id }}">0%</span>
                                </div>
                            </div>
                            <div class="flex flex-col text-sm">
                                <div class="flex items-center text-[#059669] font-medium status-sync-label-{{ $olt->id }}">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Pending
                                </div>
                                <div class="text-gray-500 text-xs mt-0.5">{{ date('Y-m-d') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-6 whitespace-nowrap text-center">
                        <div class="flex flex-col items-center space-y-2">
                            <span class="px-3 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-gray-100 text-gray-500 status-telnet-{{ $olt->id }}">
                                Telnet Checking
                            </span>
                            <span class="px-3 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-gray-100 text-gray-500 status-snmp-{{ $olt->id }}">
                                SNMP Checking
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-6 whitespace-nowrap text-right">
                        <div class="flex flex-col items-end space-y-1.5">
                            <a href="{{ route('olts.edit', $olt) }}" class="inline-flex items-center justify-center w-24 px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-md transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('olts.sync', $olt) }}" method="POST" class="inline-block" onsubmit="return confirm('Mulai sinkronisasi data ONT dari OLT? Proses ini mungkin membutuhkan waktu beberapa detik.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-24 px-3 py-1.5 text-xs font-medium bg-[#e6f4ea] text-[#137333] hover:bg-[#ceead6] rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Sync
                                </button>
                            </form>
                            <form action="{{ route('olts.destroy', $olt) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this OLT?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-24 px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-md transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span class="text-gray-400">Belum ada OLT yang dikonfigurasi.</span>
                            <a href="{{ route('olts.create') }}" class="mt-4 text-indigo-600 hover:text-indigo-900 font-medium">Tambah OLT Sekarang</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const olts = document.querySelectorAll('[data-olt-id]');
        olts.forEach(olt => {
            fetchOltStatus(olt.getAttribute('data-olt-id'));
        });
    });

    function fetchOltStatus(id) {
        const loader = document.getElementById(`loader-${id}`);
        const refreshIcon = document.querySelector(`.refresh-icon-${id}`);

        if (loader) {
            loader.classList.remove('hidden');
        }
        if (refreshIcon) {
            refreshIcon.classList.add('animate-spin');
        }

        fetch(`/olts/${id}/status`)
            .then(response => response.json())
            .then(data => {
                document.querySelector(`.status-model-${id}`).textContent = data.olt_type;
                document.querySelector(`.status-uptime-${id}`).textContent = data.uptime;
                document.querySelector(`.status-version-${id}`).textContent = data.version;
                document.querySelector(`.status-temp-${id}`).textContent = data.temperature;
                document.querySelector(`.status-onus-${id}`).textContent = data.total_onus;

                // Update Telnet Badge
                const telnetBadge = document.querySelector(`.status-telnet-${id}`);
                if (data.telnet_status) {
                    telnetBadge.className = 'px-3 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-[#148348] text-white status-telnet-' + id;
                    telnetBadge.textContent = 'Telnet Connected';
                } else {
                    telnetBadge.className = 'px-3 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-red-600 text-white status-telnet-' + id;
                    telnetBadge.textContent = 'Telnet Failed';
                }

                // Update SNMP Badge
                const snmpBadge = document.querySelector(`.status-snmp-${id}`);
                if (data.snmp_status) {
                    snmpBadge.className = 'px-3 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-[#148348] text-white status-snmp-' + id;
                    snmpBadge.textContent = 'SNMP Connected';
                } else {
                    snmpBadge.className = 'px-3 py-1 rounded-md text-[11px] font-bold tracking-wider uppercase bg-red-600 text-white status-snmp-' + id;
                    snmpBadge.textContent = 'SNMP Failed';
                }

                // Update Sync progress to 100% Completed
                const syncCircle = document.querySelector(`.status-sync-circle-${id}`);
                const syncText = document.querySelector(`.status-sync-text-${id}`);
                const syncLabel = document.querySelector(`.status-sync-label-${id}`);

                if (data.telnet_status || data.snmp_status) {
                    syncCircle.setAttribute('stroke-dasharray', '100, 100');
                    syncText.textContent = '100%';
                    syncLabel.innerHTML = '<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Completed';
                } else {
                    syncCircle.setAttribute('stroke-dasharray', '0, 100');
                    syncText.textContent = '0%';
                    syncText.classList.replace('text-[#059669]', 'text-red-500');
                    syncCircle.classList.replace('text-[#059669]', 'text-red-500');
                    syncLabel.innerHTML = '<svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Failed';
                    syncLabel.classList.replace('text-[#059669]', 'text-red-500');
                }
            })
            .catch(error => {
                console.error('Error fetching OLT status:', error);
            })
            .finally(() => {
                if (loader) {
                    loader.classList.add('hidden');
                }
                if (refreshIcon) {
                    refreshIcon.classList.remove('animate-spin');
                }
            });
    }
</script>
@endsection