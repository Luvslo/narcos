@extends('layouts.basic')

@section('page')
<h3 class="page-title"><span aria-hidden="true" class="li_user"></span> {{ $character->name }}</h3>
<table class="table table-sm table-dark">
    <tbody>
        <tr>
            <td class="w-20">
                <span aria-hidden="true" class="li_heart"></span>
                <span>{{ __('Health') }}</span>
            </td>
            <td>
                <span>{{ $character->life }}%</span>
            </td>
        </tr>
        <tr>
            <td>
                <span aria-hidden="true" class="li_data"></span>
                <span>{{ __('Rank') }}</span>
            </td>
            <td>
                <span>{{ $character->rank() }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span aria-hidden="true" class="li_banknote"></span>
                <span>{{ __('Money') }}</span>
            </td>
            <td>
                <span>&euro;{{ $character->money() }},-</span>
            </td>
        </tr>
        <tr>
            <td>
                <span aria-hidden="true" class="li_truck"></span>
                <span>{{ __('Contraband') }}</span>
            </td>
            <td>
                <span>{{ $character->contraband() }}kg</span>
            </td>
        </tr>
        <tr>
            <td>
                <span aria-hidden="true" class="li_location"></span>
                <span>{{ __('Whereabouts') }}</span>
            </td>
            <td>
                <span>{{ $character->country() }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span aria-hidden="true" class="li_world"></span>
                <span>{{ __('Transport') }}</span>
            </td>
            <td>
                <span>{{ $character->transport() }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span aria-hidden="true" class="li_tag"></span>
                <span>{{ __('Description') }}</span>
            </td>
            <td class="text-normal">
                {!! isset($character->profile->description) ? $character->profile->description() : '<i>This player surrounds itself with mystery.</i>' !!}
            </td>
        </tr>
    </tbody>
</table>
@if ($character->isOwn)
    <table class="table table-sm table-dark">
        <thead>
            <th>
                <span>Edit description</span>
            </th>
        </thead>
        <tbody>
            <tr>
                <td>
                    You can use Narcoscript to enhance your profile: <a href="/documentation#profile">show help</a>.
                    <form method="POST" action="/profile">
                        @csrf
                        <div class="form-group mb-2 mt-2">
                            <textarea class="form-control" rows="5" id="comment" name="description" required>{{ $character->profile->description }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-secondary">Save</button>
                            <a href="/profile/delete" class="btn btn-danger float-right">Delete description</a>
                        </div>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
@endif
@endsection