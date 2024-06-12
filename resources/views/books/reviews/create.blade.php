@extends('layouts.app')

@section('content')
  <h1 class="mb-10 text-2xl">Add Review for {{ $book->title }}</h1>

  <form method="POST" action="{{ route('books.reviews.store', $book) }}">
    @csrf
    <label for="review">Review</label>
    <textarea name="review" id="review" required class="input {{ $errors->has('review') ? '':'mb-3' }}"> {{ old('review') }} </textarea>
    @error('review')
        <p class="text-sm text-red-500 mb-3">{{ $message }}</p>
    @enderror

    <label for="rating mt-4">Rating</label>
    <select name="rating" id="rating" class="input mb-4" required>
      <option value="">Select a Rating</option>
      @for ($i = 1; $i <= 5; $i++)
        <option value="{{ $i }}">{{ $i }}</option>
      @endfor
    </select>
    @error('rating')
        <p class="font-small text-red-500">{{ $message }}</p>
    @enderror

    <button type="submit" class="btn">Add Review</button>
  </form>
@endsection