<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('ip');
            $table->string('telnet_username')->nullable();
            $table->string('telnet_password')->nullable();
            $table->integer('telnet_port')->default(23162);
            $table->string('snmp_username')->nullable();
            $table->string('snmp_password')->nullable();
            $table->integer('snmp_port')->default(23161);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
