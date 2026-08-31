@extends('layouts.app')

@section('content')
<x-page-header pagetitle="Movies" class="bg-primary"/>

<div class="wrapper wrapper-content">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5><i class="fa fa-film"></i> Movie Search</h5>
                </div>
                <div class="ibox-content">

                    {{-- Search form: GET so the query shows in the URL (?search=...) --}}
                    <form method="GET" action="{{ url('/p1') }}" class="m-b">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ $q }}" class="form-control"
                                   placeholder="Search a movie title...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </form>

                    @if($q !== '')
                        <p>Showing <strong>{{ count($movies) }}</strong> result(s) for "{{ $q }}".</p>
                    @else
                        <p>All <strong>{{ $total }}</strong> movies.</p>
                    @endif

                    <div class="row">
                        @forelse($movies as $movie)
                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $movie }}</h5>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-md-12">
                                <div class="alert alert-warning">No movies matched your search.</div>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
