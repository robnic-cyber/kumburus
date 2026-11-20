<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('bookings')
            ->where('payment_method', 'pay_at_hotel')
            ->update(['payment_method' => 'cash']);

        DB::table('bookings')
            ->whereIn('payment_method', ['gcash', 'card'])
            ->update(['payment_method' => 'ewallet']);

        if (DB::getDriverName() !== 'pgsql') {
            DB::table('booking_details')
                ->where('payment_method', 'pay_at_hotel')
                ->update(['payment_method' => 'cash']);

            DB::table('booking_details')
                ->whereIn('payment_method', ['gcash', 'card'])
                ->update(['payment_method' => 'ewallet']);
        }
    }

    public function down(): void
    {
        DB::table('bookings')
            ->where('payment_method', 'cash')
            ->update(['payment_method' => 'pay_at_hotel']);

        DB::table('bookings')
            ->where('payment_method', 'ewallet')
            ->update(['payment_method' => 'gcash']);

        if (DB::getDriverName() !== 'pgsql') {
            DB::table('booking_details')
                ->where('payment_method', 'cash')
                ->update(['payment_method' => 'pay_at_hotel']);

            DB::table('booking_details')
                ->where('payment_method', 'ewallet')
                ->update(['payment_method' => 'gcash']);
        }
    }
};
