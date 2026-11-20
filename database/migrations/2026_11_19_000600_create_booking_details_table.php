<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS booking_details');

        if (DB::getDriverName() === 'pgsql') {
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

            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE booking_details (
                booking_id INTEGER PRIMARY KEY,
                confirmation_code TEXT NOT NULL,
                guest_name TEXT NOT NULL,
                guest_email TEXT NOT NULL,
                hotel_name TEXT NOT NULL,
                room_name TEXT NOT NULL,
                check_in TEXT NOT NULL,
                check_out TEXT NOT NULL,
                guests INTEGER NOT NULL,
                total_amount NUMERIC NOT NULL,
                status TEXT NOT NULL,
                special_requests TEXT,
                created_at TEXT,
                updated_at TEXT
            )
        SQL);

        DB::statement(<<<'SQL'
            INSERT INTO booking_details (
                booking_id,
                confirmation_code,
                guest_name,
                guest_email,
                hotel_name,
                room_name,
                check_in,
                check_out,
                guests,
                total_amount,
                status,
                special_requests,
                created_at,
                updated_at
            )
            SELECT
                b.id,
                b.confirmation_code,
                u.name,
                u.email,
                h.name,
                r.name,
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

        DB::statement(<<<'SQL'
            CREATE TRIGGER booking_details_after_insert
            AFTER INSERT ON bookings
            BEGIN
                INSERT INTO booking_details
                SELECT
                    b.id,
                    b.confirmation_code,
                    u.name,
                    u.email,
                    h.name,
                    r.name,
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
                WHERE b.id = NEW.id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER booking_details_after_update
            AFTER UPDATE ON bookings
            BEGIN
                DELETE FROM booking_details WHERE booking_id = OLD.id;
                INSERT INTO booking_details
                SELECT
                    b.id,
                    b.confirmation_code,
                    u.name,
                    u.email,
                    h.name,
                    r.name,
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
                WHERE b.id = NEW.id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER booking_details_after_delete
            AFTER DELETE ON bookings
            BEGIN
                DELETE FROM booking_details WHERE booking_id = OLD.id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER booking_details_after_user_update
            AFTER UPDATE OF name, email ON users
            BEGIN
                DELETE FROM booking_details
                WHERE booking_id IN (SELECT id FROM bookings WHERE user_id = NEW.id);
                INSERT INTO booking_details
                SELECT
                    b.id,
                    b.confirmation_code,
                    u.name,
                    u.email,
                    h.name,
                    r.name,
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
                WHERE b.user_id = NEW.id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER booking_details_after_room_update
            AFTER UPDATE OF name, hotel_id ON rooms
            BEGIN
                DELETE FROM booking_details
                WHERE booking_id IN (SELECT id FROM bookings WHERE room_id = NEW.id);
                INSERT INTO booking_details
                SELECT
                    b.id,
                    b.confirmation_code,
                    u.name,
                    u.email,
                    h.name,
                    r.name,
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
                WHERE b.room_id = NEW.id;
            END
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER booking_details_after_hotel_update
            AFTER UPDATE OF name ON hotels
            BEGIN
                DELETE FROM booking_details
                WHERE booking_id IN (
                    SELECT b.id
                    FROM bookings b
                    JOIN rooms r ON r.id = b.room_id
                    WHERE r.hotel_id = NEW.id
                );
                INSERT INTO booking_details
                SELECT
                    b.id,
                    b.confirmation_code,
                    u.name,
                    u.email,
                    h.name,
                    r.name,
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
                WHERE h.id = NEW.id;
            END
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP VIEW IF EXISTS booking_details');

            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS booking_details_after_hotel_update');
        DB::statement('DROP TRIGGER IF EXISTS booking_details_after_room_update');
        DB::statement('DROP TRIGGER IF EXISTS booking_details_after_user_update');
        DB::statement('DROP TRIGGER IF EXISTS booking_details_after_delete');
        DB::statement('DROP TRIGGER IF EXISTS booking_details_after_update');
        DB::statement('DROP TRIGGER IF EXISTS booking_details_after_insert');
        DB::statement('DROP TABLE IF EXISTS booking_details');
    }
};
