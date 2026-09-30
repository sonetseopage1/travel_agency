<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\GalleryPhoto;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    /**
     * Seeded photos reuse the imagery already shipped for tours and
     * destinations, so the gallery has real content without adding binaries.
     *
     * Each entry is [title, caption, image file, matching destination name or
     * null for "not tied to one destination"].
     *
     * @var list<array{0: string, 1: string, 2: string, 3: string|null}>
     */
    private const PHOTOS = [
        ['কক্সবাজার সমুদ্র সৈকত', 'ঢেউের সাথে সৈকতে সন্ধ্যার আলো', 'photo-1609947017136-9daf32a5eb16.jpg', 'কক্সবাজার'],
        ['বালির সমুদ্র উপকূল', 'বাংলাদেশের একমাত্র সমুদ্রসৈকত', 'photo-1507525428034-b723cf961d3e.jpg', 'কক্সবাজার'],
        ['সাজেকের টিলা', 'সকালের আলোয় বাংলাদেশের সবচেয়ে উঁচু গন্তব্য', 'photo-1596895111956-bf1cf0599ce5.jpg', 'সাজেক ভ্যালি'],
        ['বান্দরবানের বুনো', 'খাঁচা ঝুলন্ত ঘরবাড়ি', 'photo-1570789210967-2cac24afeb00.jpg', 'বান্দরবান'],
        ['সুন্দরবনের নৌকা', 'বর্ষায় বনের পথে নৌকা ভ্রমণ', 'photo-1602216056096-3b40cc0c9944.jpg', 'সুন্দরবন'],
        ['রাঙ্গামাটির আবহ', 'মেঘলা ভোলা পাহাড়ের বুকে', 'photo-1469474968028-56623f02e42e.jpg', null],
        ['পাহাড় পথের যাত্রা', 'পাহাড়ি রাস্তায় এক নিঃশব্দ মুহূর্ত', 'photo-1500530855697-b586d89ba3ee.jpg', 'মালয়েশিয়া'],
        ['সিলেটের নীরব ভূমি', 'প্রকৃতির এক শান্ত রূপ', 'photo-1548013146-72479768bada.jpg', 'সিলেট'],
    ];

    public function run(): void
    {
        $destinations = Destination::pluck('id', 'name');

        foreach (self::PHOTOS as $index => [$title, $caption, $file, $destinationName]) {
            GalleryPhoto::updateOrCreate(
                ['image' => 'images/'.$file, 'title' => $title],
                [
                    'caption' => $caption,
                    'alt_text' => $title,
                    'destination_id' => $destinationName === null
                        ? null
                        : $destinations->get($destinationName),
                    'is_featured' => $index < 4,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
