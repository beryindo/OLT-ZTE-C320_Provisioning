@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('olts.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit OLT</h1>
    </div>
</div>

<div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-100 max-w-3xl">
    <form action="{{ route('olts.update', $olt) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">OLT Name (Optional)</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('name', $olt->name) }}">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label for="ip" class="block text-sm font-medium text-gray-700">IP Address <span class="text-red-500">*</span></label>
                <input type="text" name="ip" id="ip" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('ip', $olt->ip) }}" required>
                @error('ip') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div class="col-span-2"><hr class="my-2 border-gray-200"></div>

            <!-- Telnet Settings -->
            <div class="col-span-2 md:col-span-1">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Telnet Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="telnet_username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="telnet_username" id="telnet_username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('telnet_username', $olt->telnet_username) }}">
                        @error('telnet_username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="telnet_password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="telnet_password" id="telnet_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('telnet_password', $olt->telnet_password) }}">
                        @error('telnet_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="telnet_port" class="block text-sm font-medium text-gray-700">Port</label>
                        <input type="number" name="telnet_port" id="telnet_port" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('telnet_port', $olt->telnet_port) }}" required>
                        @error('telnet_port') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- SNMP Settings -->
            <div class="col-span-2 md:col-span-1">
                <h3 class="text-lg font-medium text-gray-900 mb-3">SNMP Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="snmp_username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="snmp_username" id="snmp_username" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('snmp_username', $olt->snmp_username ?: 'rconfigrw') }}">
                        @error('snmp_username') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="snmp_password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="snmp_password" id="snmp_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('snmp_password', $olt->snmp_password) }}">
                        @error('snmp_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="snmp_port" class="block text-sm font-medium text-gray-700">Port</label>
                        <input type="number" name="snmp_port" id="snmp_port" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border" value="{{ old('snmp_port', $olt->snmp_port) }}" required>
                        @error('snmp_port') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <a href="{{ route('olts.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3 transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                Update OLT
            </button>
        </div>
    </form>
</div>
@endsection
