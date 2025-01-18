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
        <p v-if="playerInfo.clanName != ''" class="pointer gray-link">
            {{ $playerInfo['clanName'] }}
        </p>
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
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['wins'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['wins'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['wins'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['wins'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Tier</td>
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
                        <td class="py-2 px-4">Damage</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['damage'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['damage'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['damage'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['damage'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Frags</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['frags'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['frags'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['frags'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['frags'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Spotted</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['spotted'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['spotted'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['spotted'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['spotted'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Experience</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['xp'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['xp'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['xp'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['xp'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Capture</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['capture'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['capture'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['capture'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['capture'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">Defend</td>
<<<<<<< HEAD
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['defend'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['defend'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['defend'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['defend'] ?? 'N/A' }}</td>
                    </tr>
                    <tr class="border-b">
                        <td class="py-2 px-4">WN8</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['wn8'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['wn8'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['wn8'] ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['wn8'] ?? 'N/A' }}</td>
=======
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['defend'] }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['defend'] }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['defend'] }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['defend'] }}</td>
                    </tr>
                    {{-- <tr class="border-b">
                        <td class="py-2 px-4">PR</td>
                        <td class="py-2 px-4">{{ $playerStatistics['overall']['pr'] }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastDay']['pr'] }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastWeek']['pr'] }}</td>
                        <td class="py-2 px-4">{{ $playerStatistics['lastMonth']['pr'] }}</td>
                    </tr> --}}
                    <tr class="border-b">
                        <td class="py-2 px-4">WN8</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['overall']['wn8']) }}">{{ $playerStatistics['overall']['wn8'] }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastDay']['wn8']) }}">{{ $playerStatistics['lastDay']['wn8'] }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastWeek']['wn8']) }}">{{ $playerStatistics['lastWeek']['wn8'] }}</td>
                        <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($playerStatistics['lastMonth']['wn8']) }}">{{ $playerStatistics['lastMonth']['wn8'] }}</td>
>>>>>>> dev
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- ### Player statistics -->
        <!-- Player vehicles -->
        <!-- <div v-if="playerVehicles.length === 0">Loading</div> -->
        <div class="shadow4 table-container">
            <table class="table table-striped table-bordered customRedefine playerTable">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="border-b">Nation</th>
                        <th class="border-b">Name</th>
                        <th class="border-b">Tier</th>
                        <th class="border-b">Battles</th>
                        <th class="border-b">Frags</th>
                        <th class="border-b">Damage</th>
                        <th class="border-b">Wins</th>
                        <th class="border-b">WN8</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($playerVehicles as $vehicle)
                        <tr class="border-b">
                            <td class="py-2 px-4">{{ $vehicle['nation'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['name'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['tier'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['battles'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['frags'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['damage'] }}</td>
                            <td class="py-2 px-4">{{ $vehicle['wins'] }}</td>
                            <td class="py-2 px-4 {{ 'table-' . FrontendHelper::getWN8Color($vehicle['wn8']) }}">{{ $vehicle['wn8'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- ### Player vehicles -->
    </div>
@endsection
