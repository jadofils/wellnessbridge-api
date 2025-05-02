<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hwID');
            $table->unsignedBigInteger('prjID');
            $table->date('assignedDate');
            $table->date('endDate')->nullable();
            $table->string('role');

            $table->foreign('hwID')->references('hwID')->on('health_workers')->onDelete('cascade');
            $table->foreign('prjID')->references('prjID')->on('projects')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_assignments');
    }
};
