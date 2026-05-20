<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use Illuminate\Http\Request;

class OltController extends Controller
{
    public function index()
    {
        $olts = Olt::all();
        return view('olts.index', compact('olts'));
    }

    public function create()
    {
        return view('olts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'ip' => 'required|ip',
            'telnet_username' => 'nullable|string',
            'telnet_password' => 'nullable|string',
            'telnet_port' => 'required|integer',
            'snmp_username' => 'nullable|string',
            'snmp_password' => 'nullable|string',
            'snmp_port' => 'required|integer',
        ]);

        Olt::create($validated);

        return redirect()->route('olts.index')->with('success', 'OLT added successfully');
    }

    public function edit(Olt $olt)
    {
        return view('olts.edit', compact('olt'));
    }

    public function update(Request $request, Olt $olt)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'ip' => 'required|ip',
            'telnet_username' => 'nullable|string',
            'telnet_password' => 'nullable|string',
            'telnet_port' => 'required|integer',
            'snmp_username' => 'nullable|string',
            'snmp_password' => 'nullable|string',
            'snmp_port' => 'required|integer',
        ]);

        $olt->update($validated);

        return redirect()->route('olts.index')->with('success', 'OLT updated successfully');
    }

    public function destroy(Olt $olt)
    {
        $olt->delete();
        return redirect()->route('olts.index')->with('success', 'OLT deleted successfully');
    }

    public function status(Olt $olt)
    {
        $telnetUp = false;
        $snmpUp = false;
        $totalOnus = 0;
        $oltType = 'Unknown';
        $uptime = 'Offline';

        // Cek UDP SNMP Port
        $snmpSock = @fsockopen("udp://" . $olt->ip, $olt->snmp_port, $errno, $errstr, 2);
        if ($snmpSock) {
            $snmpUp = true;
            fclose($snmpSock);
        }

        try {
            $telnet = new \App\Services\ZteTelnetService();
            $telnet->connect($olt->ip, $olt->telnet_port, $olt->telnet_username, $olt->telnet_password);
            $telnetUp = true;

            $rackOutput = $telnet->execute("show rack");
            if (preg_match('/C320Rack/', $rackOutput)) {
                $oltType = 'ZTE C320';
            }

            $stateOutput = $telnet->execute("show gpon onu state");
            $totalOnus = substr_count($stateOutput, 'working');

            // Ambil Uptime
            $systemGroupOutput = $telnet->execute("show system-group");
            if (preg_match('/Started before: (\d+) days, (\d+) hours, (\d+) minutes/', $systemGroupOutput, $matches)) {
                $uptime = "{$matches[1]} Hari {$matches[2]} Jam {$matches[3]} Menit";
            } else {
                $uptime = 'Online (Aktif)';
            }

            // Ambil versi OLT
            if (preg_match('/System Description:\s*(C320 Version [^,]+)/', $systemGroupOutput, $verMatch)) {
                $version = $verMatch[1];
            } else {
                $version = 'Unknown Version';
            }

            // Ambil suhu OLT
            // $temp = [];
            $temperatureOutput = $telnet->execute("show card-temperature");
            if (preg_match_all('/\b\d+\b/', $temperatureOutput, $matches)) {
                $temp = $matches[0][10];
            } else {
                $temp = 'N/A';
            }

            $telnet->disconnect();
        } catch (\Exception $e) {
            // Telnet failed
        }

        // Ambil tipe ONT dari database
        $ontTypes = \App\Models\Onu::where('olt_id', $olt->id)
            ->pluck('type')
            ->unique()
            ->implode(', ');

        if (empty($ontTypes)) {
            $ontTypes = 'Belum ada';
        }

        return response()->json([
            'telnet_status' => $telnetUp,
            'snmp_status' => $snmpUp,
            'total_onus' => $totalOnus,
            'olt_type' => $oltType,
            'ont_types' => $ontTypes,
            'uptime' => $uptime,
            'version' => $version,
            'temperature' => $temp,
        ]);
    }

    public function sync(Olt $olt)
    {
        try {
            $telnet = new \App\Services\ZteTelnetService();
            $telnet->connect($olt->ip, $olt->telnet_port, $olt->telnet_username, $olt->telnet_password);

            // 1. Dapatkan semua PON ports
            $stateOutput = $telnet->execute("show gpon onu state");
            $lines = explode("\n", $stateOutput);

            $ponPorts = [];
            foreach ($lines as $line) {
                if (preg_match('/^(\d+\/\d+\/\d+):\d+\s+/', trim($line), $matches)) {
                    $ponPorts[$matches[1]] = true;
                }
            }

            // 2. Dapatkan nama dari running config
            $runConfig = $telnet->execute("show running-config");
            $names = [];

            // Regex mencari block: interface gpon-onu_1/1/1:1 \n name JAJANG MUNJUL
            $configLines = explode("\n", $runConfig);
            $currentOnu = null;

            foreach ($configLines as $line) {
                $line = trim($line);
                if (preg_match('/^interface gpon-onu_(\d+\/\d+\/\d+:\d+)$/', $line, $matches)) {
                    $currentOnu = $matches[1];
                } elseif ($currentOnu && preg_match('/^name\s+(.+)$/', $line, $matches)) {
                    $names[$currentOnu] = trim($matches[1]);
                } elseif ($currentOnu && preg_match('/^description\s+(.+)$/', $line, $matches)) {
                    if (!isset($names[$currentOnu])) {
                        $names[$currentOnu] = trim($matches[1]);
                    }
                } elseif ($line == '!') {
                    $currentOnu = null;
                }
            }

            $syncedCount = 0;

            // 3. Looping per PON port dan cocokan SN, Tipe, & Nama
            foreach (array_keys($ponPorts) as $pon) {
                $baseInfo = $telnet->execute("show gpon onu baseinfo gpon-olt_{$pon}");
                $infoLines = explode("\n", $baseInfo);

                foreach ($infoLines as $line) {
                    // Match: gpon-onu_1/1/1:1    ALL-5G      sn      SN:FHTTC218DEC6         ready
                    if (preg_match('/gpon-onu_(\d+)\/(\d+)\/(\d+):(\d+)\s+(\S+)\s+\S+\s+SN:(\S+)/', trim($line), $matches)) {
                        $board = $matches[1];
                        $slot = $matches[2];
                        $port = $matches[3];
                        $onuIndex = $matches[4];
                        $type = $matches[5];
                        $sn = $matches[6];

                        $onuKey = "{$board}/{$slot}/{$port}:{$onuIndex}";
                        $name = $names[$onuKey] ?? $sn;

                        \App\Models\Onu::updateOrCreate(
                            ['sn' => $sn],
                            [
                                'olt_id' => $olt->id,
                                'board' => $board,
                                'slot' => $slot,
                                'port' => $port,
                                'onu_index' => $onuIndex,
                                'type' => $type,
                                'name' => $name,
                            ]
                        );
                        $syncedCount++;
                    }
                }
            }

            $telnet->disconnect();

            return redirect()->back()->with('success', "Berhasil sinkronisasi {$syncedCount} ONU beserta namanya dari OLT.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
