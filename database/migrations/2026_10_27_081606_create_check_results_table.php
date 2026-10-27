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
        Schema::create('check_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('endpoint_id')
                ->constrained('monitored_endpoints')
                ->cascadeOnDelete();

            $table->string('status', 10);
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_results');
    }
};
