<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')->update(['payment_amount' => DB::raw('total_amount')]);

        if (DB::getDriverName() !== 'pgsql') {
            DB::table('booking_details')->update(['payment_amount' => DB::raw('total_amount')]);
        }
    }

    public function down(): void
    {
    }
};
