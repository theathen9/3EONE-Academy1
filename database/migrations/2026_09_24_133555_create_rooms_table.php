<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblRooms', function (Blueprint $table) {
            $table->id('room_id');

            $table->string('room_name', 50);

            $table->integer('capacity');

            $table->string('status', 50)
                ->default('Active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblRooms');
    }
};