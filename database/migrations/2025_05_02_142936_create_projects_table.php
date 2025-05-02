<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id('prjID');
            $table->unsignedBigInteger('cadID');
            $table->string('name');
            $table->text('description');
            $table->date('startDate');
            $table->date('endDate')->nullable();
            $table->string('status');

            $table->foreign('cadID')->references('cadID')->on('cadres')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
