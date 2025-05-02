<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_restrictions', function (Blueprint $table) {
            $table->id('hrID');
            $table->unsignedBigInteger('recordID');
            $table->text('description');
            $table->string('severity');

            $table->foreign('recordID')->references('recordID')->on('child_health_records')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_restrictions');
    }
};
