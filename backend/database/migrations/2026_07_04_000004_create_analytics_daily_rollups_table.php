<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_daily_rollups', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('metric_name')->index();
            $table->json('dimensions')->nullable();
            $table->string('dimensions_hash', 64)->nullable();
            $table->bigInteger('integer_value')->default(0);
            $table->decimal('decimal_value', 14, 2)->nullable();
            $table->timestamps();

            $table->unique(['date', 'metric_name', 'dimensions_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_rollups');
    }
};
