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
        Schema::create('deadlock_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // PostgreSQL session ID
            $table->string('process_id')->nullable(); // PostgreSQL process ID
            $table->string('database_name')->nullable(); // Database name where deadlock occurred
            $table->string('table_name')->nullable(); // Table involved in deadlock
            $table->string('lock_type')->nullable(); // Type of lock (row, table, etc.)
            $table->text('query_text')->nullable(); // SQL query that caused deadlock
            $table->text('deadlock_details')->nullable(); // Detailed deadlock information
            $table->json('involved_queries')->nullable(); // JSON array of all queries involved
            $table->json('lock_waits')->nullable(); // JSON array of lock wait information
            $table->string('error_code', 10)->nullable(); // PostgreSQL error code (e.g., 40P01)
            $table->text('error_message')->nullable(); // Full error message
            $table->string('severity', 20)->default('ERROR'); // Error severity level
            $table->string('user_name')->nullable(); // Database user who experienced deadlock
            $table->string('application_name')->nullable(); // Application name
            $table->string('client_addr')->nullable(); // Client IP address
            $table->integer('duration_ms')->nullable(); // How long the deadlock lasted
            $table->json('stack_trace')->nullable(); // Application stack trace
            $table->string('resolved_by')->nullable(); // How it was resolved (timeout, retry, etc.)
            $table->timestamp('resolved_at')->nullable(); // When it was resolved
            $table->boolean('is_resolved')->default(false); // Whether deadlock was resolved
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['created_at', 'is_resolved']);
            $table->index(['table_name', 'created_at']);
            $table->index(['error_code', 'created_at']);
            $table->index(['user_name', 'created_at']);
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deadlock_logs');
    }
};