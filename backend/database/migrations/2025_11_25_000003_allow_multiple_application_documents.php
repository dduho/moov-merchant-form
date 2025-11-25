<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the unique constraint that prevented storing multiple files
        // for the same (merchant_application_id, document_type) pair.
        try {
            Schema::table('application_documents', function (Blueprint $table) {
                // The unique index was created with name 'ad_mad_type_unq'
                $table->dropUnique('ad_mad_type_unq');
            });
        } catch (\Exception $e) {
            // In case the index doesn't exist (safety), try to run a raw statement
            try {
                \DB::statement("ALTER TABLE `application_documents` DROP INDEX `ad_mad_type_unq`");
            } catch (\Exception $ex) {
                // If still failing, log and continue — migration should be idempotent
                logger()->warning('Could not drop ad_mad_type_unq index: ' . $ex->getMessage());
            }
        }
    }

    public function down(): void
    {
        // Recreate the unique constraint if rolling back
        Schema::table('application_documents', function (Blueprint $table) {
            $table->unique(['merchant_application_id', 'document_type'], 'ad_mad_type_unq');
        });
    }
};
