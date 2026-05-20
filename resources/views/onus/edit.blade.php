@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Setting ONU</h1>
        <p class="text-sm text-gray-500 mt-1">Konfigurasi perangkat ONT yang sudah ter-provisioning di OLT.</p>
    </div>
    <a href="{{ route('onus.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali
    </a>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-100 max-w-2xl">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('onus.update', $onu) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
                
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Informasi Perangkat (Read Only)</label>
                    <div class="bg-gray-50 px-4 py-3 rounded-md border border-gray-200 text-sm text-gray-600 flex justify-between items-center">
                        <div>
                            <span class="font-bold text-gray-900">{{ $onu->sn }}</span>
                            <span class="mx-2 text-gray-300">|</span>
                            <span class="font-mono">{{ $onu->type }}</span>
                        </div>
                        <div class="text-xs font-mono bg-white px-2 py-1 border border-gray-200 rounded text-indigo-600">
                            gpon-onu_{{ $onu->board }}/{{ $onu->slot }}/{{ $onu->port }}:{{ $onu->onu_index }}
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama / Keterangan Pelanggan</label>
                    <div class="mt-1">
                        <input type="text" name="name" id="name" value="{{ old('name', $onu->name) }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md py-2 px-3 border shadow-sm" required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Nama ini akan langsung diubah di perangkat OLT menggunakan command <code>name [keterangan]</code>.</p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="window.history.back();" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                    Batal
                </button>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
