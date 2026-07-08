<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DBMTS Master Data: Document Reference Master.
 *
 * Maps to scope entity: DocumentReference(WM document code, title, version,
 * issue date, module, status).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_references', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();      // WM document code, e.g. WM018
            $table->string('title');
            $table->string('version')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('module')->nullable();  // e.g. Batchcard, Metal Detector
            $table->string('status')->nullable();  // e.g. Active, Superseded
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_references');
    }
};
