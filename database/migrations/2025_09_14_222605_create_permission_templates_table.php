<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permission_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('area_id');

            // permissions
            $table->boolean('create_scale')->default(false);
            $table->boolean('read_scale')->default(false);
            $table->boolean('update_scale')->default(false);
            $table->boolean('delete_scale')->default(false);

            $table->boolean('create_music')->default(false);
            $table->boolean('read_music')->default(false);
            $table->boolean('update_music')->default(false);
            $table->boolean('delete_music')->default(false);

            $table->boolean('create_role')->default(false);
            $table->boolean('read_role')->default(false);
            $table->boolean('update_role')->default(false);
            $table->boolean('delete_role')->default(false);

            $table->boolean('create_area')->default(false);
            $table->boolean('read_area')->default(false);
            $table->boolean('update_area')->default(false);
            $table->boolean('delete_area')->default(false);

            $table->boolean('manage_users')->default(false);
            $table->boolean('manage_church_settings')->default(false);
            $table->boolean('manage_app_settings')->default(false);

            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('area_id')->references('id')->on('area')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_templates');
    }
};
