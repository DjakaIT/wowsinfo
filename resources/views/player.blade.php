@php
  use App\Helpers\FrontendHelper;
  $server = request('server', 'EU');

@endphp
@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])



@section('content')
    <div class="page-padding">
        <!-- Processing indicator -->
        @if(isset($playerStatistics['overall']['processing']))
            <div class="shadow4 mb-40">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div>
                            <h4 class="alert-heading">Processing Player Statistics</h4>
                            <p>{{ $playerStatistics['overall']['message'] }}</p>
                            <p class="mb-0">This page will automatically refresh in <span id="countdown">60</span> seconds.</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Auto-refresh countdown
                document.addEventListener('DOMContentLoaded', function() {
                    let seconds = 60;
                    const countdownElement = document.getElementById('countdown');
                    
                    const interval = setInterval(function() {
                        seconds--;
                        countdownElement.textContent = seconds;
                        
                        if (seconds <= 0) {
                            clearInterval(interval);
                            location.reload();
                        }
                    }, 1000);
                });
            </script>
        @endif

        <!-- Player info -->
        <p class="player-title">{{ $playerInfo['name'] }}
        @if(!empty($playerInfo['clanId']))
            <a href="{{ route('clan.page', ['name' => $playerInfo['clanName'], 'id' => $playerInfo['clanId']]) }}">
                [{{ $playerInfo['clanName'] }}]
            </a>
        @else
            <span class="text-muted">Not in a clan</span>
        @endif
        </p>
        
        <!-- Only show stats tables if not processing -->
        @if(!isset($playerStatistics['overall']['processing']))
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
                            <td class="py-2 px-4">{{ $playerStatistics['overall']['battles'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastDay']['battles'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['battles'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['battles'] ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Wins</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['overall']['wins'] ?? 0) }}">{{ $playerStatistics['overall']['wins'] ?? '-' }}%</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastDay']['wins'] ?? 0) }}">{{ $playerStatistics['lastDay']['wins'] ?? '-' }}%</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastWeek']['wins'] ?? 0) }}">{{ $playerStatistics['lastWeek']['wins'] ?? '-' }}%</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWinColor($playerStatistics['lastMonth']['wins'] ?? 0) }}">{{ $playerStatistics['lastMonth']['wins'] ?? '-' }}%</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Tier Ø</td>
                            <td class="py-2 px-4">{{ $playerStatistics['overall']['tier'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastDay']['tier'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['tier'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['tier'] ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Survived</td>
                            <td class="py-2 px-4">
                                {{ isset($playerStatistics['overall']['survived']) ? round($playerStatistics['overall']['survived'], 2) . '%' : '-' }}
                            </td>
                            <td class="py-2 px-4">
                                {{ isset($playerStatistics['lastDay']['survived']) ? round($playerStatistics['lastDay']['survived'], 2) . '%' : '-' }}
                            </td>
                            <td class="py-2 px-4">
                                {{ isset($playerStatistics['lastWeek']['survived']) ? round($playerStatistics['lastWeek']['survived'], 2) . '%' : '-' }}
                            </td>
                            <td class="py-2 px-4">
                                {{ isset($playerStatistics['lastMonth']['survived']) ? round($playerStatistics['lastMonth']['survived'], 2) . '%' : '-' }}
                            </td>
                        <tr class="border-b">
                            <td class="py-2 px-4">Damage Ø</td>
                            <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['overall']['damage'] ?? 0, $server) }}</td>
                            <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['lastDay']['damage'] ?? 0, $server) }}</td>
                            <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['lastWeek']['damage'] ?? 0, $server) }}</td>
                            <td class="py-2 px-4">{{FrontendHelper::formatDamage($playerStatistics['lastMonth']['damage'] ?? 0, $server) }}</td> </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Frags Ø</td>
                            <td class="py-2 px-4">{{ $playerStatistics['overall']['frags'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastDay']['frags'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['frags'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['frags'] ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Spotted Ø</td>
                            <td class="py-2 px-4">{{ $playerStatistics['overall']['spotted'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastDay']['spotted'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['spotted'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['spotted'] ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Experience Ø</td>
                            <td class="py-2 px-4">{{ $playerStatistics['overall']['xp'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastDay']['xp'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['xp'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['xp'] ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Captured Ø</td>
                            <td class="py-2 px-4">{{ $playerStatistics['overall']['capture'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastDay']['capture'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['capture'] ?? '-' }}</td>
                            <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['capture'] ?? '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-2 px-4">Defended Ø</td>
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
        @endif
    </div>
@endsection
