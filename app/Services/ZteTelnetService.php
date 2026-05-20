<?php

namespace App\Services;

class ZteTelnetService
{
    protected $socket;
    protected $host;
    protected $port;
    protected $timeout = 5;
    
    public function __construct()
    {
    }

    public function connect($host, $port, $username, $password)
    {
        $this->host = $host;
        $this->port = $port;
        
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $this->timeout);
        
        if (!$this->socket) {
            throw new \Exception("Telnet connection failed: $errstr ($errno)");
        }
        
        stream_set_timeout($this->socket, 2);
        
        // Wait for username prompt
        $this->readUntil("Username:");
        $this->write($username . "\n");
        
        // Wait for password prompt
        $this->readUntil("Password:");
        $this->write($password . "\n");
        
        // Wait for prompt (# or >)
        $res = $this->readUntil(["#", ">"]);
        
        if (strpos($res, 'fail') !== false || strpos($res, 'incorrect') !== false) {
            throw new \Exception("Authentication failed.");
        }
        
        // Disable pagination
        $this->execute("terminal length 0");
    }
    
    public function disconnect()
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
    
    public function write($buffer)
    {
        fwrite($this->socket, $buffer);
    }
    
    public function readUntil($string)
    {
        if (!is_array($string)) {
            $string = [$string];
        }
        
        $buffer = "";
        $start = time();
        
        while (!feof($this->socket)) {
            $chunk = fread($this->socket, 1024);
            $buffer .= $chunk;
            
            foreach ($string as $s) {
                if (strpos($buffer, $s) !== false) {
                    return $buffer;
                }
            }
            
            if (time() - $start > $this->timeout) {
                break;
            }
        }
        
        return $buffer;
    }
    
    public function execute($command)
    {
        $this->write($command . "\n");
        return $this->readUntil(["#", ">"]);
    }
    
    public function getUnconfiguredOnus()
    {
        $output = $this->execute("show gpon onu uncfg");
        $lines = explode("\n", $output);
        $onus = [];
        
        // Parse the output table
        // Example output:
        // OnuIndex                 Sn                  State
        // ---------------------------------------------------------------------
        // gpon-onu_1/2/1:1         ZTEGC1234567       unknown
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Match pattern gpon-onu_1/1/1:1 or similar
            if (preg_match('/gpon-onu_(\d+\/\d+\/\d+):(\d+)\s+([A-Za-z0-9]+)\s+/', $line, $matches)) {
                $port_parts = explode('/', $matches[1]);
                $onus[] = [
                    'board' => $port_parts[0] ?? '1',
                    'slot' => $port_parts[1] ?? '1',
                    'port' => $port_parts[2] ?? '1',
                    'onu_index' => $matches[2],
                    'sn' => $matches[3],
                ];
            }
        }
        
        return $onus;
    }
    
    public function provisionOnu($board, $slot, $port, $onu_index, $type, $sn, $name, $raw_gpon_onu, $raw_pon_onu_mng)
    {
        // Enter global config mode
        $this->execute("conf t");
        
        // Enter OLT interface config
        $this->execute("interface gpon-olt_{$board}/{$slot}/{$port}");
        
        // Register ONU
        $this->execute("onu {$onu_index} type {$type} sn {$sn}");
        $this->execute("exit");
        
        // Enter ONU interface config
        $this->execute("interface gpon-onu_{$board}/{$slot}/{$port}:{$onu_index}");
        $this->execute("name {$name}");
        $this->execute("sn-bind enable sn");
        
        // Execute manual gpon-onu script
        $lines = explode("\n", $raw_gpon_onu);
        foreach ($lines as $line) {
            $cmd = trim($line);
            if ($cmd !== '') {
                $this->execute($cmd);
            }
        }
        $this->execute("exit");
        
        // Enter pon-onu-mng config
        $this->execute("pon-onu-mng gpon-onu_{$board}/{$slot}/{$port}:{$onu_index}");
        
        // Execute manual pon-onu-mng script
        $lines = explode("\n", $raw_pon_onu_mng);
        foreach ($lines as $line) {
            $cmd = trim($line);
            if ($cmd !== '') {
                $this->execute($cmd);
            }
        }
        $this->execute("exit");
        
        $this->execute("exit");
    }
    
    public function unprovisionOnu($board, $slot, $port, $onu_index)
    {
        $this->execute("conf t");
        $this->execute("interface gpon-olt_{$board}/{$slot}/{$port}");
        $this->execute("no onu {$onu_index}");
        $this->execute("exit");
        $this->execute("exit");
    }

    public function syncOnus($olt)
    {
        $this->connect($olt->ip, $olt->telnet_port, $olt->telnet_username, $olt->telnet_password);
        
        $stateOutput = $this->execute("show gpon onu state");
        $lines = explode("\n", $stateOutput);
        
        $ponPorts = [];
        foreach ($lines as $line) {
            if (preg_match('/^(\d+\/\d+\/\d+):\d+\s+/', trim($line), $matches)) {
                $ponPorts[$matches[1]] = true;
            }
        }

        $runConfig = $this->execute("show running-config");
        $names = [];
        
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
        
        foreach (array_keys($ponPorts) as $pon) {
            $baseInfo = $this->execute("show gpon onu baseinfo gpon-olt_{$pon}");
            $infoLines = explode("\n", $baseInfo);
            
            foreach ($infoLines as $line) {
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
        
        $this->disconnect();
        return $syncedCount;
    }
}
