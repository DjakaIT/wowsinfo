@php
  use App\Helpers\FrontendHelper;
  $server = request('server', 'EU');

@endphp
@extends('layout.layout')

@section('metaTitle',__('seo_player_title'))
@section('metaDescription',__('seo_player_content'))
@section('metaKeywords', __('seo_player_keywords'))



@section('content')
    <div class="page-padding">
        <!-- Player info -->
        <p class="player-title">{{ $playerInfo['name'] }}
            @if ($playerInfo['clanName'] !== '' && !is_null($playerInfo['clanId']))
                <a class="pointer gray-link" href="{{ route('clan.page', [
                    'locale' => app()->getLocale(),
                    'server' => strtolower(session('server', 'eu')),
                    'name' => urlencode($playerInfo['clanName']),
                    'id' => $playerInfo['clanId']
                ]) }}">[{{ $playerInfo['clanName'] }}]</a>
            @elseif ($playerInfo['clanName'] !== '')
                <span class="gray-link">[{{ $playerInfo['clanName'] }}]</span>
            @endif
            </p>
        <p class="player-info">{{ __('account_created_at') }}:   {{ $playerInfo['createdAt'] }}</p>
        <!-- ### Player info -->
        <!-- Player statistics -->
        <!-- <div v-if="playerStatistics === null">Loading</div> -->
        <div class="shadow4 mb-40">
            <table class="table table-striped table-bordered customRedefine playerTable">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="border-b">Stats</th>
                        <th class="border-b">{{ __('th_player_1') }}</th>
                        <th class="border-b">{{ __('th_player_2') }}</th>
                        <th class="border-b">{{ __('th_player_3') }}</th>
                        <th class="border-b">{{ __('th_player_4') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_1') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['battles'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['battles'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['battles'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['battles'] ?? '-' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_10') }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['overall']['wins'] ?? 0) }}">{{ $playerStatistics['overall']['wins'] ?? '-' }}%</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastDay']['wins'] ?? 0) }}">{{ $playerStatistics['lastDay']['wins'] ?? '' }}%</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastWeek']['wins'] ?? 0) }}">{{ $playerStatistics['lastWeek']['wins'] ?? '-' }}%</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastMonth']['wins'] ?? 0) }}">{{ $playerStatistics['lastMonth']['wins'] ?? '-' }}%</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_2') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['tier'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['tier'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['tier'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['tier'] ?? '-' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_3') }}</td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['overall']['survived']) && is_numeric($playerStatistics['overall']['survived']) 
                                ? round($playerStatistics['overall']['survived'], 2) . '%' 
                                : (isset($playerStatistics['overall']['survived']) ? $playerStatistics['overall']['survived'] : '-') }}
                        </td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['lastDay']['survived']) && is_numeric($playerStatistics['lastDay']['survived']) 
                                ? round($playerStatistics['lastDay']['survived'], 2) . '%' 
                                : (isset($playerStatistics['lastDay']['survived']) ? $playerStatistics['lastDay']['survived'] : '-') }}
                        </td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['lastWeek']['survived']) && is_numeric($playerStatistics['lastWeek']['survived']) 
                                ? round($playerStatistics['lastWeek']['survived'], 2) . '%' 
                                : (isset($playerStatistics['lastWeek']['survived']) ? $playerStatistics['lastWeek']['survived'] : '-') }}
                        </td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['lastMonth']['survived']) && is_numeric($playerStatistics['lastMonth']['survived']) 
                                ? round($playerStatistics['lastMonth']['survived'], 2) . '%' 
                                : (isset($playerStatistics['lastMonth']['survived']) ? $playerStatistics['lastMonth']['survived'] : '-') }}
                        </td>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_4') }}</td>
                        <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['overall']['damage'] ?? 0, $server) }}</td>
                        <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['lastDay']['damage'] ?? 0, $server) }}</td>
                        <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['lastWeek']['damage'] ?? 0, $server) }}</td>
                        <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['lastMonth']['damage'] ?? 0, $server) }}</td> </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_5') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['frags'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['frags'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['frags'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['frags'] ?? '-' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_6') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['spotted'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['spotted'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['spotted'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['spotted'] ?? '-' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_7') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['xp'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['xp'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['xp'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['xp'] ?? '-' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_8') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['capture'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['capture'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['capture'] ?? '-' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['capture'] ?? '-' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">{{ __('t_player_9') }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['defend']  ?? '-'}}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['defend']  ?? '-'}}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['defend']  ?? '-'}}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['defend']  ?? '-'}}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">PR</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['overall']['pr'] ?? 0) }}">{{ $playerStatistics['overall']['pr'] ?? '-' }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastDay']['pr'] ?? 0) }}">{{ $playerStatistics['lastDay']['pr']  ?? '-'}}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastWeek']['pr'] ?? 0) }}">{{ $playerStatistics['lastWeek']['pr'] ?? '-' }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastMonth']['pr'] ?? 0) }}">{{ $playerStatistics['lastMonth']['pr']  ?? '-'}}</td>
                    </tr> 
                    <tr class="border-b">
                        <td class="py-2 px-4">WN8</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['overall']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['overall']['wn8'] ?? '-' }}
                        </td>                        
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastDay']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['lastDay']['wn8'] ?? '-' }}
                        </td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastWeek']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['lastWeek']['wn8'] ?? '-' }}
                        </td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastMonth']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['lastMonth']['wn8'] ?? '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- ### Player statistics -->
        <!-- Player vehicles -->
        <!-- <div v-if="playerVehicles.length === 0">Loading</div> -->
        <div class="shadow4 table-container">
            <table id="sortableTable" class="table table-striped table-bordered customRedefine playerTable">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="border-b">{{ __('th_vehicle_2') }}</th>
                        <th class="border-b" style="width: 100px">{{ __('th_vehicle_1') }}</th>
                        <th class="border-b">{{ __('wiki_type') }}</th>
                        <th class="border-b">{{ __('th_vehicle_3') }}</th>
                        <th class="border-b">{{ __('th_vehicle_4') }}</th>
                        <th class="border-b">{{ __('th_vehicle_5') }}</th>
                        <th class="border-b">{{ __('th_vehicle_6') }}</th>
                        <th class="border-b">XP</th>
                        <th class="border-b">{{ __('th_vehicle_7') }}</th>
                        <th class="border-b">WN8</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($playerVehicles as $vehicle)
                        <tr class="border-b">
                            <td class="py-2 px-4">{{ $vehicle['name'] }}</td>
                            <td class="py-2 px-4" style="width: 100px">
                                <img class="nation-icon" src="{{ FrontendHelper::getFlags($vehicle['nation']) }}" />
                                <span style="display: none;">{{ $vehicle['nation'] }}</span>
                            </td>
                            <td class="py-2 px-4">{{ $vehicle['type'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['tier'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['battles'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['frags'] }}</td>
                            <td class="py-2 px-4">
                                {{ FrontendHelper::formatDamage($vehicle['damage'] ?? 0, $server) }}
                            </td>                           <td class="py-2 px-4">{{ $vehicle['xp'] }}</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($vehicle['wins'] ?? 0) }}">{{ $vehicle['wins'] }}%</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($vehicle['wn8']) }}">{{ $vehicle['wn8'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const table = document.getElementById("sortableTable");
                    const headers = table.querySelectorAll("th");
                    const tbody = table.querySelector("tbody");

                    // Initialize headers with sort indicators
                    headers.forEach(header => {
                        header.dataset.originalText = header.textContent;
                        header.style.cursor = "pointer";
                        // Add a subtle sort indicator to show it's sortable
                        header.innerHTML = `${header.textContent} <span class="sort-indicator">↕</span>`;
                        header.dataset.originalText = header.innerHTML;
                    });

                    headers.forEach((header, columnIndex) => {
                        header.addEventListener("click", () => {
                            const rows = Array.from(tbody.querySelectorAll("tr"));
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
        <!-- ### Player vehicles -->
    </div>
@endsection