<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use App\Models\Onu;
use App\Services\ZteTelnetService;
use Illuminate\Http\Request;

class OnuController extends Controller
{
    public function index(Request $request)
    {
        $query = Onu::with('olt');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sn', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('olt', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('ip', 'like', "%{$search}%");
                    });
            });
        }

        $onus = $query->latest()->paginate(5)->withQueryString();
        $totalDbOnusCount = \App\Models\Onu::count();

        return view('onus.index', compact('onus', 'totalDbOnusCount'));
    }

    public function syncBackground()
    {
        try {
            $olts = Olt::all();
            $telnet = new ZteTelnetService();
            $synced = 0;
            foreach ($olts as $olt) {
                try {
                    $synced += $telnet->syncOnus($olt);
                } catch (\Exception $e) {
                    // Skip if failed
                }
            }
            return response()->json(['success' => true, 'count' => $synced]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function unconfigured()
    {
        $olts = Olt::all();
        $unconfigured = [];
        $error = null;

        $oltId = request('olt_id', $olts->first()->id ?? null);
        $selectedOlt = null;

        if ($oltId) {
            $selectedOlt = Olt::find($oltId);
            if ($selectedOlt) {
                try {
                    $telnet = new ZteTelnetService();
                    $telnet->connect(
                        $selectedOlt->ip,
                        $selectedOlt->telnet_port,
                        $selectedOlt->telnet_username,
                        $selectedOlt->telnet_password
                    );
                    $unconfigured = $telnet->getUnconfiguredOnus();
                    $telnet->disconnect();
                } catch (\Exception $e) {
                    $error = "Failed to connect to OLT: " . $e->getMessage();
                }
            }
        }

        return view('onus.unconfigured', compact('olts', 'unconfigured', 'selectedOlt', 'error'));
    }

    public function create(Request $request)
    {
        $olts = Olt::all();
        $prefill = [
            'olt_id' => $request->get('olt_id'),
            'board' => $request->get('board', '1'),
            'slot' => $request->get('slot', '1'),
            'port' => $request->get('port', '1'),
            'onu_index' => $request->get('onu_index'),
            'sn' => $request->get('sn'),
        ];

        $tcontProfiles = [];
        $trafficProfiles = [];

        if (!empty($prefill['olt_id'])) {
            $olt = Olt::find($prefill['olt_id']);
            if ($olt) {
                try {
                    $telnet = new ZteTelnetService();
                    $telnet->connect($olt->ip, $olt->telnet_port, $olt->telnet_username, $olt->telnet_password);

                    // Get TCONT (Upstream) Profiles
                    $tcontOutput = $telnet->execute("show gpon profile tcont");
                    if (preg_match_all('/Profile name\s*:\s*(\S+)/i', $tcontOutput, $matches)) {
                        $tcontProfiles = $matches[1];
                    }

                    // Get Traffic (Downstream) Profiles
                    $trafficOutput = $telnet->execute("show gpon profile traffic");
                    if (preg_match_all('/Profile name\s*:\s*(\S+)/i', $trafficOutput, $matches)) {
                        $trafficProfiles = $matches[1];
                    }

                    // Get Available ONU Indices
                    $availableIndices = [];
                    $usedIndices = [];
                    $oltInterfaceConfig = $telnet->execute("show running-config interface gpon-olt_{$prefill['board']}/{$prefill['slot']}/{$prefill['port']}");
                    if (preg_match_all('/onu\s+(\d+)\s+type/i', $oltInterfaceConfig, $matches)) {
                        $usedIndices = array_map('intval', $matches[1]);
                    }
                    for ($i = 1; $i <= 128; $i++) {
                        if (!in_array($i, $usedIndices)) {
                            $availableIndices[] = $i;
                        }
                    }

                    $telnet->disconnect();
                } catch (\Exception $e) {
                    // Fail silently
                }
            }
        }

        $acsProfiles = \App\Models\AcsProfile::all();
        $defaultAcs = \App\Models\AcsProfile::where('is_default', true)->first();

        $scriptTemplates = \App\Models\ScriptTemplate::all();

        $sn = $prefill['sn'] ?? '';
        $defaultTemplate = null;

        if (str_starts_with($sn, 'FHTT')) {
            $defaultTemplate = \App\Models\ScriptTemplate::where('merk', 'like', '%Fiberhome%')->orWhere('merk', 'like', '%FHTT%')->first();
        } elseif (str_starts_with($sn, 'HWTC')) {
            $defaultTemplate = \App\Models\ScriptTemplate::where('merk', 'like', '%Huawei%')->orWhere('merk', 'like', '%HWTC%')->first();
        } elseif (str_starts_with($sn, 'ZTEG')) {
            $defaultTemplate = \App\Models\ScriptTemplate::where('merk', 'like', '%ZTE%')->first();
        }

        if (!$defaultTemplate) {
            $defaultTemplate = \App\Models\ScriptTemplate::where('is_default', true)->first();
        }

        return view('onus.create', compact('olts', 'prefill', 'tcontProfiles', 'trafficProfiles', 'availableIndices', 'acsProfiles', 'defaultAcs', 'scriptTemplates', 'defaultTemplate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'olt_id' => 'required|exists:olts,id',
            'board' => 'required|string',
            'slot' => 'required|string',
            'port' => 'required|string',
            'onu_index' => 'required|string',
            'sn' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|string',
            'upstream_profile' => 'nullable|string',
            'downstream_profile' => 'nullable|string',
            'raw_gpon_onu' => 'required|string',
            'raw_pon_onu_mng' => 'required|string',
        ]);

        $olt = Olt::findOrFail($validated['olt_id']);

        try {
            $telnet = new ZteTelnetService();
            $telnet->connect(
                $olt->ip,
                $olt->telnet_port,
                $olt->telnet_username,
                $olt->telnet_password
            );

            $telnet->provisionOnu(
                $validated['board'],
                $validated['slot'],
                $validated['port'],
                $validated['onu_index'],
                $validated['type'],
                $validated['sn'],
                $validated['name'],
                $validated['raw_gpon_onu'],
                $validated['raw_pon_onu_mng']
            );

            $telnet->disconnect();

            Onu::create($validated);

            return redirect()->route('onus.index')->with('success', 'ONU provisioned successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Provisioning failed: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Onu $onu)
    {
        $olt = $onu->olt;
        $configOnu = "Konfigurasi tidak ditemukan atau gagal mengambil dari OLT.";
        $configMng = "Konfigurasi tidak ditemukan atau gagal mengambil dari OLT.";

        $tconts = [];
        $gemports = [];
        $servicePorts = [];

        try {
            $telnet = new ZteTelnetService();
            $telnet->connect($olt->ip, $olt->telnet_port, $olt->telnet_username, $olt->telnet_password);

            // Get full config to parse
            $fullConfig = $telnet->execute("show running-config");
            $telnet->disconnect();

            // Parse interface gpon-onu
            $onuInterface = "interface gpon-onu_{$onu->board}/{$onu->slot}/{$onu->port}:{$onu->onu_index}";
            if (preg_match("~^$onuInterface\r?\n(.*?)\r?\n!~sm", $fullConfig, $matches)) {
                $rawOnu = trim($matches[1]);
                $configOnu = $onuInterface . "\n" . $rawOnu . "\n!";

                // Extract TCONTs
                if (preg_match_all("/tcont\s+(\d+)\s+name\s+(\S+)\s+profile\s+(\S+)/i", $rawOnu, $tcontMatches, PREG_SET_ORDER)) {
                    foreach ($tcontMatches as $m) {
                        $tconts[$m[1]] = ['name' => $m[2], 'profile' => $m[3]];
                    }
                }

                // Extract Gemports
                if (preg_match_all("/gemport\s+(\d+)\s+tcont\s+(\d+)/i", $rawOnu, $gemMatches, PREG_SET_ORDER)) {
                    foreach ($gemMatches as $m) {
                        $gemports[$m[1]] = ['tcont' => $m[2], 'traffic_limit' => ''];
                    }
                }
                if (preg_match_all("/gemport\s+(\d+)\s+traffic-limit\s+downstream\s+(\S+)/i", $rawOnu, $tlMatches, PREG_SET_ORDER)) {
                    foreach ($tlMatches as $m) {
                        if (isset($gemports[$m[1]])) {
                            $gemports[$m[1]]['traffic_limit'] = $m[2];
                        }
                    }
                }

                // Extract Service Ports
                if (preg_match_all("/service-port\s+(\d+)\s+vport\s+(\d+)\s+user-vlan\s+(\d+)\s+vlan\s+(\d+)/i", $rawOnu, $spMatches, PREG_SET_ORDER)) {
                    foreach ($spMatches as $m) {
                        $servicePorts[$m[1]] = ['vport' => $m[2], 'user_vlan' => $m[3], 'vlan' => $m[4]];
                    }
                }
            }

            // Parse pon-onu-mng
            $mngInterface = "pon-onu-mng gpon-onu_{$onu->board}/{$onu->slot}/{$onu->port}:{$onu->onu_index}";
            if (preg_match("~^$mngInterface\r?\n(.*?)\r?\n!~sm", $fullConfig, $matches)) {
                $configMng = $mngInterface . "\n" . trim($matches[1]) . "\n!";
            }
        } catch (\Exception $e) {
            $configOnu = "Error: " . $e->getMessage();
        }

        return view('onus.edit', compact('onu', 'configOnu', 'configMng', 'tconts', 'gemports', 'servicePorts'));
    }

    public function update(Request $request, Onu $onu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tconts' => 'nullable|array',
            'gemports' => 'nullable|array',
            'service_ports' => 'nullable|array',
        ]);

        $olt = $onu->olt;

        try {
            $telnet = new ZteTelnetService();
            $telnet->connect(
                $olt->ip,
                $olt->telnet_port,
                $olt->telnet_username,
                $olt->telnet_password
            );

            $telnet->execute("conf t");
            $telnet->execute("interface gpon-onu_{$onu->board}/{$onu->slot}/{$onu->port}:{$onu->onu_index}");

            // Update Name
            $telnet->execute("name {$validated['name']}");

            // Update TCONTs
            if (!empty($validated['tconts'])) {
                foreach ($validated['tconts'] as $id => $data) {
                    if (!empty($data['profile'])) {
                        $telnet->execute("tcont {$id} profile {$data['profile']}");
                    }
                }
            }

            // Update Gemports Traffic Limits
            if (!empty($validated['gemports'])) {
                foreach ($validated['gemports'] as $id => $data) {
                    if (!empty($data['traffic_limit'])) {
                        $telnet->execute("gemport {$id} traffic-limit downstream {$data['traffic_limit']}");
                    }
                }
            }

            // Update Service Ports / VLANs
            if (!empty($validated['service_ports'])) {
                foreach ($validated['service_ports'] as $id => $data) {
                    if (!empty($data['user_vlan']) && !empty($data['vlan']) && !empty($data['vport'])) {
                        // First remove the old service port, then recreate it to prevent errors in ZTE C320
                        $telnet->execute("no service-port {$id}");
                        $telnet->execute("service-port {$id} vport {$data['vport']} user-vlan {$data['user_vlan']} vlan {$data['vlan']}");
                    }
                }
            }

            // Advanced Raw CLI Execution for gpon-onu
            if ($request->filled('raw_gpon_onu')) {
                $lines = explode("\n", $request->input('raw_gpon_onu'));
                foreach ($lines as $line) {
                    $cmd = trim($line);
                    if ($cmd !== '' && strpos($cmd, 'name ') !== 0) { // Skip name as it's handled above
                        $telnet->execute($cmd);
                    }
                }
            }

            $telnet->execute("exit"); // Exit gpon-onu

            // Advanced Raw CLI Execution for pon-onu-mng
            if ($request->filled('raw_pon_onu_mng')) {
                $telnet->execute("pon-onu-mng gpon-onu_{$onu->board}/{$onu->slot}/{$onu->port}:{$onu->onu_index}");
                $lines = explode("\n", $request->input('raw_pon_onu_mng'));
                foreach ($lines as $line) {
                    $cmd = trim($line);
                    if ($cmd !== '') {
                        $telnet->execute($cmd);
                    }
                }
                $telnet->execute("exit"); // Exit pon-onu-mng
            }

            $telnet->execute("exit"); // Exit config t

            $telnet->disconnect();

            $onu->update(['name' => $validated['name']]);

            return redirect()->route('onus.index')->with('success', 'ONU configuration updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Onu $onu)
    {
        $olt = $onu->olt;

        try {
            $telnet = new ZteTelnetService();
            $telnet->connect(
                $olt->ip,
                $olt->telnet_port,
                $olt->telnet_username,
                $olt->telnet_password
            );

            $telnet->unprovisionOnu(
                $onu->board,
                $onu->slot,
                $onu->port,
                $onu->onu_index
            );

            $telnet->disconnect();

            $onu->delete();

            return redirect()->route('onus.index')->with('success', 'ONU unprovisioned successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Unprovisioning failed: ' . $e->getMessage());
        }
    }

    public function power(Onu $onu)
    {
        try {
            $olt = $onu->olt;
            if (!$olt) {
                throw new \Exception("OLT tidak ditemukan");
            }

            $rxOlt = 'N/A (SNMP OID Unknown)';
            $rxOnu = 'N/A';
            $method = 'SNMP'; // Strictly SNMP

            // Mengaktifkan ekstensi SNMP native PHP secara aman
            if (!function_exists('snmpget')) {
                throw new \Exception("Ekstensi SNMP PHP belum diaktifkan di server.");
            }

            snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
            snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

            // Berdasarkan referensi dari go-snmp-olt-zte-c320
            $board = (int)$onu->board;
            $pon = (int)$onu->port;
            $onuId = (int)$onu->onu_index;

            if ($board === 1) {
                $baseOnuID = 285278464;
                $baseOnuType = 268500992;
            } else {
                $baseOnuID = 285278720;
                $baseOnuType = 268566528;
            }

            $onuIDSuffix = $baseOnuID + $pon;
            $onuTypeSuffix = $baseOnuType + ($pon * 256);

            // Rx ONU OID (Rx Power di ONU)
            $rxOnuOid = ".1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.10.{$onuIDSuffix}.{$onuId}.1";

            // Tx ONU OID (Tx Power dari ONU) - Sering dipakai sebagai patokan pengganti Rx OLT
            $txOnuOid = ".1.3.6.1.4.1.3902.1012.3.50.12.1.1.14.{$onuTypeSuffix}.{$onuId}.1";

            // Matikan error warning sementara agar tidak bocor ke UI
            $snmpCommunity = !empty($olt->snmp_username) ? $olt->snmp_username : 'rconfigrw';
            $rxOnuSnmp = @snmpget("{$olt->ip}:{$olt->snmp_port}", $snmpCommunity, $rxOnuOid, 1000000, 1);

            if ($rxOnuSnmp !== false) {
                // Formula konversi dari Cepat-Kilat-Teknologi: (Value * 0.002) - 30
                $rxOnuVal = ((int)$rxOnuSnmp * 0.002) - 30;
                $rxOnu = number_format($rxOnuVal, 2) . ' dBm';
            } else {
                throw new \Exception("Data SNMP Rx ONU kosong.");
            }

            // Karena referensi go-snmp-olt-zte-c320 TIDAK memiliki OID untuk RX OLT,
            // kita harus mengambil RX OLT menggunakan Telnet.
            // RX ONU tetap menggunakan SNMP karena lebih cepat dan stabil.
            try {
                $telnet = new \App\Services\ZteTelnetService();
                $telnet->connect($olt->ip, $olt->telnet_port, $olt->telnet_username, $olt->telnet_password);
                $telnetResult = trim($telnet->execute("show pon power attenuation gpon-onu_{$onu->board}/{$onu->slot}/{$onu->port}:{$onu->onu_index}"));
                $telnet->disconnect();

                if (
                    preg_match('/UP\s+Rx\s*:\s*([-0-9.]+)\s*\(dbm\)/i', $telnetResult, $matches) ||
                    preg_match('/Rx power\s*:\s*([-0-9.]+)\s*\(dbm\)/i', $telnetResult, $matches) ||
                    preg_match('/up\s+rx\s+power\s*:\s*([-0-9.]+)/i', $telnetResult, $matches) ||
                    preg_match('/Rx\s*:\s*([-0-9.]+)\s*\(dbm\)/i', $telnetResult, $matches)
                ) {
                    $rxOlt = $matches[1] . ' dBm';
                    $method = 'SNMP (ONU) + Telnet (OLT)';
                } else {
                    $rxOlt = 'N/A';
                }
            } catch (\Throwable $telnetErr) {
                $rxOlt = 'Error Telnet';
            }

            return response()->json([
                'success' => true,
                'rx_olt' => $rxOlt,
                'rx_onu' => $rxOnu,
                'method' => $method
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
