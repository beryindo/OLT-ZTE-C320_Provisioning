@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <h1 class="text-2xl font-bold text-gray-900">Provisioned ONUs</h1>
    <form action="{{ route('onus.index') }}" method="GET" class="w-full sm:w-auto flex items-center">
        <label for="search" class="mr-3 text-gray-500 flex-shrink-0 cursor-pointer hover:text-indigo-600 transition-colors" title="Cari">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </label>
        <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Cari Nama, SN, Target..." class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition">
    </form>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-100">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider">Nama & SN</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider">Interface</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider">RX OLT</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0f2852] uppercase tracking-wider">RX ONU</th>
                    <th scope="col" class="relative px-6 py-3 text-right text-xs font-bold text-[#0f2852] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($onus as $onu)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-50 rounded-full flex items-center justify-center">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $onu->name }}</div>
                                    <div class="text-xs font-mono text-gray-500 mt-0.5">{{ $onu->sn }} <span class="text-gray-400">({{ $onu->type }})</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">{{ $onu->olt->name ?? $onu->olt->ip }}</div>
                            <div class="text-xs text-gray-500">gpon-onu_{{ $onu->board }}/{{ $onu->slot }}/{{ $onu->port }}:{{ $onu->onu_index }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 power-rx-olt-{{ $onu->id }}">
                                --
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 power-rx-onu-{{ $onu->id }}">
                                --
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('onus.edit', $onu) }}" class="text-indigo-600 hover:text-indigo-900 transition flex items-center" title="Setting">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </a>
                                <form action="{{ route('onus.destroy', $onu) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ONU ini dari OLT? (Aksi ini tidak dapat dibatalkan!)');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition flex items-center" title="Delete">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <span class="block text-sm font-medium">Tidak ada ONU yang ditemukan.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($onus->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $onus->links() }}
        </div>
    @endif
</div>

<script>
    const onuIds = @json($onus->pluck('id'));
    const totalOnusCount = {{ $totalDbOnusCount }};

    async function checkAllPower() {
        // Set semua ke status loading di awal
        onuIds.forEach(id => {
            const rxOltBadge = document.querySelector(`.power-rx-olt-${id}`);
            const rxOnuBadge = document.querySelector(`.power-rx-onu-${id}`);
            
            rxOltBadge.innerHTML = '<svg class="animate-spin h-3 w-3 mr-1 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading';
            rxOnuBadge.innerHTML = '<svg class="animate-spin h-3 w-3 mr-1 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Loading';
            
            rxOltBadge.className = 'px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-600 power-rx-olt-' + id;
            rxOnuBadge.className = 'px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-600 power-rx-onu-' + id;
        });

        // Eksekusi penarikan data secara background (batch of 3 untuk mencegah OLT crash)
        for (let i = 0; i < onuIds.length; i += 3) {
            const batch = onuIds.slice(i, i + 3);
            await Promise.all(batch.map(id => checkPowerSilent(id)));
        }
    }

    // Fungsi fetch tanpa merubah status menjadi loading lagi (karena sudah di awal)
    function checkPowerSilent(id) {
        const rxOltBadge = document.querySelector(`.power-rx-olt-${id}`);
        const rxOnuBadge = document.querySelector(`.power-rx-onu-${id}`);

        return fetch(`/onus/${id}/power`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    rxOltBadge.textContent = data.rx_olt;
                    rxOnuBadge.textContent = data.rx_onu;
                    
                    const oltVal = parseFloat(data.rx_olt);
                    const onuVal = parseFloat(data.rx_onu);

                    const getColorClass = (val) => {
                        if (isNaN(val)) return 'bg-gray-100 text-gray-800';
                        if (val >= -27 && val <= -10) return 'bg-green-100 text-green-800';
                        if (val < -27 && val >= -30) return 'bg-yellow-100 text-yellow-800';
                        return 'bg-red-100 text-red-800';
                    };

                    rxOltBadge.className = `px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full ${getColorClass(oltVal)} power-rx-olt-${id}`;
                    rxOnuBadge.className = `px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full ${getColorClass(onuVal)} power-rx-onu-${id}`;
                } else {
                    throw new Error('Gagal');
                }
            })
            .catch(error => {
                rxOltBadge.textContent = 'Error';
                rxOnuBadge.textContent = 'Error';
                rxOltBadge.className = 'px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 power-rx-olt-' + id;
                rxOnuBadge.className = 'px-2 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 power-rx-onu-' + id;
            });
    }

    // Auto-run when page loads
    window.onload = () => {
        // 1. Sinkronisasi ONU baru secara background (AJAX)
        fetch('/onus/sync-background', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success && data.count > totalOnusCount) {
                  // Jika ada ONU baru, otomatis refresh tabel secara halus
                  window.location.reload();
              }
          }).catch(err => console.error("Background sync error:", err));

        // 2. Langsung eksekusi cek power (RX OLT & ONU) secara background
        if (onuIds.length > 0) {
            checkAllPower();
        }
    };
</script>
@endsection
