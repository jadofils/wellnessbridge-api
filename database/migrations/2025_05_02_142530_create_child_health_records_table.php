<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('child_health_records', function (Blueprint $table) {
            $table->id('recordID');
            $table->unsignedBigInteger('childID');
            $table->unsignedBigInteger('healthWorkerID');
            $table->date('checkupDate');
            $table->float('height');
            $table->float('weight');
            $table->string('vaccination');
            $table->text('diagnosis');
            $table->text('treatment');

            $table->foreign('childID')->references('childID')->on('children')->onDelete('cascade');
            $table->foreign('healthWorkerID')->references('hwID')->on('health_workers')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_health_records');
    }
};
