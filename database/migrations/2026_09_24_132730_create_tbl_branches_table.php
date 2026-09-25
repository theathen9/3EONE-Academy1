<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tblBranches', function (Blueprint $table) {
            $table->id('branch_id');

            $table->string('branch_code', 20)
                ->unique();

            $table->string('branch_name', 150);

            $table->string('phone1', 20)->nullable();
            $table->string('phone2', 20)->nullable();
            $table->string('email', 150)->nullable();

            $table->text('address')->nullable();

            $table->string('location', 20)->nullable();

            $table->boolean('status')
                ->default(true);

            $table->timestamp('created_at')
                ->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tblBranches');
    }
};