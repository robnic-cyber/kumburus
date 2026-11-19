<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE VIEW booking_details AS
            SELECT
                b.id AS booking_id,
                b.confirmation_code,
                u.name AS guest_name,
                u.email AS guest_email,
                h.name AS hotel_name,
                r.name AS room_name,
                b.check_in,
                b.check_out,
                b.guests,
                b.total_amount,
                b.status,
                b.special_requests,
                b.created_at,
                b.updated_at
            FROM bookings b
            JOIN users u ON u.id = b.user_id
            JOIN rooms r ON r.id = b.room_id
            JOIN hotels h ON h.id = r.hotel_id
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS booking_details');
    }
};
