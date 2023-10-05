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
        Schema::create('form_dual_auths', function (Blueprint $table) {
            $table->id();
            $table->string('form_name', 255);
            $table->string('method', 255);
            $table->string('model_type', 255);
            $table->string('repository_type', 255);
            $table->text('new_payload');
            $table->text('old_payload')->nullable();
            $table->text('summary')->nullable();
            $table->string('permission', 255);
            $table->string('created_by', 255)->nullable();
            $table->string('approved_by', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->dateTime('approved_at')->nullable();
            $table->json('summary_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_dual_auths');
    }
};
