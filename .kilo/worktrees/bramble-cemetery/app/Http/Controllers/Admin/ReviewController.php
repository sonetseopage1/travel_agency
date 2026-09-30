<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with('tour');

        if ($status = $request->input('status')) {
            if ($status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        $reviews = $query->latest()->paginate(20);

        $approvedCount = Review::where('is_approved', true)->count();
        $pendingCount = Review::where('is_approved', false)->count();
        $avgRating = Review::avg('rating') ?? 0;

        return view('admin.reviews.index', compact('reviews', 'approvedCount', 'pendingCount', 'avgRating'));
    }

    public function toggleApproval($id)
    {
        $review = Review::findOrFail($id);
        $review->is_approved = ! $review->is_approved;
        $review->save();

        return back()->with('success', 'Review approval toggled!');
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'Review deleted!');
    }
}
