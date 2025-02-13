@php
  use App\Helpers\FrontendHelper;
@endphp
@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
    <div class="page-padding">
        <!-- Player info -->
        <p class="player-title">{{ $playerInfo['name'] }}
        @if ($playerInfo['clanName'] !== '')
            <a class="pointer gray-link" href="{{ route('clan.page', [
                'name' => urlencode($playerInfo['clanName']),
                'id' => $playerInfo['clanId']
            ]) }}">[{{ $playerInfo['clanName'] }}]</a>
        @endif
        </p>
        <p class="player-info">Created at: {{ $playerInfo['createdAt'] }}</p>
        <!-- ### Player info -->
        <!-- Player statistics -->
        <!-- <div v-if="playerStatistics === null">Loading</div> -->
        <div class="shadow4 mb-40">
            <table class="table table-striped table-bordered customRedefine playerTable">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="border-b">Stats</th>
                        <th class="border-b">Overall</th>
                        <th class="border-b">Last Day</th>
                        <th class="border-b">Last 7 days</th>
                        <th class="border-b">Last month</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="py-2 px-4">Battles</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['battles'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['battles'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['battles'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['battles'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Wins</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['overall']['wins'] ?? 0) }}">{{ $playerStatistics['overall']['wins'] ?? 'N/A' }}%</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastDay']['wins'] ?? 0) }}">{{ $playerStatistics['lastDay']['wins'] ?? 'N/A' }}%</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastWeek']['wins'] ?? 0) }}">{{ $playerStatistics['lastWeek']['wins'] ?? 'N/A' }}%</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastMonth']['wins'] ?? 0) }}">{{ $playerStatistics['lastMonth']['wins'] ?? 'N/A' }}%</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Tier Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['tier'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['tier'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['tier'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['tier'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Survived</td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['overall']['survived']) ? round($playerStatistics['overall']['survived'], 2) . '%' : 'N/A' }}
                        </td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['lastDay']['survived']) ? round($playerStatistics['lastDay']['survived'], 2) . '%' : 'N/A' }}
                        </td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['lastWeek']['survived']) ? round($playerStatistics['lastWeek']['survived'], 2) . '%' : 'N/A' }}
                        </td>
                        <td class="py-2 px-4">
                            {{ isset($playerStatistics['lastMonth']['survived']) ? round($playerStatistics['lastMonth']['survived'], 2) . '%' : 'N/A' }}
                        </td>
                    <tr class="border-b">
                        <td class="py-2 px-4">Damage Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['damage'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['damage'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['damage'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['damage'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Frags Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['frags'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['frags'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['frags'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['frags'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Spotted Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['spotted'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['spotted'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['spotted'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['spotted'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Experience Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['xp'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['xp'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['xp'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['xp'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Captured Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['capture'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['capture'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['capture'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['capture'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Defended Ø</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['defend']  ?? 'N/A'}}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['defend']  ?? 'N/A'}}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['defend']  ?? 'N/A'}}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['defend']  ?? 'N/A'}}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">PR</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['overall']['pr'] ?? 0) }}">{{ $playerStatistics['overall']['pr'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastDay']['pr'] ?? 0) }}">{{ $playerStatistics['lastDay']['pr']  ?? 'N/A'}}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastWeek']['pr'] ?? 0) }}">{{ $playerStatistics['lastWeek']['pr'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastMonth']['pr'] ?? 0) }}">{{ $playerStatistics['lastMonth']['pr']  ?? 'N/A'}}</td>
                    </tr> 
                    <tr class="border-b">
                        <td class="py-2 px-4">WN8</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['overall']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['overall']['wn8'] ?? 'N/A' }}
                        </td>                        
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastDay']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['lastDay']['wn8'] ?? 'N/A' }}
                        </td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastWeek']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['lastWeek']['wn8'] ?? 'N/A' }}
                        </td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastMonth']['wn8'] ?? 0) }}">
                            {{ $playerStatistics['lastMonth']['wn8'] ?? 'N/A' }}
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
                        <th class="border-b">Name</th>
                        <th class="border-b" style="width: 100px">Nation</th>
                        <th class="border-b">Type</th>
                        <th class="border-b">Tier</th>
                        <th class="border-b">Battles</th>
                        <th class="border-b">Frags Ø</th>
                        <th class="border-b">Damage Ø</th>
                        <th class="border-b">XP</th>
                        <th class="border-b">Wins</th>
                        <th class="border-b">WN8</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($playerVehicles as $vehicle)
                        <tr class="border-b">
                            <td class="py-2 px-4">{{ $vehicle['name'] }}</td>
                            <td class="py-2 px-4" style="width: 100px">
                                <img class="nation-icon" src="{{ FrontendHelper::getFlags($vehicle['nation']) }}" />
                                <span style="display: none;">{{ $vehicle['nation'] }}<span>
                            </td>
                            <td class="py-2 px-4">-</td>
                            <td class="py-2 px-4">{{ $vehicle['tier'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['battles'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['frags'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['damage'] }}</td>
                            <td class="py-2 px-4">-</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($vehicle['wins'] ?? 0) }}">{{ $vehicle['wins'] }}</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($vehicle['wn8']) }}">{{ $vehicle['wn8'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div onclick="closeVehiclePopup" class="vehicle-popup-background"></div>
            <div class="vehicle-info-popup shadow4" v-if="vehicleInfoPopup.show" ref="vehiclePopup">
                <h3>Name</h3>
                <img src="vehicleInfoPopup.obj.image" />
                <p>
                    Description
                    <a href="{{ route('wiki.vehicle', [
                            'nation' => $nation,
                            'type' => $key,
                            'ship' => $vehicle['name']
                        ]) }}">
                        <span>More info</span>
                    </a>
                </p>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const table = document.getElementById("sortableTable");
                    const headers = table.querySelectorAll("th");
                    const tbody = table.querySelector("tbody");

                    headers.forEach((header, columnIndex) => {
                        header.addEventListener("click", () => {
                            const rows = Array.from(tbody.querySelectorAll("tr"));
                            const isAscending = header.dataset.order === "asc";
                            header.dataset.order = isAscending ? "desc" : "asc";

                            rows.sort((rowA, rowB) => {
                                const cellA = rowA.cells[columnIndex].textContent.trim();
                                const cellB = rowB.cells[columnIndex].textContent.trim();

                                const isNumeric = !isNaN(cellA) && !isNaN(cellB);
                                return isAscending
                                    ? (isNumeric ? cellA - cellB : cellA.localeCompare(cellB))
                                    : (isNumeric ? cellB - cellA : cellB.localeCompare(cellA));
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
