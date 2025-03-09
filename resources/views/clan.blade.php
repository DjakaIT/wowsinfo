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
  <p style="font-size: 1.2rem;">
    {{ $clanDescription }}
  </p>
  <div class="shadow4 mb-40">
    <table class="table table-striped table-bordered customRedefine playerTable">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="border-b">{{ __('nav_player') }}</th>
                <th class="border-b" style="min-width: 150px">{{ __('wn8_last_month') }}</th>
                <th class="border-b" style="min-width: 150px">{{ __('battles_last_month') }}</th>
                <th class="border-b">WN8</th>
                <th class="border-b" style="min-width: 100px">{{ __('win_rate') }}</th>
                <th class="border-b">{{ __('t_player_1') }}</th>
                <th class="border-b">{{ __('last_battle') }}</th>
                <th class="border-b">{{ __('position') }}</th>
                <th class="border-b" style="min-width: 150px">{{ __('joined') }}</th>
            </tr>
        </thead>
        <tbody>
          @foreach ($members as $member)
            <tr class="border-b">
                <td class="py-2 px-4">
                    @if (!empty($member['account_id']) || !empty($member['id']))
                        <a href="{{ route('player.page', [
                            'locale' => app()->getLocale(),
                            'server' => strtolower(session('server', 'eu')),
                            'name' => $member['name'],
                            'id' => $member['account_id'] ?? $member['id']
                        ]) }}">
                            {{ $member['name'] }}
                        </a>
                    @else
                        {{ $member['name'] }}
                    @endif
                </td>
                <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($member['wn8Month']) }}">{{ $member['wn8Month'] }}</td>
                <td class="py-2 px-4">{{ $member['battlesMonth'] }}</td>
                <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($member['wn8']) }}">{{ $member['wn8'] }}</td>
                <td class="py-2 px-4">{{ $member['winRate'] }}</td>
                <td class="py-2 px-4">{{ $member['battles'] }}</td>
                <td class="py-2 px-4">{{ $member['lastBattle'] }}</td>
                <td class="py-2 px-4">
                    @switch(strtolower($member['position']))
                        @case('executive_officer')
                            {{ __('clan_exec_officer') }}
                            @break
                        @case('recruitment_officer')
                            {{ __('clan_rec_officer') }}
                            @break
                        @case('private')
                            {{ __('clan_private') }}
                            @break
                        @case('commander')
                            {{ __('clan_commander') }}
                            @break
                        @case('officer')
                            {{ __('clan_officer') }}
                            @break
                        @case('commissioned_officer')
                            {{ __('clan_commisioned_officer') }}
                            @break
                        @default
                            {{ $member['position'] }}
                    @endswitch
                </td>
                <td class="py-2 px-4">{{ $member['joined'] }}</td>
            </tr>
          @endforeach
        </tbody>
    </table>
</div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const table = document.querySelector(".customRedefine.playerTable");
            if (!table) return; // Exit if table doesn't exist
            
            const headers = table.querySelectorAll("th");
            const tbody = table.querySelector("tbody");
            
            // Initialize headers with sort indicators
            headers.forEach(header => {
                header.style.cursor = "pointer";
                // Add a subtle sort indicator to show it's sortable
                header.innerHTML = `${header.textContent} <span class="sort-indicator">↕</span>`;
                header.dataset.originalText = header.innerHTML;
            });
            
            headers.forEach((header, columnIndex) => {
                header.addEventListener("click", () => {
                    // Get current sort order or default to none
                    const currentOrder = header.dataset.order || 'none';
                    let newOrder;
                    
                    if (currentOrder === 'none' || currentOrder === 'desc') {
                        newOrder = 'asc';
                    } else {
                        newOrder = 'desc';
                    }
                    
                    // Reset all headers
                    headers.forEach(h => {
                        h.innerHTML = h.dataset.originalText;
                        h.dataset.order = 'none';
                    });
                    
                    // Set new order and add indicator
                    header.dataset.order = newOrder;
                    const indicator = newOrder === 'asc' ? '▲' : '▼';
                    header.innerHTML = header.innerHTML.replace('↕', indicator);
                    
                    // Sort rows
                    const rows = Array.from(tbody.querySelectorAll("tr"));
                    rows.sort((rowA, rowB) => {
                        const cellA = rowA.cells[columnIndex].textContent.trim();
                        const cellB = rowB.cells[columnIndex].textContent.trim();
                        
                        const isNumeric = !isNaN(parseFloat(cellA)) && !isNaN(parseFloat(cellB));
                        return newOrder === 'asc'
                            ? (isNumeric ? parseFloat(cellA) - parseFloat(cellB) : cellA.localeCompare(cellB))
                            : (isNumeric ? parseFloat(cellB) - parseFloat(cellA) : cellB.localeCompare(cellA));
                    });
                    
                    tbody.append(...rows);
                });
            });
        });
    </script>
</div>
@endsection