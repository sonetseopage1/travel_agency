<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'tour_id' => 1,
                'customer_name' => 'রাকিব হাসান',
                'customer_email' => 'rakib.hasan@email.com',
                'rating' => 5,
                'comment' => 'পুরো trip-টা খুব সুন্দরভাবে organized ছিল। সময়মতো transport, hotel এবং guide—সবকিছু খুব ভালো ছিল। কক্সবাজারের সৈকতে সূর্যাস্ত দেখা ছিল আমার জীবনের সেরা মুহূর্ত।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 1,
                'customer_name' => 'তানিয়া আক্তার',
                'customer_email' => 'tania.akter@email.com',
                'rating' => 4,
                'comment' => 'কক্সবাজার ভ্রমণ ছিল খুবই আনন্দদায়ক। ইনানী ও হিমছড়ি সৈকত দেখলাম। খাবারের ব্যবস্থা ভালো ছিল, শুধু হোটেলটা একটু বেশি দূরে ছিল। সামগ্রিকভাবে খুব ভালো অভিজ্ঞতা।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 1,
                'customer_name' => 'মোঃ শফিকুল ইসলাম',
                'customer_email' => 'shafiqul.islam@email.com',
                'rating' => 5,
                'comment' => 'আমার পরিবারসহ কক্সবাজার ভ্রমণ করেছি। বাচ্চারা খুব খুশি হয়েছে। টিমের সদস্যরা খুব সহায়ক ছিল। বিশেষ করে গাইড ভাইয়ার ব্যবহার ছিল অসাধারণ। আবার যাবো ইনশাআল্লাহ।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 2,
                'customer_name' => 'সাদিয়া রহমান',
                'customer_email' => 'sadia.rahman@email.com',
                'rating' => 5,
                'comment' => 'সবচেয়ে ভালো লেগেছে live seat availability। Booking করার আগে কতগুলো seat আছে সেটা পরিষ্কারভাবে দেখতে পেরেছি। সাজেকের সুন্দর দৃশ্য কখনও ভুলবো না। রুইলুই হ্রদটা দেখতে খুব সুন্দর।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 2,
                'customer_name' => 'আরিফ হোসেন',
                'customer_email' => 'arif.hossain@email.com',
                'rating' => 5,
                'comment' => 'সাজেক ভ্যালি ট্যুর ছিল আমার জীবনের প্রথম পাহাড় ভ্রমণ। কংলাক পাহাড়ের চূড়ায় উঠতে গিয়ে হাড় কাঁপলেও উপভোগ করেছি। টিমের ব্যবস্থা খুবই ভালো ছিল। আবার এই সকল ট্যুরে যেতে চাই।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 2,
                'customer_name' => 'নুসরাত জাহান',
                'customer_email' => 'nusrat.jahan@email.com',
                'rating' => 4,
                'comment' => 'সাজেক ভ্রমণ খুবই সুন্দর। আদিবাসী সংস্কৃতি, পাহাড়ি স্থাপত্য, সুন্দর হ্রদ—সব কিছু মিলে অসাধারণ একটা অভিজ্ঞতা। শুধু খাবারটা একটু কম স্বাদের ছিল। যোগাযোগ ব্যবস্থা ভালো।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 3,
                'customer_name' => 'মাহমুদুল ইসলাম',
                'customer_email' => 'mahmudul.islam@email.com',
                'rating' => 5,
                'comment' => 'Family নিয়ে Malaysia trip করেছি। পুরো itinerary আগে থেকেই clear ছিল, তাই trip planning অনেক সহজ হয়েছে। পেট্রোনাস টাওয়ারের আলো দেখে বাচ্চারা খুব খুশি হয়েছে। জেন্টিং হাইল্যান্ডস ছিল সেরা।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 3,
                'customer_name' => 'ফারহানা কবীর',
                'customer_email' => 'farhana.kabir@email.com',
                'rating' => 5,
                'comment' => 'Malaysia tour এর সবচেয়ে ভালো লেগেছে visa processing। আগে থেকেই সব ঠিকঠাক করে দিয়েছিল। হোটেল, খাবার, ট্রান্সপোর্ট—সবকিছু perfect। শপিং এর সুযোগও ছিল। সবার সাথে recommend করবো।',
                'is_approved' => true,
            ],
            [
                'tour_id' => 3,
                'customer_name' => 'শেখ রফিকুল ইসলাম',
                'customer_email' => 'rafiqul.islam@email.com',
                'rating' => 4,
                'comment' => 'Malaysia tour টা খুবই চমৎকার ছিল। কুয়ালালামপুর, পেনাং, জেন্টিং—সব স্থান ভালোভাবে দেখানো হয়েছে। শুধু দুপুরের খাবার arrangement একটু ভালো করা যায়। International tour এর জন্য সবচেয়ে reliable টিম।',
                'is_approved' => true,
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
