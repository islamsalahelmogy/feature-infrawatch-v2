<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('check_type');
            $table->enum('status', ['success', 'failure']);
            $table->decimal('response_time_ms', 10, 2)->nullable();
            $table->decimal('packet_loss_percent', 5, 2)->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
            $table->index(['device_id', 'check_type', 'checked_at']);
        });

        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('metric_name');
            $table->decimal('metric_value', 16, 4);
            $table->string('unit', 32)->nullable();
            $table->timestamp('measured_at');
            $table->timestamps();
            $table->index(['device_id', 'metric_name', 'measured_at']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('check_type');
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'informational']);
            $table->enum('status', ['open', 'acknowledged', 'resolved'])->default('open');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('opened_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['device_id', 'check_type', 'status']);
        });

        Schema::create('incident_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['incident_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_events');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('check_results');
    }
};
