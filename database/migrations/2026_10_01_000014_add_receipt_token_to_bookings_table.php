<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The receipt is reached from a link the admin shares with the
        // customer, so a booking id alone is not safe to expose: ids are
        // sequential and guessable. The token is the only credential.
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('receipt_token', 64)->nullable()->unique()->after('id');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable()->after('special_notes');
        });

        // Every existing booking gets a token straight away, so a link already
        // sent out before this migration still resolves.
        DB::table('bookings')->orderBy('id')->each(function ($booking) {
            DB::table('bookings')->where('id', $booking->id)->update([
                'receipt_token' => bin2hex(random_bytes(32)),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['receipt_token']);
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['receipt_token', 'approved_at', 'admin_note']);
        });
    }
};
