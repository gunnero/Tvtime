<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table): void {
            $table->string('last_metadata_failure_reason')->nullable()->index();
            $table->timestamp('metadata_failed_at')->nullable()->index();
            $table->unsignedInteger('metadata_failure_count')->default(0)->index();
            $table->string('metadata_review_status')->default('pending')->index();

            $table->index(['user_id', 'metadata_review_status', 'metadata_failure_count']);
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'metadata_review_status', 'metadata_failure_count']);
            $table->dropColumn([
                'last_metadata_failure_reason',
                'metadata_failed_at',
                'metadata_failure_count',
                'metadata_review_status',
            ]);
        });
    }
};
