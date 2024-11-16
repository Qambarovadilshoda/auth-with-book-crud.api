<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('user.image')->paginate(10);
        return response()->json(
            [
                'books' => BookResource::collection($books->load('images')),
                'links' => [
                    'first' => $books->url(1),
                    'last' => $books->url($books->lastPage()),
                    'prev' => $books->previousPageUrl(),
                    'next' => $books->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $books->currentPage(),
                    'from' => $books->firstItem(),
                    'last_page' => $books->lastPage(),
                    'path' => $books->path(),
                    'per_page' => $books->perPage(),
                    'to' => $books->lastItem(),
                    'total' => $books->total(),

                ],
            ]);
    }
    public function show($id)
    {
        $book = Book::with('user.image')->findOrFail($id);
        return response()->json([
            'book' => new BookResource($book->load('images')),
        ]);
    }

    public function store(StoreBookRequest $request)
    {
        $book = new Book();
        $book->user_id = Auth::id();
        $book->title = $request->title;
        $book->description = $request->description;
        $book->save();

        $images = [];
        foreach ($request->file('images') as $image) {
            $uploadedImage = $this->uploadImage($image);
            $images[] = [
                'imageable_id' => $book->id,
                'imageable_type' => Book::class,
                'path' => $uploadedImage,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Image::insert($images);
        return response()->json([
            'message' => 'Success',
        ], 201);

    }
    public function update(UpdateBookRequest $request, $id)
    {
        $book = Book::findOrFail($id);
        if(Auth::id() !== $book->user_id) {
            return response()->json([
                'message'=> 'You do not have permission to perform this action',
            ],403);
        }
        $book->title = $request->title;
        $book->description = $request->description;
        $book->save();
        if ($request->hasFile('images')) {
            foreach ($book->images as $image) {
                $this->deleteImage($image->path);
            }
            $book->images()->delete();
            $images = [];
            foreach ($request->file('images') as $image) {
                $updatedImage = $this->uploadImage($image);
                $images[] = [
                    'imageable_id' => $book->id,
                    'imageable_type' => Book::class,
                    'path' => $updatedImage,
                ];
            }
            Image::insert($images);
        }
        return response()->json([
            'message' => 'Book updated!',
            'data' => new BookResource($book->load('images')),

        ], 200);
    }
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        if(Auth::id() !== $book->user_id) {
            return response()->json([
                'message'=> 'You do not have permission to perform this action',
            ],403);
        }
        foreach ($book->images as $image) {
            $this->deleteImage($image->path);
        }
        $book->images()->delete();
        $book->delete();
        return response()->noContent(204);
    }
    public function search(Request $request)
    {
        $books = Book::where('title', 'like', "%$request->q%")->with('user.image')->paginate(6);
        return response()->json(
            [
                'books' => BookResource::collection($books->load('images')),
                'links' => [
                    'first' => $books->url(1),
                    'last' => $books->url($books->lastPage()),
                    'prev' => $books->previousPageUrl(),
                    'next' => $books->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $books->currentPage(),
                    'from' => $books->firstItem(),
                    'last_page' => $books->lastPage(),
                    'path' => $books->path(),
                    'per_page' => $books->perPage(),
                    'to' => $books->lastItem(),
                    'total' => $books->total(),
                ],
            ]);

    }
}
