<?php

namespace Database\Seeders;

use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $bookings = [
            [
                'tour_id' => 1,
                'customer_name' => 'মোহাম্মদ রহিম',
                'customer_email' => 'rahim.uddin@email.com',
                'customer_phone' => '01711000001',
                'guest_count' => 2,
                'total_price' => 9000,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'bKash',
                'transaction_id' => 'BK7X9A2B3C',
                'special_notes' => 'সংসার সাথে দুইজন। বাসে পাশের সিট দিতে হবে।',
            ],
            [
                'tour_id' => 2,
                'customer_name' => 'সুমাইয়া বেগম',
                'customer_email' => 'sumaiya.begum@email.com',
                'customer_phone' => '01812000002',
                'guest_count' => 4,
                'total_price' => 34000,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'Nagad',
                'transaction_id' => 'NG8Y2D5F7G',
                'special_notes' => '৪জন (পরিবার)। হাইকিং এর সময় গাইড দিতে হবে।',
            ],
            [
                'tour_id' => 3,
                'customer_name' => 'আব্দুল করিম',
                'customer_email' => 'abdul.karim@email.com',
                'customer_phone' => '01913000003',
                'guest_count' => 2,
                'total_price' => 110000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => null,
                'transaction_id' => null,
                'special_notes' => 'ভিসা সহ প্যাকেজ। দুইজনের জন্য।',
            ],
            [
                'tour_id' => 4,
                'customer_name' => 'নাজমা আক্তার',
                'customer_email' => 'nazma.akter@email.com',
                'customer_phone' => '01614000004',
                'guest_count' => 3,
                'total_price' => 22500,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'Rocket',
                'transaction_id' => 'RK1Q3W8E9R',
                'special_notes' => 'মা ও ছেলেমেয়ে ৩জন। বিশেষ খাবার (নিরামিষ) arrangement।',
            ],
            [
                'tour_id' => 5,
                'customer_name' => 'ফয়সাল আহমেদ',
                'customer_email' => 'faisal.ahmed@email.com',
                'customer_phone' => '01515000005',
                'guest_count' => 1,
                'total_price' => 9500,
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'payment_method' => 'Bank Transfer',
                'transaction_id' => 'BT5T6Y4U2I',
                'special_notes' => 'একাকী যাত্রী। হাইকিং gear লাগবে না। (ক্যান্সেলড - ৫০% ফেরত)',
            ],
            [
                'tour_id' => 6,
                'customer_name' => 'হাফেজ উল্লাহ',
                'customer_email' => 'hafez.ullah@email.com',
                'customer_phone' => '01716000006',
                'guest_count' => 5,
                'total_price' => 27500,
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'bKash',
                'transaction_id' => 'BK4H8J3K9L',
                'special_notes' => '৫জন কচ্চা। চা বাগানে বিশেষ কিছু সময় চাই।',
            ],
            [
                'tour_id' => 7,
                'customer_name' => 'ইসমাত জাহান',
                'customer_email' => 'ismat.jahan@email.com',
                'customer_phone' => '01817000007',
                'guest_count' => 3,
                'total_price' => 180000,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => null,
                'transaction_id' => null,
                'special_notes' => 'বন্ধুদের ৩জন। বিশেষ শপিং ট্যুরে আগ্রহী।',
            ],
            [
                'tour_id' => 8,
                'customer_name' => 'জাসিম উদ্দিন',
                'customer_email' => 'jasim.uddin@email.com',
                'customer_phone' => '01918000008',
                'guest_count' => 6,
                'total_price' => 39000,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'Cash',
                'transaction_id' => 'CS7X2C5V8B',
                'special_notes' => 'বড় পরিবার ৬জন। বাচ্চাদের জন্য বিশেষ স্ন্যাকস।',
            ],
        ];

        foreach ($bookings as $booking) {
            Booking::create($booking);
        }
    }
}
