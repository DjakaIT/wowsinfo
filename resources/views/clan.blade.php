@php
  use App\Helpers\FrontendHelper;
@endphp
@extends('layout.layout')

@section('metaTitle',__('seo_clan_title'))
@section('metaDescription',__('seo_clan_content'))
@section('metaKeywords', __('seo_clan_keywords'))

@section('content')
<div class="page-padding">
  <p class="player-title">
    {{ $shortName }} <span class="gray-link">[{{ urldecode($fullName) }}]</span>
  </p>
  <p>
    {{ $clanDescription }}
  </p>
  <div class="shadow4 mb-40">
    <table class="table table-striped table-bordered customRedefine playerTable">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="border-b">{{ __('nav_player') }}</th>
                <th class="border-b">{{ __('wn8_last_month') }}</th>
                <th class="border-b">{{ __('battles_last_month') }}</th>
                <th class="border-b">WN8</th>
                <th class="border-b">{{ __('win_rate') }}</th>
                <th class="border-b">{{ __('t_player_1') }}</th>
                <th class="border-b">{{ __('last_battle') }}</th>
                <th class="border-b">{{ __('position') }}</th>
                <th class="border-b">{{ __('joined') }}</th>
            </tr>
        </thead>
        <tbody>
          @foreach ($members as $member)
            <tr class="border-b">
                <td class="py-2 px-4">{{ $member['name'] }}</td>
                <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($member['wn8Month']) }}">{{ $member['wn8Month'] }}</td>
                <td class="py-2 px-4">{{ $member['battlesMonth'] }}</td>
                <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($member['wn8']) }}">{{ $member['wn8'] }}</td>
                <td class="py-2 px-4">{{ $member['winRate'] }}</td>
                <td class="py-2 px-4">{{ $member['battles'] }}</td>
                <td class="py-2 px-4">{{ $member['lastBattle'] }}</td>
                <td class="py-2 px-4">{{ $member['position'] }}</td>
                <td class="py-2 px-4">{{ $member['joined'] }}</td>
            </tr>
          @endforeach
        </tbody>
    </table>
</div>
</div>
@endsection